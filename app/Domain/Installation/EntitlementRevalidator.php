<?php

namespace App\Domain\Installation;

use App\Domain\Licensing\InstallationIdentity;
use App\Domain\Licensing\LicenseActivationService;
use App\Domain\Licensing\SignedEntitlementVerifier;
use App\Models\LicenseState;
use Illuminate\Database\DatabaseManager;

class EntitlementRevalidator
{
    public function __construct(private readonly SignedEntitlementVerifier $verifier, private readonly InstallationIdentity $identity, private readonly LicenseActivationService $activation, private readonly DatabaseManager $database) {}

    public function validate(string $host): LicenseState
    {
        $state = LicenseState::query()->where('product_slug', config('naxas-license.product'))->firstOrFail();
        $durablyAcknowledged = $this->database->table('installation_progress')->where('key', 'license_acknowledged')->exists();
        if ($state->acknowledged_at === null || ! $durablyAcknowledged || $state->encrypted_signed_entitlement === null || $state->entitlement_fingerprint === null) {
            throw new \RuntimeException('The acknowledged license could not be verified. Return to License Activation.');
        }

        $uuid = $this->identity->get();
        $domain = $this->activation->domain($host);
        if (! hash_equals($state->installation_uuid, $uuid) || ! hash_equals($state->normalized_domain, $domain)) {
            throw new \RuntimeException('The installation identity or domain does not match the license.');
        }

        $this->verifier->verify($state->encrypted_signed_entitlement, $state->entitlement_fingerprint, $uuid, $domain);

        return $state;
    }
}
