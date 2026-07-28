<?php

use App\Domain\Licensing\Exceptions\LicenseException;
use App\Domain\Licensing\InstallationIdentity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    @unlink(storage_path('app/system/installation-identity.json'));
    @unlink(storage_path('app/system/installation-identity.json.lock'));
});

afterEach(function () {
    @unlink(storage_path('app/system/installation-identity.json'));
    @unlink(storage_path('app/system/installation-identity.json.lock'));
});

it('creates one stable random uuid', function () {
    $identity = app(InstallationIdentity::class);
    $first = $identity->get();

    expect(Str::isUuid($first))->toBeTrue()->and($identity->get())->toBe($first);
});

it('fails safely for a corrupt identity', function () {
    if (! is_dir(storage_path('app/system'))) {
        mkdir(storage_path('app/system'), 0700, true);
    }
    file_put_contents(storage_path('app/system/installation-identity.json'), '{broken');

    expect(fn () => app(InstallationIdentity::class)->get())->toThrow(LicenseException::class, 'identity is corrupt');
});
