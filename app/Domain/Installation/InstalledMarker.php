<?php

namespace App\Domain\Installation;

use Illuminate\Support\Str;
use Throwable;

class InstalledMarker
{
    public const SchemaVersion = 1;

    public function path(): string
    {
        return storage_path('app/system/installed.json');
    }

    /** @param array<string, mixed> $payload */
    public function write(array $payload): bool
    {
        $directory = dirname($this->path());
        if ((! is_dir($directory) && ! mkdir($directory, 0700, true)) || ! is_writable($directory)) {
            return false;
        }
        $lock = fopen($this->path().'.lock', 'c');
        if ($lock === false || ! flock($lock, LOCK_EX)) {
            return false;
        }
        $temporary = $this->path().'.'.Str::random(12).'.tmp';
        try {
            $json = json_encode($payload, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            if (file_put_contents($temporary, $json, LOCK_EX) === false || ! chmod($temporary, 0600) || ! rename($temporary, $this->path())) {
                @unlink($temporary);
                return false;
            }
            return $this->read() !== null;
        } catch (Throwable) {
            @unlink($temporary);
            return false;
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    /** @return array{product:string,installation_uuid:string,installed_at:string,application_version:string,schema_version:int,normalized_domain:string}|null */
    public function read(): ?array
    {
        try {
            $contents = file_get_contents($this->path());
            $data = json_decode($contents === false ? '' : $contents, true, flags: JSON_THROW_ON_ERROR);
            $required = ['product', 'installation_uuid', 'installed_at', 'application_version', 'schema_version', 'normalized_domain'];
            if (! is_array($data) || array_diff($required, array_keys($data)) !== [] || array_diff(array_keys($data), $required) !== [] || $data['schema_version'] !== self::SchemaVersion) {
                return null;
            }
            foreach (['product', 'installation_uuid', 'installed_at', 'application_version', 'normalized_domain'] as $key) {
                if (! is_string($data[$key]) || $data[$key] === '') {
                    return null;
                }
            }
            return $data;
        } catch (Throwable) {
            return null;
        }
    }
}
