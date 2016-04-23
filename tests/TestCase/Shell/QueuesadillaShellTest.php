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
