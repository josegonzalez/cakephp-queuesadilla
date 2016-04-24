Plugin configuration options
----------------------------

This plugin can be configured via your ``config/app.php``. It works similar
to how all other CakePHP engine-based libraries work (Cache, Email, Log), and
as such you can have multiple backends under different names. Here is an example
config stanza:

.. code:: php

    /**
     * Configures the Queuesadilla engine to read from mysql as it's database
     */
    'Queuesadilla' => [
        'default' => [
            'url' => env('QUEUESADILLA_DEFAULT_URL', ''),
        ],
    ],

Note that the config array is passed as settings to the queueing engine. Please
refer to the Queuesadilla `docs <http://josegonzalez.viewdocs.io/php-queuesadilla/>`_
for more information on how each engine can be configured.
