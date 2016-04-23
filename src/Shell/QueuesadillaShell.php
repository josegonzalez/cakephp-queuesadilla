<?php
namespace Josegonzalez\CakeQueuesadilla\Shell;

use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\Log\Log;
use Exception;

class QueuesadillaShell extends Shell
{
    /**
     * Override main() to handle action
     * Starts a Queuesadilla worker
     *
     * @return void
     */
    public function main()
    {
        $engine = $this->params['engine'];
        $worker = $this->params['worker'];
        $EngineClass = "josegonzalez\\Queuesadilla\\Engine\\" . $engine . 'Engine';
        $WorkerClass = "josegonzalez\\Queuesadilla\\Worker\\" . $worker . "Worker";

        $config = Configure::read('Queuesadilla.engine');
        $engineConfig = $this->getEngineConfig($config);

        $defaultLoggerName = Configure::read('Queuesadilla.logger');
        $loggerName = $this->getLoggerName($defaultLoggerName);

        $logger = Log::engine($loggerName);
        $engine = new $EngineClass($logger, $engineConfig);

        $worker = new $WorkerClass($engine, $logger);
        $worker->work();
    }

    /**
     * Retrieves default configuration for the engine
     *
     * @param array $config Default engine configuration
     * @return array
     */
    protected function getEngineConfig(array $config = [])
    {
        if (empty($config)) {
            throw new Exception('Invalid Queuesadilla.engine config');
        }

        if (!empty($this->params['queue'])) {
            $config['queue'] = $this->params['queue'];
        }
        return $config;
    }

    /**
     * Retrieves a name of a logger engine to use
     *
     * @param array $config Default logger name
     * @return string
     */
    protected function getLoggerName($loggerName)
    {
        if (empty($loggerName)) {
            $loggerName = $this->params['logger'];
        }
        return $loggerName;
    }

    /**
     * Gets the option parser instance and configures it.
     *
     * @return \Cake\Console\ConsoleOptionParser
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();
        $parser->addOption('engine', [
            'choices' => [
                'Beanstalk',
                'Iron',
                'Memory',
                'Mysql',
                'Null',
                'Redis',
                'Synchronous',
            ],
            'default' => 'Mysql',
            'help' => 'Name of engine',
            'short' => 'e',
        ]);
        $parser->addOption('queue', [
            'help' => 'Name of a queue',
            'short' => 'q',
        ]);
        $parser->addOption('logger', [
            'help' => 'Name of a configured logger',
            'default' => 'stdout',
            'short' => 'l',
        ]);
        $parser->addOption('worker', [
            'choices' => [
                'Sequential',
                'Test',
            ],
            'default' => 'Sequential',
            'help' => 'Name of worker class',
            'short' => 'w',
        ])->description(__('Runs a Queuesadilla worker.'));
        return $parser;
    }
}
