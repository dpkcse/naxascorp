<?php

namespace App\Domain\Licensing;

use App\Domain\Installation\AdministratorLifecycle;
use App\Domain\Licensing\Exceptions\LicenseException;
use App\Models\LicenseState;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Str;
use Throwable;

class LicenseActivationService
{
    public function __construct(
        private readonly InstallationIdentity $identity,
        private readonly DomainNormalizer $domains,
        private readonly EnvironmentResolver $environment,
        private readonly LicensePortalClient $portal,
        private readonly SignedEntitlementVerifier $verifier,
        private readonly DatabaseManager $database,
        private readonly AdministratorLifecycle $administrators,
        private readonly LicenseAuditLogger $audit,
    ) {}

    public function domain(string $host): string
    {
        return $this->domains->normalize($host);
    }

    public function state(string $host): LicenseState
    {
        $identity = $this->identity->get();
        $domain = $this->domain($host);

        return LicenseState::query()->firstOrCreate(
            ['product_slug' => (string) config('naxas-license.product')],
            [
                'license_type' => (string) config('naxas-license.license_type'),
                'installation_uuid' => $identity,
                'normalized_domain' => $domain,
                'environment' => $this->environment->resolve(),
            ],
        );
    }

    public function createRequest(string $host): LicenseState
    {
        $this->guardPrerequisites();
        $state = $this->state($host);
        if ($state->acknowledged_at !== null) {
            throw new LicenseException('This installation is already activated.', 'already_activated');
        }
        if (! hash_equals($state->installation_uuid, $this->identity->get()) || ! hash_equals($state->normalized_domain, $this->domain($host))) {
            throw new LicenseException('The installation identity or domain changed. Restore the original values before continuing.', 'installation_mismatch');
        }
        $nonce = Str::random(64);
        $result = $this->portal->createActivationRequest([
            'product' => (string) config('naxas-license.product'),
            'version' => (string) config('app.version'),
            'license_type' => (string) config('naxas-license.license_type'),
            'installation_uuid' => $state->installation_uuid,
            'domain' => $state->normalized_domain,
            'environment' => $state->environment,
            'nonce' => $nonce,
        ]);
        $data = $result->data;
        if (! Str::isUuid((string) ($data['request_id'] ?? '')) || ! is_string($data['request_token'] ?? null) || ! str_starts_with($data['request_token'], 'BRQ-') || ($data['status'] ?? null) !== 'pending') {
            throw new LicenseException('The license portal returned an invalid activation request.', 'invalid_activation_response');
        }
        try {
            $expiresAt = new \DateTimeImmutable((string) ($data['expires_at'] ?? ''));
        } catch (Throwable) {
            throw new LicenseException('The license portal returned an invalid activation expiry.', 'invalid_activation_response');
        }

        $this->database->transaction(function () use ($state, $data, $expiresAt): void {
            $state->update([
                'request_id' => $data['request_id'],
                'encrypted_request_token' => $data['request_token'],
                'request_expires_at' => $expiresAt,
                'activation_status' => 'pending',
                'encrypted_signed_entitlement' => null,
                'entitlement_fingerprint' => null,
                'license_status' => null,
                'activated_at' => null,
                'last_verified_at' => null,
                'acknowledged_at' => null,
                'last_failure_code' => null,
                'last_safe_message' => null,
                'portal_url' => $this->safePortalUrl($data['portal_url'] ?? null),
            ]);
            $this->setProgress('license_request_created');
            $this->clearProgress(['license_entitlement_verified', 'license_acknowledged']);
        });
        $this->audit->record('license.activation_request_created', ['request_id' => $state->request_id, 'product' => $state->product_slug]);

        return $state->refresh();
    }

    public function checkStatus(string $host): LicenseState
    {
        $this->guardPrerequisites();
        $state = $this->state($host);
        if ($state->acknowledged_at !== null) {
            return $state;
        }
        if ($state->request_id === null || $state->encrypted_request_token === null) {
            throw new LicenseException('Generate an activation request first.', 'request_missing');
        }
        if (! hash_equals($state->installation_uuid, $this->identity->get()) || ! hash_equals($state->normalized_domain, $this->domain($host))) {
            throw new LicenseException('The installation identity or domain no longer matches this request.', 'installation_mismatch');
        }
        if ($state->request_expires_at?->isPast() && $state->encrypted_signed_entitlement === null) {
            return $this->fail($state, 'expired', 'request_expired', 'The activation request has expired.');
        }

        $result = $this->portal->status($state->request_id, $state->encrypted_request_token, $state->installation_uuid);
        $status = (string) ($result->data['status'] ?? '');
        $this->audit->record('license.activation_status_checked', ['request_id' => $state->request_id, 'status' => $status, 'correlation_id' => $result->correlationId]);
        if ($status === 'pending') {
            $state->update(['activation_status' => 'pending', 'last_safe_message' => 'Approval is still pending.', 'last_failure_code' => null]);
            return $state->refresh();
        }
        if (in_array($status, ['rejected', 'expired', 'delivery_expired', 'suspended', 'revoked', 'inactive'], true)) {
            return $this->fail($state, $status, (string) ($result->data['failure_code'] ?? $status), $this->safeMessage($result->data['safe_message'] ?? null, $status));
        }
        if ($status !== 'approved') {
            throw new LicenseException('The license portal returned an unsupported status.', 'unsupported_status');
        }

        return $this->acceptApproved($state, $result->data);
    }

    /** @param array<string, mixed> $data */
    private function acceptApproved(LicenseState $state, array $data): LicenseState
    {
        $token = $data['signed_license'] ?? null;
        $fingerprint = $data['entitlement_fingerprint'] ?? null;
        if (! is_string($token) || ! is_string($fingerprint)) {
            throw new LicenseException('The approved license response is incomplete.', 'invalid_approved_response');
        }
        if ($state->entitlement_fingerprint !== null && ! hash_equals($state->entitlement_fingerprint, $fingerprint)) {
            throw new LicenseException('The portal delivered a conflicting license for this request.', 'conflicting_fingerprint');
        }
        $verified = $this->verifier->verify($token, $fingerprint, $state->installation_uuid, $state->normalized_domain);
        $this->audit->record('license.entitlement_verified', ['request_id' => $state->request_id, 'status' => 'active']);

        if ($state->encrypted_signed_entitlement === null) {
            $this->database->transaction(function () use ($state, $verified): void {
                $state->update([
                    'encrypted_signed_entitlement' => $verified->token,
                    'entitlement_fingerprint' => $verified->fingerprint,
                    'activation_status' => 'verified',
                    'license_status' => 'active',
                    'activated_at' => now(),
                    'last_verified_at' => now(),
                    'last_failure_code' => null,
                    'last_safe_message' => 'The signed license was verified and stored securely.',
                ]);
                $this->setProgress('license_entitlement_verified');
            });
            $this->audit->record('license.entitlement_persisted', ['request_id' => $state->request_id]);
        }

        $acknowledgement = $this->portal->acknowledge($state->request_id, $state->encrypted_request_token, $state->installation_uuid, $verified->fingerprint);
        if (($acknowledgement->data['status'] ?? null) !== 'completed') {
            throw new LicenseException('The license was stored but acknowledgement is pending. Check status again.', 'acknowledgement_pending');
        }
        $this->database->transaction(function () use ($state): void {
            $state->update(['activation_status' => 'completed', 'acknowledged_at' => $state->acknowledged_at ?? now(), 'last_safe_message' => 'License activated. Installation is ready for Phase 4.']);
            $this->setProgress('license_acknowledged');
        });
        $this->audit->record('license.acknowledgement_completed', ['request_id' => $state->request_id, 'status' => 'completed']);

        return $state->refresh();
    }

    private function fail(LicenseState $state, string $status, string $code, string $message): LicenseState
    {
        $state->update(['activation_status' => $status, 'license_status' => in_array($status, ['suspended', 'revoked', 'inactive'], true) ? $status : null, 'last_failure_code' => Str::limit($code, 255, ''), 'last_safe_message' => Str::limit($message, 1000, '')]);
        $this->audit->record(in_array($status, ['expired', 'delivery_expired'], true) ? 'license.activation_expired' : 'license.activation_rejected', ['request_id' => $state->request_id, 'status' => $status, 'failure_code' => $code]);
        return $state->refresh();
    }

    private function guardPrerequisites(): void
    {
        if (! (bool) config('naxas-license.enabled')) {
            throw new LicenseException('License activation is not configured for this installation.', 'licensing_disabled');
        }
        if (! $this->administrators->hasAdministrator()) {
            throw new LicenseException('Create the administrator before activating a license.', 'administrator_required');
        }
    }

    private function setProgress(string $key): void
    {
        $this->database->table('installation_progress')->updateOrInsert(['key' => $key], ['completed_at' => now()]);
    }

    /** @param list<string> $keys */
    private function clearProgress(array $keys): void
    {
        $this->database->table('installation_progress')->whereIn('key', $keys)->delete();
    }

    private function safePortalUrl(mixed $url): ?string
    {
        if (! is_string($url) || strlen($url) > 2048 || ! str_starts_with($url, $this->portal->origin().'/')) {
            return null;
        }
        return $url;
    }

    private function safeMessage(mixed $message, string $status): string
    {
        return is_string($message) && $message !== '' ? Str::limit(strip_tags($message), 1000, '') : match ($status) {
            'expired' => 'The activation request has expired.',
            'delivery_expired' => 'The approved license delivery window expired. Create a new request.',
            default => 'The activation request cannot be completed. Create a new request or contact support.',
        };
    }
}
