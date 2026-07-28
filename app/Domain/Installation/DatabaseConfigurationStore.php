<?php

namespace App\Domain\Installation;

use Illuminate\Contracts\Encryption\Encrypter;
use RuntimeException;

class DatabaseConfigurationStore
{
    public function __construct(private readonly Encrypter $encrypter) {}

    /** @param array{host: string, port: int, database: string, username: string, password: string} $credentials */
    public function put(array $credentials): void
    {
        $directory = dirname($this->path());

        if (! is_dir($directory) && ! mkdir($directory, 0700, true) && ! is_dir($directory)) {
            throw new RuntimeException('The secure installer storage directory could not be created.');
        }

        $temporaryPath = tempnam($directory, 'database-');
        if ($temporaryPath === false) {
            throw new RuntimeException('The temporary database configuration could not be created.');
        }

        try {
            $payload = $this->encrypter->encryptString(json_encode($credentials, JSON_THROW_ON_ERROR));
            if (file_put_contents($temporaryPath, $payload, LOCK_EX) === false || ! chmod($temporaryPath, 0600)) {
                throw new RuntimeException('The database configuration could not be secured.');
            }

            if (! rename($temporaryPath, $this->path())) {
                throw new RuntimeException('The database configuration could not be activated.');
            }
        } finally {
            if (is_file($temporaryPath)) {
                @unlink($temporaryPath);
            }
        }
    }

    /** @return array{host: string, port: int, database: string, username: string, password: string}|null */
    public function get(): ?array
    {
        if (! is_file($this->path())) {
            return null;
        }

        $payload = file_get_contents($this->path());
        if ($payload === false) {
            return null;
        }

        $credentials = json_decode($this->encrypter->decryptString($payload), true, flags: JSON_THROW_ON_ERROR);

        return is_array($credentials) ? $credentials : null;
    }

    public function exists(): bool
    {
        return is_file($this->path());
    }

    private function path(): string
    {
        return storage_path('framework/installer/database.enc');
    }
}
