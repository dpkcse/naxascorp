<?php

namespace App\Domain\Installation;

use App\Domain\Installation\DTOs\DatabaseProvisioningResult;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Schema\Builder;
use Throwable;

class DatabaseProvisioner
{
    private const ApplicationTables = ['cache', 'cache_locks', 'failed_jobs', 'installation_progress', 'job_batches', 'jobs', 'migrations', 'password_reset_tokens', 'sessions', 'users'];

    public function __construct(private readonly DatabaseManager $database, private readonly Kernel $artisan) {}

    public function prepare(): DatabaseProvisioningResult
    {
        try {
            $connection = $this->database->connection();
            $connection->select('SELECT 1');
            /** @var Builder $schema */
            $schema = $connection->getSchemaBuilder();
            $tables = $schema->getTableListing();

            if ($tables !== [] && ! in_array('migrations', $tables, true)) {
                return DatabaseProvisioningResult::failure('The selected database contains an unrecognized schema. Use an empty database or follow a future recovery process.');
            }

            if (array_diff($tables, self::ApplicationTables) !== []) {
                return DatabaseProvisioningResult::failure('The selected database contains an unrecognized schema. Use an empty database or follow a future recovery process.');
            }

            if (in_array('users', $tables, true) && $connection->table('users')->exists()) {
                return DatabaseProvisioningResult::failure('Existing account data was detected. Administrator setup will not overwrite it.');
            }

            if ($this->artisan->call('migrate', ['--force' => true, '--no-interaction' => true]) !== 0) {
                return DatabaseProvisioningResult::failure();
            }

            return DatabaseProvisioningResult::success();
        } catch (Throwable) {
            return DatabaseProvisioningResult::failure();
        }
    }
}
