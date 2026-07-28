<?php

namespace App\Domain\Licensing;

use App\Domain\Licensing\Exceptions\LicenseException;
use App\Models\LicenseState;
use Illuminate\Support\Str;
use Throwable;

class InstallationIdentity
{
    public function get(): string
    {
        $path = $this->path();
        if (is_file($path)) {
            return $this->read($path);
        }

        try {
            if (LicenseState::query()->whereNotNull('request_id')->exists()) {
                throw new LicenseException('The installation identity is missing. Restore it before continuing.', 'identity_missing');
            }
        } catch (LicenseException $exception) {
            throw $exception;
        } catch (Throwable) {
            // The identity can be created before the licensing table exists.
        }

        $directory = dirname($path);
        if (! is_dir($directory) && ! mkdir($directory, 0700, true) && ! is_dir($directory)) {
            throw new LicenseException('Secure installation identity storage is unavailable.', 'identity_storage');
        }

        $lock = fopen($path.'.lock', 'c');
        if ($lock === false || ! flock($lock, LOCK_EX)) {
            throw new LicenseException('The installation identity is temporarily unavailable.', 'identity_lock');
        }

        try {
            if (is_file($path)) {
                return $this->read($path);
            }
            $uuid = (string) Str::uuid();
            $temporaryPath = $path.'.'.Str::random(12).'.tmp';
            $json = json_encode(['installation_uuid' => $uuid], JSON_THROW_ON_ERROR);
            if (file_put_contents($temporaryPath, $json, LOCK_EX) === false || ! chmod($temporaryPath, 0600) || ! rename($temporaryPath, $path)) {
                @unlink($temporaryPath);
                throw new LicenseException('Secure installation identity storage is unavailable.', 'identity_storage');
            }

            return $uuid;
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    public function path(): string
    {
        return storage_path('app/system/installation-identity.json');
    }

    private function read(string $path): string
    {
        try {
            $data = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            throw new LicenseException('The installation identity is corrupt. Restore the original identity before continuing.', 'identity_corrupt');
        }
        $uuid = $data['installation_uuid'] ?? null;
        if (! is_string($uuid) || ! Str::isUuid($uuid)) {
            throw new LicenseException('The installation identity is corrupt. Restore the original identity before continuing.', 'identity_corrupt');
        }

        return $uuid;
    }
}
