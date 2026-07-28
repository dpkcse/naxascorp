<?php

namespace App\Domain\Installation;

use Illuminate\Database\DatabaseManager;

class DatabaseConfigurationActivator
{
    public function __construct(
        private readonly DatabaseConfigurationStore $store,
        private readonly DatabaseManager $database,
    ) {}

    public function activate(): bool
    {
        $credentials = $this->store->get();
        if ($credentials === null) {
            return false;
        }

        config([
            'database.default' => 'mysql',
            'database.connections.mysql.host' => $credentials['host'],
            'database.connections.mysql.port' => $credentials['port'],
            'database.connections.mysql.database' => $credentials['database'],
            'database.connections.mysql.username' => $credentials['username'],
            'database.connections.mysql.password' => $credentials['password'],
        ]);
        $this->database->purge('mysql');

        return true;
    }
}
