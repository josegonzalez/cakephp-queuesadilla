<?php
declare(strict_types=1);

namespace Josegonzalez\CakeQueuesadilla\Traits;

use Josegonzalez\CakeQueuesadilla\Queue\Queue;

trait QueueTrait
{
    /**
     * Push a single job onto the queue.
     *
     * @param string $callable    a job callable
     * @param array  $args        an array of data to set for the job
     * @param array  $options     an array of options for publishing the job
     * @return bool the result of the push
     */
    protected function push(callable $callable, array $args = [], array $options = []): bool
    {
        return Queue::push($callable, $args, $options);
    }
}
