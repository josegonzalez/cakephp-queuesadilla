Examples
--------

Basic example
~~~~~~~~~~~~~

You can start a queue off the ``jobs`` mysql table:

.. code:: shell

    # ensure everything is migrated and the jobs table exists
    bin/cake migrations migrate

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
    use josegonzalez\Cake\Queuesadilla\Queue\Queue;

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

See `here <https://github.com/josegonzalez/php-queuesadilla/blob/master/docs/defining-jobs.md>`_ for more information on defining jobs.
