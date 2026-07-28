<?php

use App\Domain\Installation\EntitlementRevalidator;
use App\Domain\Installation\InstalledState;
use App\Models\LicenseState;
use App\Models\User;

use function Pest\Laravel\mock;

test('guests are redirected to the login page', function () {
    $response = $this->get('/dashboard');
    $response->assertRedirect('/login');
});

test('authenticated users can visit the dashboard', function () {
    mock(InstalledState::class)->shouldReceive('isInstalled')->andReturnTrue();
    mock(EntitlementRevalidator::class)->shouldReceive('validate')->andReturn(new LicenseState(['license_type' => 'single_site']));
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get('/dashboard');
    $response->assertSuccessful();
});