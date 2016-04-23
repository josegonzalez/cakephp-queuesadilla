<?php
namespace Josegonzalez\CakeQueuesadilla\Queue;

use Cake\Core\ObjectRegistry;
use Cake\Core\StaticConfigTrait;
use Cake\Utility\Hash;
use InvalidArgumentException;
use Josegonzalez\CakeQueuesadilla\Queue\QueueEngineRegistry;
use josegonzalez\Queuesadilla\Engine\NullEngine;
use josegonzalez\Queuesadilla\Queue as Queuer;
use RuntimeException;

/**
 * Queue provides a consistent interface to Queuing in your application. It allows you
 * to use several different Queue engines, without coupling your application to a specific
 * implementation. It also allows you to change out queue engine or configuration without effecting
 * the rest of your application.
 *
 * ### Configuring Queue engines
 *
 * You can configure Queue engines in your application's `Config/queue.php` file.
 * A sample configuration would be:
 *
 * ```
 * Queue::config('shared', [
 *    'className' => 'Josegonzalez\Queuesadilla\Engine\MysqlEngine',
 *    'url' => 'mysql://username:password@host:port/my-database?table=jobs'
 * ]);
 * ```
 *
 * This would configure an Mysql queue engine to the 'my-database' database with the 'jobs'
 * table to the 'shared' alias. You could then push jobs to that queue alias by using it for t
 * he `$config` parameter in the various Queue methods.
 *
 * The only method supported by this library is the Queue::push() method. All other worker
 * functions should use the provided shell class.
 *
 * There are 5 built-in caching engines:
 *
 * - `BeanstalkEngine` - Queues to a beanstalk server
 * - `IronEngine` - Queues to the Iron.IO webservice
 * - `MemoryEngine` - Queues in-memory, and will lose jobs after the request ends
 * - `MysqlEngine` - Queues to a database table on a mysql server
 * - `NullEngine` - Ignores all queued jobs and returns true
 * - `RedisEngine` - Queues to a redis server
 * - `SynchronousEngine` - Runs jobs immediately at the time of queueing
 *
 * See Queue engine documentation for expected configuration keys.
 *
 * @see config/app.php for configuration settings
 * @param string $name Name of the configuration
 * @param array $config Optional associative array of settings passed to the engine
 * @return array [engine, settings] on success, false on failure
 */
class Queue
{

    use StaticConfigTrait {
        parseDsn as protected _parseDsn;
    }

    /**
     * An array mapping url schemes to fully qualified queuing engine
     * class names.
     *
     * @var array
     */
    protected static $_dsnClassMap = [
        'beanstalk' => 'josegonzalez\Queuesadilla\Engine\BeanstalkEngine',
        'iron' => 'josegonzalez\Queuesadilla\Engine\IronEngine',
        'memory' => 'josegonzalez\Queuesadilla\Engine\MemoryEngine',
        'mysql' => 'josegonzalez\Queuesadilla\Engine\MysqlEngine',
        'null' => 'josegonzalez\Queuesadilla\Engine\NullEngine',
        'redis' => 'josegonzalez\Queuesadilla\Engine\RedisEngine',
        'synchronous' => 'josegonzalez\Queuesadilla\Engine\SynchronousEngine',
    ];

    /**
     * Internal flag for tracking whether or not configuration has been changed.
     *
     * @var bool
     */
    protected static $_dirtyConfig = false;

    /**
     * Flag for tracking whether or not queueing is enabled.
     *
     * @var bool
     */
    protected static $_enabled = true;

    /**
     * Used to manage individual queue engines attached to an actual queuer
     *
     * @var array
     */
    protected static $_queuers = [];

    /**
     * Queue Registry used for creating and using queue engines.
     *
     * @var \Josegonzalez\CakeQueuesadilla\Queue\QueueEngineRegistry
     */
    protected static $_registry;

    /**
     * Initializes registry and configurations
     *
     * @return void
     */
    protected static function _init()
    {
        if (empty(static::$_registry)) {
            static::$_registry = new QueueEngineRegistry();
        }
        if (static::$_dirtyConfig) {
            static::_loadConfig();
        }
        static::$_dirtyConfig = false;
    }

    /**
     * Load the defined configuration and create all the defined logging
     * adapters.
     *
     * @return void
     */
    protected static function _loadConfig()
    {
        foreach (static::$_config as $name => $properties) {
            if (isset($properties['engine'])) {
                $properties['className'] = $properties['engine'];
            }
            if (!static::$_registry->has($name)) {
                static::$_registry->load($name, $properties);
            }
        }
    }

    /**
     * Parses a DSN into a valid connection configuration
     *
     * Also remap the username and password to something parse_url
     * spits out by default.
     *
     * @param string $dsn The DSN string to convert to a configuration array
     * @return array The configuration array to be stored after parsing the DSN
     * @throws \InvalidArgumentException If not passed a string
     */
    public static function parseDsn($dsn)
    {
        $dsn = static::_parseDsn($dsn);
        if (is_array($dsn)) {
            if (isset($dsn['username'])) {
                $dsn['user'] = $dsn['username'];
            }

            if (isset($dsn['password'])) {
                $dsn['pass'] = $dsn['password'];
            }
        }
        return $dsn;
    }
    /**
     * Reset all the connected loggers.  This is useful to do when changing the logging
     * configuration or during testing when you want to reset the internal state of the
     * Log class.
     *
     * Resets the configured logging adapters, as well as any custom logging levels.
     * This will also clear the configuration data.
     *
     * @return void
     */
    public static function reset()
    {
        static::$_registry = null;
        static::$_config = [];
        static::$_dirtyConfig = true;
    }

    /**
     * Returns the Queue Registry instance used for creating and using queue engines.
     * Also allows for injecting of a new registry instance.
     *
     * @param \Cake\Core\ObjectRegistry|null $registry Injectable registry object.
     * @return \Cake\Core\ObjectRegistry
     */
    public static function registry(ObjectRegistry $registry = null)
    {
        if ($registry) {
            static::$_registry = $registry;
        }

        if (empty(static::$_registry)) {
            static::$_registry = new QueueEngineRegistry();
        }

        return static::$_registry;
    }

    /**
     * Finds and builds the instance of the required engine class.
     *
     * @param string $name Name of the config array that needs an engine instance built
     * @return void
     * @throws \InvalidArgumentException When a queue engine cannot be created.
     */
    protected static function _buildEngine($name)
    {
        $registry = static::registry();

        if (empty(static::$_config[$name]['className'])) {
            throw new InvalidArgumentException(
                sprintf('The "%s" queue configuration does not exist.', $name)
            );
        }

        $config = static::$_config[$name];
        $registry->load($name, $config);
    }

    /**
     * Fetch the engine attached to a specific configuration name.
     *
     * If the queue engine & configuration are missing an error will be
     * triggered.
     *
     * @param string $config The configuration name you want an engine for.
     * @return \josegonzalez\Queuesadilla\Engine\EngineInterface When caching is disabled a null engine will be returned.
     */
    public static function engine($config)
    {
        if (!static::$_enabled) {
            return new NullEngine();
        }

        $registry = static::registry();

        if (isset($registry->{$config})) {
            return $registry->{$config};
        }

        static::_buildEngine($config);
        return $registry->{$config};
    }

    /**
     * Fetch the queue attached to a specific engine configuration name.
     *
     * If the queue engine & configuration are missing an error will be
     * triggered.
     *
     * @param string $config The configuration name you want an engine for.
     * @return \josegonzalez\Queuesadilla\Queue
     */
    public static function queue($config)
    {
        if (isset(static::$_queuers[$config])) {
            return static::$_queuers[$config];
        }

        $engine = static::engine($config);
        return static::$_queuers[$config] = new Queuer($engine);
    }

    /**
     * Push a single job onto the queue.
     *
     * @param string $callable    a job callable
     * @param array  $args        an array of data to set for the job
     * @param array  $options     an array of options for publishing the job
     * @return bool the result of the push
     */
    public static function push($callable, $args = [], $options = [])
    {
        $config = Hash::get($options, 'config', 'default');
        $queue = static::queue($config);
        return $queue->push($callable, $args, $options);
    }
}
