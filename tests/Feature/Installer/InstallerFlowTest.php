<?php

use App\Domain\Installation\DTOs\PermissionResult;
use App\Domain\Installation\DTOs\RequirementResult;
use App\Domain\Installation\InstallationState;
use App\Domain\Installation\PermissionChecker;
use App\Domain\Installation\RequirementChecker;
use Livewire\Volt\Volt;

use function Pest\Laravel\mock;

test('installer welcome page loads with branding and response protection', function () {
    $this->get('/install')
        ->assertSuccessful()
        ->assertSee('Naxora CMS')
        ->assertSee('Premium Corporate Website CMS')
        ->assertSee('Naxas Innovations Limited')
        ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
        ->assertHeader('Cache-Control', 'no-store, private, max-age=0')
        ->assertSee('noindex, nofollow', false);
});

test('direct installer urls cannot skip prerequisite steps', function () {
    $this->get('/install/requirements')->assertRedirect(route('installer.welcome'));
    $this->get('/install/permissions')->assertRedirect(route('installer.requirements'));
    $this->get('/install/database')->assertRedirect(route('installer.permissions'));
    $this->get('/install/administrator')->assertRedirect(route('installer.database'));
});

test('welcome action marks only welcome complete', function () {
    Volt::test('installer.welcome')->call('start')->assertRedirect(route('installer.requirements'));

    $state = app(InstallationState::class);
    expect($state->hasCompleted('welcome_completed'))->toBeTrue()
        ->and($state->hasCompleted('requirements_passed'))->toBeFalse();
});

test('requirements page lists the required checks', function () {
    session()->put('installer.progress.welcome_completed', true);

    $this->get('/install/requirements')
        ->assertSuccessful()
        ->assertSeeTextInOrder(['PHP 8.2 or newer', 'OpenSSL', 'PDO MySQL', 'Mbstring', 'BCMath', 'cURL', 'ZIP', 'GD or Imagick']);
});

test('a required check failure blocks continuation and invalidates later progress', function () {
    session()->put('installer.progress', [
        'welcome_completed' => true,
        'requirements_passed' => true,
        'permissions_passed' => true,
    ]);

    $checker = mock(RequirementChecker::class);
    $checker->shouldReceive('passes')->once()->andReturnFalse();
    $checker->shouldReceive('check')->andReturn([
        new RequirementResult('php', 'PHP 8.2 or newer', false),
    ]);

    Volt::test('installer.requirements')->call('continue')->assertHasErrors('requirements');

    expect(app(InstallationState::class)->hasCompleted('requirements_passed'))->toBeFalse()
        ->and(app(InstallationState::class)->hasCompleted('permissions_passed'))->toBeFalse();
});

test('successful requirements allow the permissions step', function () {
    session()->put('installer.progress.welcome_completed', true);
    $checker = mock(RequirementChecker::class);
    $checker->shouldReceive('passes')->once()->andReturnTrue();
    $checker->shouldReceive('check')->andReturn([
        new RequirementResult('php', 'PHP 8.2 or newer', true),
    ]);

    Volt::test('installer.requirements')->call('continue')->assertRedirect(route('installer.permissions'));

    expect(app(InstallationState::class)->hasCompleted('requirements_passed'))->toBeTrue();
});

test('permission failure blocks continuation', function () {
    session()->put('installer.progress', ['welcome_completed' => true, 'requirements_passed' => true]);
    $checker = mock(PermissionChecker::class);
    $checker->shouldReceive('passes')->once()->andReturnFalse();
    $checker->shouldReceive('check')->andReturn([
        new PermissionResult(storage_path(), 'storage', false, 'Grant write access.'),
    ]);

    Volt::test('installer.permissions')->call('continue')->assertHasErrors('permissions');
    expect(app(InstallationState::class)->hasCompleted('permissions_passed'))->toBeFalse();
});

test('direct livewire actions cannot bypass prerequisites', function () {
    Volt::test('installer.requirements')->call('continue')->assertRedirect(route('installer.welcome'));
    Volt::test('installer.permissions')->call('continue')->assertRedirect(route('installer.requirements'));
    Volt::test('installer.database')->call('testConnection')->assertRedirect(route('installer.permissions'));
});

test('authentication routes remain available', function () {
    $this->get('/login')->assertSuccessful();
    $this->get('/register')->assertRedirect(route('login'));
    $this->get('/forgot-password')->assertSuccessful();
});
