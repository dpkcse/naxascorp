<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LicenseState extends Model
{
    protected $fillable = [
        'product_slug', 'license_type', 'installation_uuid', 'normalized_domain', 'environment',
        'request_id', 'encrypted_request_token', 'request_expires_at', 'activation_status',
        'encrypted_signed_entitlement', 'entitlement_fingerprint', 'license_status', 'activated_at',
        'last_verified_at', 'acknowledged_at', 'last_failure_code', 'last_safe_message', 'portal_url',
    ];

    protected $hidden = ['encrypted_request_token', 'encrypted_signed_entitlement'];

    protected $attributes = ['activation_status' => 'not_requested'];

    protected function casts(): array
    {
        return [
            'encrypted_request_token' => 'encrypted',
            'encrypted_signed_entitlement' => 'encrypted',
            'request_expires_at' => 'datetime',
            'activated_at' => 'datetime',
            'last_verified_at' => 'datetime',
            'acknowledged_at' => 'datetime',
        ];
    }
}
