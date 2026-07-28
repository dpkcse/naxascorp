<?php

use App\Domain\Installation\DatabaseConfigurationStore;

test('database handoff is encrypted outside the session and can be recovered', function () {
    $store = app(DatabaseConfigurationStore::class);
    $credentials = [
        'host' => 'database.internal',
        'port' => 3306,
        'database' => 'naxora',
        'username' => 'naxora_user',
        'password' => 'sensitive-database-password',
    ];

    $path = storage_path('framework/installer/database.enc');
    try {
        $store->put($credentials);

        expect($store->get())->toBe($credentials)
            ->and(file_get_contents($path))->not->toContain('sensitive-database-password')
            ->and(session()->all())->not->toContain('sensitive-database-password');
    } finally {
        if (is_file($path)) {
            unlink($path);
        }
    }
});
