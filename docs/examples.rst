Examples
========

Basic example
-------------

You can start a queue off the ``jobs`` mysql table:

.. code:: shell

    # ensure everything is migrated and the jobs table exists
    bin/cake migrations migrate --plugin Josegonzalez/CakeQueuesadilla

    # default queue
    bin/cake queuesadilla

    # also the default queue
    bin/cake queuesadilla --queue default

    # some other queue
    bin/cake queuesadilla --queue some-other-default

    # use a different config
    bin/cake queuesadilla --config other

Need to queue something up?

.. code:: php

    <?php
    use Josegonzalez\CakeQueuesadilla\Queue\Queue;

    // a function in the global scope
    function some_job($job) {
        var_dump($job->data());
    }

    // uses the 'default' engine
    Queue::push('some_job', [
        'id' => 7,
        'message' => 'hi'
    ]);

    // uses the 'other' engine
    Queue::push('some_job', [
        'id' => 7,
        'message' => 'hi'
    ], ['config' => 'other']);

    // uses the 'default' engine
    // on the 'slow' queue
    Queue::push('some_job', [
        'id' => 7,
        'message' => 'hi'
    ], ['config' => 'other', 'queue' => 'slow']);

    ?>

You can also add the ``Josegonzalez\CakeQueuesadilla\Traits\QueueTrait`` to any class in order to have a protected ``push`` method added to the class so that you can do ``$this->push()``.

See `here <https://github.com/josegonzalez/php-queuesadilla/blob/master/docs/defining-jobs.md>`_ for more information on defining jobs.

Need to react to Queuesadilla events inside your CakePHP application?

All worker and queue events are propagated to the CakePHP event system.
To hook into them, do something like this:

.. code:: php

    // Worker events
    <?php
    use Cake\Event\EventManager;

    EventManager::instance()->on('Queue.Worker.job.success', function ($event, $worker) {
        // Access the job data
         $job = $worker->data['job']
    });

    // Queue event
    <?php
    use Cake\Event\EventManager;

    EventManager::instance()->on('Queue.Queue.afterEnqueue', function ($event, $queue) {
        // Access the queue item and the success state
        $item = $queue->data['item'];
        $success = $queue->data['success'];
    });
