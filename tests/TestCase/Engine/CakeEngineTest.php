<?php
namespace Josegonzalez\CakeQueuesadilla\Test\Engine;

use Cake\Datasource\ConnectionManager;
use Cake\TestSuite\TestCase;
use Josegonzalez\CakeQueuesadilla\Queue\Queue;

/**
 * CakeEngineTest class
 *
 */
class CakeEngineTest extends TestCase
{

    /**
     * setup method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        Queue::reset();
        $this->Logger = $this->getMockBuilder('Cake\Log\Engine\FileLog')
            ->setMethods(['error'])
            ->getMock();
    }

    /**
     * teardown method
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();
        Queue::reset();
    }

    /**
     * test config() with valid datasource name
     *
     * @return void
     */
    public function testValidConfig()
    {
        Queue::setConfig('valid', [
            'className' => 'Josegonzalez\CakeQueuesadilla\Engine\CakeEngine',
            'datasource' => 'test'
        ]);
        $engine = Queue::engine('valid');
        $this->assertInstanceOf('Josegonzalez\CakeQueuesadilla\Engine\CakeEngine', $engine);
        $this->assertEquals('test', $engine->config('datasource'));
    }

    /**
     * test config() with invalid datasource name
     *
     * @return void
     */
    public function testInvalidDatasourceName()
    {
        $this->Logger->expects($this->once())
            ->method('error')
            ->with('The datasource configuration "wrong-datasource" was not found.');
        Queue::setConfig('invalid', [
            'className' => 'Josegonzalez\CakeQueuesadilla\Engine\CakeEngine',
            'datasource' => 'wrong-datasource'
        ]);
        $engine = Queue::engine('invalid');
        $engine->setLogger($this->Logger);
        $this->assertInstanceOf('Josegonzalez\CakeQueuesadilla\Engine\CakeEngine', $engine);
        $this->assertEquals('wrong-datasource', $engine->config('datasource'));
        $engine->connect();
    }

    /**
     * test config() with invalid datasource config (missing datasource class)
     *
     * @return void
     */
    public function testInvalidConfig()
    {
        $this->Logger->expects($this->once())
            ->method('error')
            ->with('Datasource class wrong-datasource-class could not be found. ');
        Queue::setConfig('invalid', [
            'className' => 'Josegonzalez\CakeQueuesadilla\Engine\CakeEngine',
            'datasource' => 'wrong-datasource-class'
        ]);
        ConnectionManager::setConfig('wrong-datasource-class', [
            'user' => 'invalid-user',
            'password' => 'invalid-password',
            'host' => 'localhost'
        ]);
        $engine = Queue::engine('invalid');
        $engine->setLogger($this->Logger);
        $this->assertFalse($engine->connect());
    }

    /**
     * test config() with invalid datasource connection params
     *
     * @return void
     */
    public function testInvalidConfigParams()
    {
        $this->Logger->expects($this->once())
            ->method('error')
            ->with($this->matchesRegularExpression('/Connection to database could not be established:/'));
        Queue::setConfig('invalid', [
            'className' => 'Josegonzalez\CakeQueuesadilla\Engine\CakeEngine',
            'datasource' => 'wrong-datasource-params'
        ]);
        ConnectionManager::setConfig('wrong-datasource-params', [
            'className' => 'Cake\Database\Connection',
            'driver' => 'Cake\Database\Driver\Mysql',
            'user' => 'invalid-user',
            'password' => 'invalid-password',
            'host' => 'localhost'
        ]);
        $engine = Queue::engine('invalid');
        $engine->setLogger($this->Logger);
        $this->assertFalse($engine->connect());
    }

    /**
     * test config with noDatasource (default)
     *
     * @return void
     */
    public function testNoDatasource()
    {
        Queue::setConfig('noDatasource', [
            'className' => 'Josegonzalez\CakeQueuesadilla\Engine\CakeEngine',
        ]);
        $engine = Queue::engine('noDatasource');
        $this->assertInstanceOf('Josegonzalez\CakeQueuesadilla\Engine\CakeEngine', $engine);
        $this->assertEquals('default', $engine->config('datasource'));
    }

    /**
     * test connection
     *
     * @return void
     */
    public function testConnection()
    {
        Queue::setConfig('valid', [
            'className' => 'Josegonzalez\CakeQueuesadilla\Engine\CakeEngine',
            'datasource' => 'test'
        ]);
        $engine = Queue::engine('valid');
        $this->assertInstanceOf('Josegonzalez\CakeQueuesadilla\Engine\CakeEngine', $engine);
        $this->assertEquals('test', $engine->config('datasource'));
        $this->assertTrue($engine->connect());
        $this->assertEquals(ConnectionManager::get('test')->getDriver()->getConnection(), $engine->connection());
    }
}
