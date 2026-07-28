<?php

use App\Models\LicenseState;
use Illuminate\Support\Facades\DB;

it('encrypts request tokens and signed entitlements at rest', function () {
    $state = LicenseState::query()->create([
        'product_slug' => 'naxora-cms', 'license_type' => 'single_site',
        'installation_uuid' => '550e8400-e29b-41d4-a716-446655440000', 'normalized_domain' => 'example.com',
        'environment' => 'testing', 'encrypted_request_token' => 'BRQ-super-secret',
        'encrypted_signed_entitlement' => 'payload.signature',
    ]);
    $raw = DB::table($state->getTable())->where('id', $state->id)->first();

    expect($raw->encrypted_request_token)->not->toContain('BRQ-super-secret')
        ->and($raw->encrypted_signed_entitlement)->not->toContain('payload.signature')
        ->and($state->fresh()->encrypted_request_token)->toBe('BRQ-super-secret')
        ->and($state->fresh()->encrypted_signed_entitlement)->toBe('payload.signature')
        ->and($state->toArray())->not->toHaveKey('encrypted_request_token')
        ->and($state->toArray())->not->toHaveKey('encrypted_signed_entitlement');
});
