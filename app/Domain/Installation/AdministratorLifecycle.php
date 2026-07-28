<?php

namespace App\Domain\Installation;

use Illuminate\Database\DatabaseManager;
use Throwable;

class AdministratorLifecycle
{
    public const ProgressKey = 'administrator_created';

    public function __construct(private readonly DatabaseManager $database) {}

    public function hasAdministrator(): bool
    {
        try {
            $connection = $this->database->connection();

            return $connection->getSchemaBuilder()->hasTable('installation_progress')
                && $connection->table('installation_progress')->where('key', self::ProgressKey)->exists();
        } catch (Throwable) {
            return false;
        }
    }
}
