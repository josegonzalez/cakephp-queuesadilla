<?php
namespace Josegonzalez\CakeQueuesadilla\Test\Shell;

use Cake\Core\Plugin;
use Cake\TestSuite\TestCase;
use Josegonzalez\CakeQueuesadilla\Shell\QueuesadillaShell;

/**
 * QueuesadillaShell test.
 */
class QueuesadillaShellTest extends TestCase
{

    /**
     * setup method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->io = $this->getMock('Cake\Console\ConsoleIo');
        $this->shell = new QueuesadillaShell($this->io);
    }

    /**
     * Test that we get an engine config
     *
     * @return void
     */
    public function testGetEngineConfig()
    {
        $config = $this->shell->getEngineConfig(['url' => true]);
        $this->assertEquals(['url' => true], $config);

        $this->shell->params['queue'] = 'jobs';
        $config = $this->shell->getEngineConfig(['url' => true]);
        $this->assertEquals(['url' => true, 'queue' => 'jobs'], $config);
    }

    /**
     * testNoDefaultConfig
     *
     * @expectedException Exception
     * @expectedExceptionMessage Invalid Queuesadilla.engine config
     */
    public function testNoDefaultConfig()
    {
        $this->shell->getEngineConfig();
    }

    /**
     * testNoDefaultConfig
     *
     * @expectedException Exception
     * @expectedExceptionMessage Invalid Queuesadilla.engine config
     */
    public function testEmptyDefaultConfig()
    {
        $this->shell->getEngineConfig([]);
    }

    /**
     * Test that the logger name is a string
     *
     * @return void
     */
    public function testGetLoggerName()
    {
        $loggerName = $this->shell->getLoggerName('name');
        $this->assertEquals('name', $loggerName);

        $this->shell->params['logger'] = 'stdout';
        $loggerName = $this->shell->getLoggerName();
        $this->assertEquals('stdout', $loggerName);
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
        $this->assertArrayHasKey('engine', $commands);
        $this->assertArrayHasKey('queue', $commands);
        $this->assertArrayHasKey('logger', $commands);
        $this->assertArrayHasKey('worker', $commands);
    }
}
