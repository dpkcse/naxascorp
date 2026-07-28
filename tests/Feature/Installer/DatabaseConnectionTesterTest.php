<?php

use App\Domain\Installation\DatabaseConnectionTester;
use Illuminate\Database\Connection;
use Illuminate\Database\DatabaseManager;

use function Pest\Laravel\mock;

test('temporary connection is queried and purged without changing the default connection', function () {
    config(['database.default' => 'sqlite']);
    $connection = mock(Connection::class);
    $connection->shouldReceive('select')->once()->with('SELECT 1')->andReturn([['1' => 1]]);

    $manager = mock(DatabaseManager::class);
    $manager->shouldReceive('connection')->once()->withArgs(fn (string $name) => str_starts_with($name, 'installer_'))->andReturn($connection);
    $manager->shouldReceive('purge')->once()->withArgs(fn (string $name) => str_starts_with($name, 'installer_'));

    $result = (new DatabaseConnectionTester($manager))->test([
        'host' => '127.0.0.1', 'port' => 3306, 'database' => 'naxora',
        'username' => 'tester', 'password' => 'not-a-real-password',
    ]);

    expect($result->successful)->toBeTrue()
        ->and(config('database.default'))->toBe('sqlite')
        ->and(collect(config('database.connections'))->keys()->contains(fn ($name) => str_starts_with($name, 'installer_')))->toBeFalse();
});

test('temporary connection is purged after a safe failure', function () {
    $manager = mock(DatabaseManager::class);
    $manager->shouldReceive('connection')->once()->andThrow(new RuntimeException('SQLSTATE secret detail'));
    $manager->shouldReceive('purge')->once();

    $result = (new DatabaseConnectionTester($manager))->test([
        'host' => 'localhost', 'port' => 3306, 'database' => 'naxora',
        'username' => 'tester', 'password' => 'secret',
    ]);

    expect($result->successful)->toBeFalse()
        ->and($result->message)->not->toContain('SQLSTATE', 'secret');
});
