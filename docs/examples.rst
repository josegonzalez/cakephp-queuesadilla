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

    # use a different engine
    bin/cake queuesadilla --engine redis

You can customize the engine configuration under the ``Queuesadilla.engine`` array in ``config/app.php``. At the moment, it defaults to a config compatible with your application's mysql database config.

Need to queue something up?


.. code:: php

    <?php
    // assuming mysql engine
    use josegonzalez\Queuesadilla\Engine\MysqlEngine;
    use josegonzalez\Queuesadilla\Queue;

    // get the engine config:
    $config = Configure::read('Queuesadilla.engine');

    // instantiate the things
    $engine = new MysqlEngine($config);
    $queue = new Queue($engine);

    // a function in the global scope
    function some_job($job) {
        var_dump($job->data());
    }
    $queue->push('some_job', [
        'id' => 7,
        'message' => 'hi'
    ]);

    ?>

See `here <https://github.com/josegonzalez/php-queuesadilla/blob/master/docs/defining-jobs.md>`_ for more information on defining jobs.
