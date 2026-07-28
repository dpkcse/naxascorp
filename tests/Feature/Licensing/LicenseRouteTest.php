<?php

use App\Domain\Installation\AdministratorLifecycle;
use App\Domain\Installation\DatabaseConfigurationActivator;
use App\Domain\Licensing\InstallationIdentity;
use App\Models\User;

use function Pest\Laravel\mock;

beforeEach(function () {
    mock(DatabaseConfigurationActivator::class)->shouldReceive('activate')->zeroOrMoreTimes()->andReturnTrue();
    @unlink(storage_path('app/system/installation-identity.json'));
    @unlink(storage_path('app/system/installation-identity.json.lock'));
});

it('cannot access licensing before durable administrator creation', function () {
    $this->get('/install/license')->assertRedirect(route('installer.administrator'));
});

it('requires authentication after administrator creation', function () {
    mock(AdministratorLifecycle::class)->shouldReceive('hasAdministrator')->andReturnTrue();
    $this->get('/install/license')->assertRedirect(route('login'));
});

it('requires an active administrator', function () {
    mock(AdministratorLifecycle::class)->shouldReceive('hasAdministrator')->andReturnTrue();
    $user = User::factory()->create(['is_active' => false]);
    $this->actingAs($user)->get('/install/license')->assertRedirect(route('login'));
});

it('shows safe product version domain and environment details', function () {
    mock(AdministratorLifecycle::class)->shouldReceive('hasAdministrator')->andReturnTrue();
    $user = User::factory()->create(['is_active' => true]);

    $this->actingAs($user)->get('http://example.com/install/license')
        ->assertSuccessful()
        ->assertSeeText('Naxora CMS')
        ->assertSeeText(config('app.version'))
        ->assertSeeText('example.com')
        ->assertSeeText('testing')
        ->assertDontSee('signed_license')
        ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
});

it('diagnostics never exposes stored secrets', function () {
    mock(AdministratorLifecycle::class)->shouldReceive('hasAdministrator')->andReturnTrue();
    $user = User::factory()->create(['is_active' => true]);

    $this->actingAs($user)->get('http://example.com/install/license/diagnostics')
        ->assertSuccessful()->assertDontSee('request_token')->assertDontSee('signed_license');
});

it('does not create a final installed marker', function () {
    expect(app_path('../storage/app/installed'))->not->toBeFile();
});
