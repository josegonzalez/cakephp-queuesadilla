<?php
declare(strict_types=1);

namespace Josegonzalez\CakeQueuesadilla\Test\Queue;

use Cake\Log\Log;
use Cake\TestSuite\TestCase;
use Josegonzalez\CakeQueuesadilla\Queue\Queue;

/**
 * QueueTest class
 *
 */
class QueueTest extends TestCase
{
    /**
     * setup method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
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
     * test all the errors from failed logger imports
     *
     * @return void
     */
    public function testImportingQueueEngineFailure(): void
    {
        $this->expectException('\InvalidArgumentException');

        Queue::setConfig('fail', []);
        Queue::engine('fail');
    }

    /**
     * test config() with valid key name
     *
     * @return void
     */
    public function testValidKeyName(): void
    {
        Log::setConfig('stdout', ['engine' => 'File']);
        Queue::setConfig('valid', [
            'url' => 'mysql://username:password@localhost:80/database',
        ]);
        $engine = Queue::engine('valid');
        $this->assertInstanceOf('josegonzalez\Queuesadilla\Engine\MysqlEngine', $engine);
    }

    /**
     * test that loggers have to implement the correct interface.
     *
     * @return void
     */
    public function testNotImplementingInterface(): void
    {
        $this->expectException('\RuntimeException');

        Queue::setConfig('fail', ['engine' => '\stdClass']);
        Queue::engine('fail');
    }

    /**
     * explicit tests for drop()
     *
     * @return void
     */
    public function testDrop(): void
    {
        Queue::setConfig('default', [
            'url' => 'mysql://username:password@localhost:80/database',
        ]);
        $result = Queue::configured();
        $this->assertContains('default', $result);

        $this->assertTrue(Queue::drop('default'), 'Should be dropped');
        $this->assertFalse(Queue::drop('default'), 'Already gone');

        $result = Queue::configured();
        $this->assertNotContains('default', $result);
    }

    /**
     * Ensure you cannot reconfigure a log adapter.
     *
     * @return void
     */
    public function testConfigErrorOnReconfigure(): void
    {
        $this->expectException('\BadMethodCallException');

        Queue::setConfig('tests', ['url' => 'mysql://username:password@localhost:80/database']);
        Queue::setConfig('tests', ['url' => 'null://']);
    }

    /**
     * Ensure Queue resets correctly
     *
     * @return void
     */
    public function testReset(): void
    {
        // Set the initial config
        Queue::setConfig('test', [
            'url' => 'null://',
        ]);

        $registry = Queue::registry();
        $engine = Queue::engine('test');
        $queue = Queue::queue('test');

        // Reset the queue
        Queue::reset();

        // Set the config after reset and assert that objects have been recreated
        Queue::setConfig('test', [
            'url' => 'null://',
        ]);
        $newRegistry = Queue::registry();
        $newEngine = Queue::engine('test');
        $newQueue = Queue::queue('test');
        $this->assertNotSame($registry, $newRegistry, 'After reset the registry references the old object');
        $this->assertNotSame($engine, $newEngine, 'After reset the engine references the old object');
        $this->assertNotSame($queue, $newQueue, 'After reset the queue references the old object');
    }
}
