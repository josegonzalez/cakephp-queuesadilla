Plugin configuration options
============================

This plugin can be configured via your ``config/app.php``. It works similar
to how all other CakePHP engine-based libraries work (Cache, Email, Log), and
as such you can have multiple backends under different names. Here is an example
config stanza:

.. code:: php

    /**
     * Configures the Queuesadilla engine to read from mysql as it's database
     * The `url` parameter is a datasource name, as supported by CakePHP and other frameworks.
     *
     * Some examples:
     * - beanstalkd:  beanstalk://127.0.0.1:11300?queue=default&timeout=1
     * - in memory:   memory:///?queue=default&timeout=1
     * - mysql:       mysql://travis@127.0.0.1:3306/database_name?queue=default&timeout=1
     * - /dev/null:   null:///?queue=default&timeout=1
     * - redis:       redis://travis@127.0.0.1:6379/0?queue=default&timeout=1
     * - synchronous: synchronous:///?queue=default&timeout=1
     * - postgres:    pgsql://postgres@127.0.0.1:5432/database_name?queue=default
     */
    'Queuesadilla' => [
        'default' => [
            'url' => env('QUEUESADILLA_DEFAULT_URL', ''),
        ],
    ],
    
Note that the config array is passed as settings to the queueing engine. Please
refer to the Queuesadilla `docs <http://josegonzalez.viewdocs.io/php-queuesadilla/>`_
for more information on how each engine can be configured.

**Please Note:**

If your job callback is performing a database involved operation, you should check if the CakePHP database connection is still alive.
It can happen that your database connection timed out, when no jobs were acknowledged for a long time.
A way to come around this, is to disconnect the CakePHP connection every time a job succeeded or failed.

To do so, create a subclass of the ``QueuesadillaShell`` and implement two event listeners inside the ``getWorker`` method.
You can easily do that by baking a shell with ``bin/cake bake shell MyQueuesadillaShell``  command and alter the created
class as below:

.. code:: php
    <?php
    namespace App\Shell;

    use Cake\Datasource\ConnectionManager;
    use Josegonzalez\CakeQueuesadilla\Shell\QueuesadillaShell;

    /**
     * AlteredQueuesadillaShell shell command.
     */
    class AlteredQueuesadillaShellShell extends QueuesadillaShell
    {

        /**
         * @param \josegonzalez\Queuesadilla\Engine\Base $engine
         * @param \Psr\Log\LoggerInterface $logger
         * @return \josegonzalez\Queuesadilla\Worker\Base
         */
        public function getWorker($engine, $logger)
        {
            $worker = parent::getWorker($engine, $logger);

            $worker->attachListener('Worker.job.success', function ($event) {
                ConnectionManager::get('default')->disconnect();
            });
            $worker->attachListener('Worker.job.failure', function ($event) {
                ConnectionManager::get('default')->disconnect();
            });

            return $worker;
        }
    }
