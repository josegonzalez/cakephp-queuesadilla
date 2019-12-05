<?php
declare(strict_types=1);

namespace Josegonzalez\CakeQueuesadilla\Queue;

use Cake\Core\App;
use Cake\Core\ObjectRegistry;
use Cake\Log\Log;
use Cake\Utility\Hash;
use josegonzalez\Queuesadilla\Engine\EngineInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;

/**
 * Registry of loaded queue engines
 */
class QueueEngineRegistry extends ObjectRegistry
{
    /**
     * Resolve a queue engine classname.
     *
     * Part of the template method for Cake\Core\ObjectRegistry::load()
     *
     * @param string $class Partial classname to resolve.
     * @return string Either the correct classname or null.
     */
    protected function _resolveClassName(string $class): ?string
    {
        return App::className($class, 'Queue/Engine', 'Engine');
    }

    /**
     * Throws an exception when a queue engine is missing.
     *
     * Part of the template method for Cake\Core\ObjectRegistry::load()
     *
     * @param string $class The classname that is missing.
     * @param string|null $plugin The plugin the queue engine is missing in.
     * @return void
     * @throws \RuntimeException
     */
    protected function _throwMissingClassError(string $class, ?string $plugin): void
    {
        throw new RuntimeException(sprintf('Could not load class %s', $class));
    }

    /**
     * Create the queue engine instance.
     *
     * Part of the template method for Cake\Core\ObjectRegistry::load()
     *
     * @param string|\josegonzalez\Queuesadilla\Engine\EngineInterface $class The classname or object to make.
     * @param string $alias The alias of the object.
     * @param array $settings An array of settings to use for the queue engine.
     * @return \josegonzalez\Queuesadilla\Engine\EngineInterface The constructed queue engine class.
     * @throws \RuntimeException when an object doesn't implement the correct interface.
     */
    protected function _create($class, string $alias, array $settings): EngineInterface
    {
        if (is_callable($class)) {
            $class = $class($alias);
        }

        if (is_object($class)) {
            $instance = $class;
        }

        if (!isset($instance)) {
            $key = PHP_SAPI === 'cli' ? 'stdout' : 'default';
            $logger = Hash::get($settings, 'logger', $key);
            if (!($logger instanceof LoggerInterface)) {
                $logger = Log::engine($logger);
            }
            if (!($logger instanceof LoggerInterface)) {
                $logger = Log::engine('debug');
            }

            if ($logger === false) {
                $logger = null;
            }

            $instance = new $class($logger, $settings);
        }

        if ($instance instanceof EngineInterface) {
            return $instance;
        }

        throw new RuntimeException(
            'Queue Engines must implement josegonzalez\Queuesadilla\Engine\EngineInterface.'
        );
    }

    /**
     * Remove a single queue engine from the registry.
     *
     * @param string $name The queue engine name.
     * @return void
     */
    public function unload(string $name): void
    {
        unset($this->_loaded[$name]);
    }
}
