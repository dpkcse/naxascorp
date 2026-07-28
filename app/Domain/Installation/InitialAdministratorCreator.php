<?php

namespace App\Domain\Installation;

use App\Domain\Installation\DTOs\InitialAdministratorResult;
use App\Models\User;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Throwable;

class InitialAdministratorCreator
{
    public function __construct(private readonly InstallationState $state, private readonly DatabaseConfigurationActivator $activator, private readonly DatabaseManager $database) {}

    /** @param array{name: string, email: string, password: string} $attributes */
    public function create(array $attributes): InitialAdministratorResult
    {
        if (! $this->state->hasCompleted('database_connection_verified') || ! $this->activator->activate()) {
            return InitialAdministratorResult::conflict();
        }

        try {
            $schema = $this->database->connection()->getSchemaBuilder();
            if (! $schema->hasTable('users') || ! $schema->hasColumns('users', ['is_active', 'last_login_at']) || ! $schema->hasTable('installation_progress')) {
                return InitialAdministratorResult::conflict();
            }

            return $this->database->transaction(function () use ($attributes): InitialAdministratorResult {
                $progress = $this->database->table('installation_progress');

                if ($progress->where('key', AdministratorLifecycle::ProgressKey)->lockForUpdate()->exists() || User::query()->lockForUpdate()->exists()) {
                    return InitialAdministratorResult::conflict();
                }

                $progress->insert(['key' => AdministratorLifecycle::ProgressKey, 'completed_at' => now()]);

                $administrator = User::query()->create([
                    'name' => Str::squish($attributes['name']),
                    'email' => Str::lower(trim($attributes['email'])),
                    'password' => Hash::make($attributes['password']),
                    'is_active' => true,
                ]);

                return InitialAdministratorResult::success($administrator);
            });
        } catch (Throwable) {
            return InitialAdministratorResult::conflict();
        }
    }
}
