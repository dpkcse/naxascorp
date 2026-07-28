<?php

namespace App\Domain\Licensing;

use App\Domain\Licensing\DTOs\PortalResult;
use App\Domain\Licensing\Exceptions\LicenseException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Str;
use JsonException;

class LicensePortalClient
{
    public function __construct(private readonly Factory $http, private readonly EnvironmentResolver $environment) {}

    /** @param array<string, mixed> $payload */
    public function createActivationRequest(array $payload): PortalResult
    {
        return $this->post('/api/v1/activation-requests', $payload);
    }

    public function status(string $requestId, string $requestToken, string $installationUuid): PortalResult
    {
        $this->guardRequestId($requestId);

        return $this->post("/api/v1/activation-requests/{$requestId}/status", [
            'request_token' => $requestToken,
            'installation_uuid' => $installationUuid,
        ]);
    }

    public function acknowledge(string $requestId, string $requestToken, string $installationUuid, string $fingerprint): PortalResult
    {
        $this->guardRequestId($requestId);

        return $this->post("/api/v1/activation-requests/{$requestId}/acknowledge", [
            'request_token' => $requestToken,
            'installation_uuid' => $installationUuid,
            'entitlement_fingerprint' => $fingerprint,
        ]);
    }

    public function origin(): string
    {
        $url = rtrim((string) config('naxas-license.server_url'), '/');
        $parts = parse_url($url);
        $scheme = Str::lower((string) ($parts['scheme'] ?? ''));
        $host = Str::lower((string) ($parts['host'] ?? ''));
        if ($url === '' || $parts === false || $host === '' || isset($parts['user']) || isset($parts['query']) || isset($parts['fragment']) || ! empty($parts['path'])) {
            throw new LicenseException('The license portal configuration is invalid.', 'portal_configuration');
        }
        if ($scheme !== 'https') {
            $trustedHosts = config('naxas-license.trusted_local_hosts', []);
            $localHttpAllowed = $scheme === 'http'
                && $this->environment->resolve() === 'local'
                && (bool) config('naxas-license.allow_local_http')
                && in_array($host, $trustedHosts, true);
            if (! $localHttpAllowed) {
                throw new LicenseException('A secure license portal connection is required.', 'portal_https_required');
            }
        }
        if (! in_array($scheme, ['http', 'https'], true)) {
            throw new LicenseException('The license portal configuration is invalid.', 'portal_configuration');
        }

        return $url;
    }

    /** @param array<string, mixed> $payload */
    private function post(string $path, array $payload): PortalResult
    {
        try {
            $response = $this->http->acceptJson()
                ->asJson()
                ->connectTimeout((int) config('naxas-license.connect_timeout_seconds'))
                ->timeout((int) config('naxas-license.timeout_seconds'))
                ->withOptions(['allow_redirects' => false])
                ->post($this->origin().$path, $payload);
        } catch (ConnectionException) {
            throw new LicenseException('The license portal could not be reached. Please try again.', 'portal_unreachable');
        }

        return $this->parse($response);
    }

    private function parse(Response $response): PortalResult
    {
        $body = $response->body();
        if (strlen($body) > (int) config('naxas-license.max_response_bytes')) {
            throw new LicenseException('The license portal returned an invalid response.', 'response_too_large');
        }
        if ($response->redirect()) {
            throw new LicenseException('The license portal returned an unexpected redirect.', 'unexpected_redirect');
        }
        if (! $response->successful()) {
            throw new LicenseException('The license portal could not complete the request. Please try again.', 'portal_request_failed');
        }
        try {
            $data = json_decode($body, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new LicenseException('The license portal returned an invalid response.', 'malformed_json');
        }
        if (! is_array($data)) {
            throw new LicenseException('The license portal returned an invalid response.', 'malformed_json');
        }

        return new PortalResult($data, $response->header('X-Correlation-ID'));
    }

    private function guardRequestId(string $requestId): void
    {
        if (! Str::isUuid($requestId)) {
            throw new LicenseException('The saved activation request is invalid.', 'invalid_request_id');
        }
    }
}
