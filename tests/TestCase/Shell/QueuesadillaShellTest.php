<?php
namespace Josegonzalez\CakeQueuesadilla\Test\Shell;

use Cake\Core\Plugin;
use Cake\Event\EventManager;
use Cake\Log\Log;
use Cake\TestSuite\TestCase;
use Josegonzalez\CakeQueuesadilla\Queue\Queue;
use Josegonzalez\CakeQueuesadilla\Shell\QueuesadillaShell;
use josegonzalez\Queuesadilla\Engine\NullEngine;
use Psr\Log\NullLogger;


class MyJob
{
    public function performFail()
    {
        return false;
    }
    public function performException()
    {
        throw new \Exception("Exception");
    }
    public function perform($job)
    {
        return true;
    }
}

/**
 * QueuesadillaShell test.
 */
class QueuesadillaShellTest extends TestCase
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.Josegonzalez\CakeQueuesadilla.jobs'
    ];

    /**
     * setup method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->io = $this->getMockBuilder('Cake\Console\ConsoleIo')
            ->getMock();
        $this->shell = new QueuesadillaShell($this->io);
        Log::reset();
        Queue::reset();
    }

    /**
     * teardown method
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();
        Log::reset();
        Queue::reset();
    }

    /**
     * Test that the worker is an instance of the correct object
     *
     * @return void
     */
    public function testGetEngine()
    {
        Log::config('stdout', ['engine' => 'File']);
        Queue::config('default', [
            'url' => 'mysql://username:password@localhost:80/database'
        ]);
        $logger = new NullLogger;
        $this->shell->params['config'] = 'default';
        $engine = $this->shell->getEngine($logger);
        $this->assertInstanceOf('josegonzalez\\Queuesadilla\\Engine\\MysqlEngine', $engine);
    }

    /**
     * Test that the worker is an instance of the correct object
     *
     * @return void
     */
    public function testGetWorker()
    {
        $logger = new NullLogger;
        $engine = new NullEngine;
        $this->shell->params['worker'] = 'Sequential';
        $worker = $this->shell->getWorker($engine, $logger);
        $this->assertInstanceOf('josegonzalez\\Queuesadilla\\Worker\\SequentialWorker', $worker);

        $this->shell->params['worker'] = 'Test';
        $worker = $this->shell->getWorker($engine, $logger);
        $this->assertInstanceOf('josegonzalez\\Queuesadilla\\Worker\\TestWorker', $worker);
    }

    /**
     * Test that the option parser is shaped right.
     *
     * @return void
     */
    public function testGetOptionParser()
    {
        $this->shell->loadTasks();
        $parser = $this->shell->getOptionParser();
        $commands = $parser->options();
        $this->assertArrayHasKey('queue', $commands);
        $this->assertArrayHasKey('logger', $commands);
        $this->assertArrayHasKey('worker', $commands);
    }

    /**
     * Test that all events maped to the CakePHP event system
     *
     * @return void
     */
    public function testAttachEvents()
    {
        $connectionFailed = false;
        EventManager::instance()->on(
            'Queue.Worker.connectionFailed',
            function() use (&$connectionFailed) {
                $connectionFailed = true;
            }
        );

        $maxIterations = false;
        EventManager::instance()->on(
            'Queue.Worker.maxIterations',
            function() use (&$maxIterations){
                $maxIterations = true;
            }
        );

        $maxRuntime = false;
        EventManager::instance()->on(
            'Queue.Worker.maxRuntime',
            function() use (&$maxRuntime) {
                $maxRuntime = true;
            }
        );

        $seen = false;
        EventManager::instance()->on(
            'Queue.Worker.job.seen',
            function() use (&$seen) {
                $seen = true;
            }
        );

        $empty = false;
        EventManager::instance()->on(
            'Queue.Worker.job.empty',
            function() use (&$empty) {
                $empty = true;
            }
        );

        $invalid = false;
        EventManager::instance()->on(
            'Queue.Worker.job.invalid',
            function() use (&$invalid) {
                $invalid = true;
            }
        );

        $exception = false;
        EventManager::instance()->on(
            'Queue.Worker.job.exception',
            function() use (&$exception) {
                $exception = true;
            }
        );

        $success = false;
        EventManager::instance()->on(
            'Queue.Worker.job.success',
            function() use (&$success) {
                $success = true;
            }
        );

        $failure = false;
        EventManager::instance()->on(
            'Queue.Worker.job.failure',
            function() use (&$failure) {
                $failure = true;
            }
        );

        $afterEnqueue = false;
        EventManager::instance()->on(
            'Queue.Queue.afterEnqueue',
            function() use (&$afterEnqueue) {
                $afterEnqueue = true;
            }
        );

        Log::config('stdout', ['engine' => 'File']);

        $this->shell->params['config'] = 'default';
        $this->shell->params['logger'] = 'stdout';
        $this->shell->params['worker'] = 'Sequential';

        //Test connectionFailed event
        Queue::config('default', [
            'url' => 'mysql://foo:bar@localhost:80/database',
            'engine' => 'josegonzalez\Queuesadilla\Engine\MysqlEngine',
            'maxIterations' => 1
        ]);

        $this->shell->main();
        $this->assertTrue($connectionFailed);

        //Test maxIterations event
        Queue::reset();
        Queue::config('default', [
            'url' => getenv('db_dsn'),
            'engine' => 'josegonzalez\Queuesadilla\Engine\MysqlEngine',
            'maxIterations' => 1
        ]);

        $this->shell->main();
        $this->assertTrue($maxIterations);

        //Test maxRuntime event
        Queue::reset();
        Queue::config('default', [
            'url' => getenv('db_dsn'),
            'engine' => 'josegonzalez\Queuesadilla\Engine\MysqlEngine',
            'maxRuntime' => 1
        ]);

        $this->shell->main();
        $this->assertTrue($maxRuntime);

        //Test afterEnque, seen, empty and sucess event
        Queue::reset();
        Queue::config('default', [
            'url' => getenv('db_dsn'),
            'engine' => 'josegonzalez\Queuesadilla\Engine\MysqlEngine',
            'maxIterations' => 2
        ]);
        Queue::push([
            '\Josegonzalez\CakeQueuesadilla\Test\Shell\MyJob',
            'perform'
        ]);

        $this->shell->main();
        $this->assertTrue($afterEnqueue);
        $this->assertTrue($seen);
        $this->assertTrue($empty);
        $this->assertTrue($success);

        //Test invalid event
        Queue::reset();
        Queue::config('default', [
            'url' => getenv('db_dsn'),
            'engine' => 'josegonzalez\Queuesadilla\Engine\MysqlEngine',
            'maxIterations' => 1
        ]);

        // Test invalid event
        Queue::push([
            '\Josegonzalez\CakeQueuesadilla\Test\Shell\MyJob',
            'doesNotExist'
        ]);
        $this->shell->main();
        $this->assertTrue($invalid);

        // Test exception event
        Queue::push([
            '\Josegonzalez\CakeQueuesadilla\Test\Shell\MyJob',
            'performException'
        ]);
        $this->shell->main();
        $this->assertTrue($exception);

        // Test failure event
        Queue::push([
            '\Josegonzalez\CakeQueuesadilla\Test\Shell\MyJob',
            'performFail'
        ]);
        $this->shell->main();
        $this->assertTrue($failure);
    }
}
