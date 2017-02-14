<?php
namespace Josegonzalez\CakeQueuesadilla\Shell;

use Cake\Console\Shell;
use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\Log\Log;
use Cake\Utility\Hash;
use Josegonzalez\CakeQueuesadilla\Queue\Queue;

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
        $logger = Log::engine($this->params['logger']);
        $engine = $this->getEngine($logger);
        $worker = $this->getWorker($engine, $logger);
        $worker->work();
    }

    /**
     * Retrieves a queue engine
     *
     * @param \Psr\Log\LoggerInterface $logger logger
     * @return \josegonzalez\Queuesadilla\Engine\Base
     */
    public function getEngine($logger)
    {
        $config = Hash::get($this->params, 'config');
        $engine = Queue::engine($config);
        $engine->setLogger($logger);
        if (!empty($this->params['queue'])) {
            $engine->config('queue', $this->params['queue']);
        }

        return $engine;
    }

    /**
     * Retrieves a queue worker
     *
     * @param \josegonzalez\Queuesadilla\Engine\Base $engine engine to run
     * @param \Psr\Log\LoggerInterface $logger logger
     * @return \josegonzalez\Queuesadilla\Worker\Base
     */
    public function getWorker($engine, $logger)
    {
        $workerName = $this->params['worker'];
        $workerClass = "josegonzalez\\Queuesadilla\\Worker\\" . $workerName . "Worker";

        $worker = new $workerClass($engine, $logger, [
            'queue' => $engine->config('queue'),
            'maxRuntime' => $engine->config('maxRuntime')
        ]);

        $this->__attachEvents($worker);

        return $worker;
    }

    /**
     * Gets the option parser instance and configures it.
     *
     * @return \Cake\Console\ConsoleOptionParser
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();
        $parser->addOption('config', [
            'default' => 'default',
            'help' => 'Name of a queue config to use',
            'short' => 'c',
        ]);
        $parser->addOption('queue', [
            'help' => 'Name of queue to override from loaded config',
            'short' => 'Q',
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

    /**
     * Attach the league/event events to the CakePHP event system
     *
     * @param \josegonzalez\Queuesadilla\Worker\Base $worker worker instance
     * @return void
     */
    private function __attachEvents($worker)
    {
        $worker->attachListener('Worker.connectionFailed', function ($event) {
            $event = new Event('CakeQueuesadilla.Worker.connectionFailed', $this, [
                'workerEvent' => $event
            ]);
            EventManager::instance()->dispatch($event);
        });
        $worker->attachListener('Worker.maxIterations', function ($event) {
            $event = new Event('CakeQueuesadilla.Worker.maxIterations', $this, [
                'workerEvent' => $event
            ]);
            EventManager::instance()->dispatch($event);
        });
        $worker->attachListener('Worker.maxRuntime', function ($event) {
            $event = new Event('CakeQueuesadilla.Worker.maxRuntime', $this, [
                'workerEvent' => $event
            ]);
            EventManager::instance()->dispatch($event);
        });
        $worker->attachListener('Worker.job.seen', function ($event) {
            $event = new Event('CakeQueuesadilla.Worker.job.seen', $this, [
                'workerEvent' => $event
            ]);
            EventManager::instance()->dispatch($event);
        });
        $worker->attachListener('Worker.job.empty', function ($event) {
            $event = new Event('CakeQueuesadilla.Worker.job.empty', $this, [
                'workerEvent' => $event
            ]);
            EventManager::instance()->dispatch($event);
        });
        $worker->attachListener('Worker.job.invalid', function ($event) {
            $event = new Event('CakeQueuesadilla.Worker.job.invalid', $this, [
                'workerEvent' => $event
            ]);
            EventManager::instance()->dispatch($event);
        });
        $worker->attachListener('Worker.job.exception', function ($event) {
            $event = new Event('CakeQueuesadilla.Worker.job.exception', $this, [
                'workerEvent' => $event
            ]);
            EventManager::instance()->dispatch($event);
        });
        $worker->attachListener('Worker.job.success', function ($event) {
            $event = new Event('CakeQueuesadilla.Worker.job.success', $this, [
                'workerEvent' => $event
            ]);
            EventManager::instance()->dispatch($event);
        });
        $worker->attachListener('Worker.job.failure', function ($event) {
            $event = new Event('CakeQueuesadilla.Worker.job.failure', $this, [
                'workerEvent' => $event
            ]);
            EventManager::instance()->dispatch($event);
        });
    }
}
