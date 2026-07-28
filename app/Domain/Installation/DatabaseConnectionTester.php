<?php

namespace App\Domain\Installation;

use App\Domain\Installation\DTOs\DatabaseConnectionResult;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Str;
use Throwable;

class DatabaseConnectionTester
{
    public function __construct(private readonly DatabaseManager $database) {}

    /** @param array{host: string, port: int, database: string, username: string, password: string} $credentials */
    public function test(array $credentials): DatabaseConnectionResult
    {
        $connectionName = 'installer_'.Str::lower(Str::random(20));

        config(["database.connections.{$connectionName}" => [
            'driver' => 'mysql',
            'host' => $credentials['host'],
            'port' => $credentials['port'],
            'database' => $credentials['database'],
            'username' => $credentials['username'],
            'password' => $credentials['password'],
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'options' => extension_loaded('pdo_mysql') ? [\PDO::ATTR_TIMEOUT => 5] : [],
        ]]);

        try {
            $this->database->connection($connectionName)->select('SELECT 1');

            return DatabaseConnectionResult::success();
        } catch (Throwable) {
            return DatabaseConnectionResult::failure();
        } finally {
            $this->database->purge($connectionName);
            config()->offsetUnset("database.connections.{$connectionName}");
        }
    }
}
