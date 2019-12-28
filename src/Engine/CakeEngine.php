<?php
declare(strict_types=1);

namespace Josegonzalez\CakeQueuesadilla\Engine;

use Cake\Core\Exception\Exception;
use Cake\Datasource\ConnectionManager;
use Cake\Utility\Hash;
use josegonzalez\Queuesadilla\Engine\PdoEngine;

class CakeEngine extends PdoEngine
{
    /**
     * Base config
     * @var array
     */
    protected $baseConfig = [
        'delay' => null,
        'expires_in' => null,
        'priority' => 0,
        'queue' => 'default',
        'attempts' => 0,
        'attempts_delay' => 600,
        'table' => 'jobs',
        'datasource' => 'default',
    ];

    /**
     * {@inheritDoc}
     */
    public function connect()
    {
        $config = $this->settings;
        try {
            /** @var \Cake\Database\Connection $connection */
            $connection = ConnectionManager::get(Hash::get($config, 'datasource'));
            $connection->connect();
            $this->connection = $connection->getDriver()->getConnection();
        } catch (Exception $e) {
            $this->logger()->error($e->getMessage());
            $this->connection = null;
        }

        return (bool)$this->connection;
    }
}
