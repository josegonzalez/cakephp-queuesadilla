Installation
------------

The only officialy supported method of installing this plugin is via composer.

Using `Composer <http://getcomposer.org/>`__
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

`View on
Packagist <https://packagist.org/packages/josegonzalez/cakephp-queuesadilla>`__,
and copy the json snippet for the latest version into your project's
``composer.json``. Eg, v. 3.0.0 would look like this:

.. code:: json

    {
        "require": {
            "josegonzalez/cakephp-queuesadilla": "3.0.0"
        }
    }

Enable plugin
~~~~~~~~~~~~~

You need to enable the plugin your ``config/bootstrap.php`` file:

.. code:: php

    <?php
    Plugin::load('Josegonzalez/CakeQueuesadilla');

If you are already using ``Plugin::loadAll();``, then this is not
necessary.