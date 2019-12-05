<?php
declare(strict_types=1);

namespace Josegonzalez\CakeQueuesadilla\Test\Shell;

use Cake\Log\Log;
use Cake\TestSuite\ConsoleIntegrationTestCase;
use Josegonzalez\CakeQueuesadilla\Queue\Queue;
use Josegonzalez\CakeQueuesadilla\Shell\QueuesadillaShell;
use josegonzalez\Queuesadilla\Engine\NullEngine;
use Psr\Log\NullLogger;

/**
 * QueuesadillaShell test.
 */
class QueuesadillaShellTest extends ConsoleIntegrationTestCase
{
    /**
     * setup method
     *
     * @return void
     */
    public function setUp(): void
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
    public function tearDown(): void
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
    public function testGetEngine(): void
    {
        Log::setConfig('stdout', ['engine' => 'File']);
        Queue::setConfig('default', [
            'url' => 'mysql://username:password@localhost:80/database',
        ]);
        $logger = new NullLogger();
        $this->shell->params['config'] = 'default';
        $engine = $this->shell->getEngine($logger);
        $this->assertInstanceOf('josegonzalez\\Queuesadilla\\Engine\\MysqlEngine', $engine);
    }

    /**
     * Test that the worker is an instance of the correct object
     *
     * @return void
     */
    public function testGetWorker(): void
    {
        $logger = new NullLogger();
        $engine = new NullEngine();
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
    public function testGetOptionParser(): void
    {
        $this->shell->loadTasks();
        $parser = $this->shell->getOptionParser();
        $commands = $parser->options();
        $this->assertArrayHasKey('queue', $commands);
        $this->assertArrayHasKey('logger', $commands);
        $this->assertArrayHasKey('worker', $commands);
    }

    /**
     * Test that the queuesadilla shell executes successfully.
     *
     * @return void
     */
    public function testMainSuccess(): void
    {
        Queue::setConfig('default', [
            'url' => 'memory://',
            'maxRuntime' => 1,
        ]);

        $this->exec('queuesadilla');
        $this->assertExitCode(0);
    }
}
