<?php

namespace App\Domain\Installation;

use App\Domain\Installation\DTOs\PermissionResult;

class PermissionChecker
{
    /** @return list<PermissionResult> */
    public function check(): array
    {
        $paths = [
            'storage' => storage_path(),
            'storage/app' => storage_path('app'),
            'storage/framework' => storage_path('framework'),
            'storage/framework/cache' => storage_path('framework/cache'),
            'storage/framework/sessions' => storage_path('framework/sessions'),
            'storage/framework/views' => storage_path('framework/views'),
            'storage/logs' => storage_path('logs'),
            'bootstrap/cache' => base_path('bootstrap/cache'),
        ];

        $results = [];

        foreach ($paths as $label => $path) {
            $results[] = new PermissionResult($path, $label, is_dir($path) && is_writable($path), 'Grant the web-server user write access to this path, then check again.');
        }

        $environmentPath = base_path('.env');
        $environmentWritable = file_exists($environmentPath)
            ? is_file($environmentPath) && is_writable($environmentPath)
            : is_readable(base_path('.env.example')) && is_writable(base_path());

        $results[] = new PermissionResult($environmentPath, '.env', $environmentWritable, 'Ensure .env is writable, or that the project directory permits creating it from .env.example.');

        return $results;
    }

    public function passes(): bool
    {
        foreach ($this->check() as $result) {
            if (! $result->writable) {
                return false;
            }
        }

        return true;
    }
}
