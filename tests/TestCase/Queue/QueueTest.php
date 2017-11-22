<?php
namespace Josegonzalez\CakeQueuesadilla\Test\Queue;

use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Log\Engine\FileLog;
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
    public function setUp()
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
    public function tearDown()
    {
        parent::tearDown();
        Log::reset();
        Queue::reset();
    }

    /**
     * test all the errors from failed logger imports
     *
     * @expectedException \InvalidArgumentException
     * @return void
     */
    public function testImportingQueueEngineFailure()
    {
        Queue::setConfig('fail', []);
        Queue::engine('fail');
    }

    /**
     * test config() with valid key name
     *
     * @return void
     */
    public function testValidKeyName()
    {
        Log::setConfig('stdout', ['engine' => 'File']);
        Queue::setConfig('valid', [
            'url' => 'mysql://username:password@localhost:80/database'
        ]);
        $engine = Queue::engine('valid');
        $this->assertInstanceOf('josegonzalez\Queuesadilla\Engine\MysqlEngine', $engine);
    }

    /**
     * test that loggers have to implement the correct interface.
     *
     * @expectedException \RuntimeException
     * @return void
     */
    public function testNotImplementingInterface()
    {
        Queue::setConfig('fail', ['engine' => '\stdClass']);
        Queue::engine('fail');
    }

    /**
     * explicit tests for drop()
     *
     * @return void
     */
    public function testDrop()
    {
        Queue::setConfig('default', [
            'url' => 'mysql://username:password@localhost:80/database'
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
     * @expectedException \BadMethodCallException
     * @return void
     */
    public function testConfigErrorOnReconfigure()
    {
        Queue::setConfig('tests', ['url' => 'mysql://username:password@localhost:80/database']);
        Queue::setConfig('tests', ['url' => 'null://']);
    }
}
