<?php

use App\Domain\Installation\DatabaseConnectionTester;
use App\Domain\Installation\DatabaseConfigurationActivator;
use App\Domain\Installation\DatabaseConfigurationStore;
use App\Domain\Installation\DatabaseProvisioner;
use App\Domain\Installation\DTOs\DatabaseConnectionResult;
use App\Domain\Installation\DTOs\DatabaseProvisioningResult;
use App\Domain\Installation\InstallationState;
use Illuminate\Support\Facades\Log;
use Livewire\Volt\Volt;

use function Pest\Laravel\mock;

beforeEach(function () {
    session()->put('installer.progress', [
        'welcome_completed' => true,
        'requirements_passed' => true,
        'permissions_passed' => true,
    ]);
});

test('database fields are validated on the server', function () {
    Volt::test('installer.database')
        ->set('host', '')
        ->set('port', 70000)
        ->set('database', 'unsafe database name')
        ->set('username', '')
        ->call('testConnection')
        ->assertHasErrors(['host', 'port', 'database', 'username']);
});

test('successful connection marks database verification and clears the password', function () {
    $tester = mock(DatabaseConnectionTester::class);
    $tester->shouldReceive('test')->once()->andReturn(DatabaseConnectionResult::success());
    mock(DatabaseConfigurationStore::class)->shouldReceive('put')->once();
    mock(DatabaseConfigurationActivator::class)->shouldReceive('activate')->once()->andReturnTrue();
    mock(DatabaseProvisioner::class)->shouldReceive('prepare')->once()->andReturn(DatabaseProvisioningResult::success());

    Volt::test('installer.database')
        ->set('host', '127.0.0.1')->set('port', 3306)->set('database', 'naxora')
        ->set('username', 'naxora_user')->set('password', 'highly-sensitive-value')
        ->call('testConnection')
        ->assertSet('password', '')
        ->assertRedirect(route('installer.administrator'))
        ->assertDontSee('highly-sensitive-value');

    expect(app(InstallationState::class)->hasCompleted('database_connection_verified'))->toBeTrue()
        ->and(session()->all())->not->toContain('highly-sensitive-value');
});

test('connection failure is safe and invalidates database verification', function () {
    session()->put('installer.progress.database_connection_verified', true);
    Log::spy();
    $tester = mock(DatabaseConnectionTester::class);
    $tester->shouldReceive('test')->once()->andReturn(DatabaseConnectionResult::failure());

    Volt::test('installer.database')
        ->set('host', '127.0.0.1')->set('database', 'naxora')->set('username', 'invalid')
        ->set('password', 'secret-not-for-output')->call('testConnection')
        ->assertSet('password', '')
        ->assertSee('We could not connect to the database')
        ->assertDontSee('secret-not-for-output')
        ->assertDontSee('SQLSTATE');

    Log::shouldNotHaveReceived('error');
    expect(app(InstallationState::class)->hasCompleted('database_connection_verified'))->toBeFalse();
});

test('failed migration does not mark database or administrator complete', function () {
    $tester = mock(DatabaseConnectionTester::class);
    $tester->shouldReceive('test')->once()->andReturn(DatabaseConnectionResult::success());
    mock(DatabaseConfigurationStore::class)->shouldReceive('put')->once();
    mock(DatabaseConfigurationActivator::class)->shouldReceive('activate')->once()->andReturnTrue();
    mock(DatabaseProvisioner::class)->shouldReceive('prepare')->once()->andReturn(DatabaseProvisioningResult::failure());

    Volt::test('installer.database')->set('database', 'naxora')->set('username', 'user')->call('testConnection')->assertSee('could not be prepared safely');

    expect(app(InstallationState::class)->hasCompleted('database_connection_verified'))->toBeFalse()
        ->and(app(InstallationState::class)->hasCompleted('administrator_created'))->toBeFalse();
});

test('changing database fields invalidates prior verification', function () {
    session()->put('installer.progress.database_connection_verified', true);

    Volt::test('installer.database')->set('host', 'database.internal');

    expect(app(InstallationState::class)->hasCompleted('database_connection_verified'))->toBeFalse();
});
