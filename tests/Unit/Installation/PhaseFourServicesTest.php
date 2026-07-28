<?php

use App\Domain\Installation\DemoContentInstaller;
use App\Domain\Installation\InstalledMarker;
use App\Models\InstallationChoice;
use App\Models\LicenseState;
use App\Models\User;
use App\Models\WebsiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('installs only an idempotent deferred demo choice without changing settings users or license', function () {
    $settings = WebsiteSetting::query()->create(['site_name'=>'Customer Site','legal_name'=>'Customer Legal','primary_email'=>'a@example.com','country_code'=>'BD','timezone'=>'UTC','default_locale'=>'en','site_url'=>'https://example.com']);
    $user = User::factory()->create();
    $license = LicenseState::query()->create(['product_slug'=>'naxora-cms','license_type'=>'single_site','installation_uuid'=>'13a1a584-e065-4777-a52e-076a6e4a97ab','normalized_domain'=>'example.com','environment'=>'testing']);
    app(DemoContentInstaller::class)->install(true);
    app(DemoContentInstaller::class)->install(true);
    expect(InstallationChoice::query()->count())->toBe(1)
        ->and($settings->fresh()->site_name)->toBe('Customer Site')
        ->and(User::query()->count())->toBe(1)
        ->and($license->fresh()->getAttributes())->toBe($license->getAttributes());
});

it('writes a restrictive valid marker containing no secrets and rejects corruption', function () {
    $marker = app(InstalledMarker::class);
    @unlink($marker->path());
    $payload = ['product'=>'naxora-cms','installation_uuid'=>'13a1a584-e065-4777-a52e-076a6e4a97ab','installed_at'=>'2026-07-28T00:00:00+00:00','application_version'=>'1.0.0','schema_version'=>1,'normalized_domain'=>'example.com'];
    expect($marker->write($payload))->toBeTrue()->and($marker->read())->toBe($payload)
        ->and(file_get_contents($marker->path()))->not->toContain('token', 'entitlement', 'password', 'database');
    file_put_contents($marker->path(), '{corrupt');
    expect($marker->read())->toBeNull();
});
