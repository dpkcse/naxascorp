# Installer Phase 3: Naxas License Portal activation

## Objective and contract

Phase 3 adds initial `naxora-cms` / `single_site` activation without finalizing the installation. The authority is the existing Naxas License Portal contract: `POST /api/v1/activation-requests`, `POST /api/v1/activation-requests/{request_id}/status`, and `POST /api/v1/activation-requests/{request_id}/acknowledge`. It uses manual approval, a proof-bound request token, and an RSA/SHA-256 entitlement. No direct license key, Gumroad, billing, update download, role, permission, demo content, or installed marker is introduced.

## Route flow

The flow is Welcome → Requirements → Permissions → Database → Administrator → License Activation (`/install/license`) → Installation Ready (`/install/ready`). License and diagnostics routes require the durable `administrator_created` record, authentication, an active administrator, the normal web/CSRF middleware, database activation, installer availability, and no-store/noindex response protection. Ready is a Phase 4 handoff, not completion.

## Identity, domain, and environment

`InstallationIdentity` atomically writes one random RFC 4122 UUID to `storage/app/system/installation-identity.json`, with a lock and mode `0600`. A corrupt file fails safely; a missing file is never silently replaced after a license request exists. Back up this file. It contains only the UUID.

Domain normalization follows the portal contract by retaining the lower-case host (including `www`), stripping scheme, port, path, query, fragment, and trailing dot, and rejecting credentials, unsupported schemes, malformed ASCII/Unicode, and invalid hosts. The request host is authoritative during installation. A domain change is rejected against saved request state.

Environment resolution maps `production`, `staging`, `testing`, and `local` exactly; every other application environment maps to `development`. This uses Laravel's resolved environment rather than reading `APP_ENV` directly. APP_URL remains platform configuration; the current request host prevents a stale APP_URL from licensing another domain.

## Transport security

`LicensePortalClient` uses Laravel HTTP with JSON accept/content headers, configured connect/total timeouts, no automatic redirects, strict JSON parsing, and a 128 KiB default response limit. It sends only to the configured origin and performs no automatic retries for request creation. HTTPS is mandatory. HTTP is possible only when the resolved environment is `local`, `NAXAS_LICENSE_ALLOW_LOCAL_HTTP=true`, and the portal host is in `NAXAS_LICENSE_TRUSTED_LOCAL_HOSTS`. TLS verification is never disabled.

## License state schema

`license_states` contains one unique row per product: product/type, UUID, normalized domain, environment, request ID, encrypted request token, expiry/status, encrypted signed entitlement, SHA-256 fingerprint, license status/timestamps, safe failure details, and portal link. Laravel encrypted casts depend on a stable APP_KEY. Neither status nor an editable boolean is authority: trust comes from re-verifying the signed entitlement and matching its claims.

Durable `installation_progress` keys are ordered as `license_request_created`, `license_entitlement_verified`, and `license_acknowledged`. Creating a new incomplete request clears later keys and prior entitlement state.

## Signed entitlement and fingerprint

The portal's `payload.signature` token is parsed as two unpadded Base64URL segments. The original encoded payload segment is the exact RSA/SHA-256 signed input; decoded JSON is not re-encoded. `key_id` selects an explicitly trusted public PEM path. The default path is accepted only for `key_id=default` when no rotation mapping is configured. Private keys are never used or stored.

The verifier requires product, license type, installation UUID, domain, active status, a non-future ISO-8601 `issued_at`, and an absent or future ISO-8601 `expires_at`. Optional support/update expiries must parse safely, and unknown listed critical claims are rejected. The portal fingerprint is exactly lowercase SHA-256 hex of the complete signed token and is compared with `hash_equals`.

## Persistence before acknowledgement and recovery

An approved response is size/format checked, signature and key verified, fingerprint matched, and claims validated. The encrypted token and verified state are committed in a database transaction before acknowledgement is sent. Persistence failure therefore prevents acknowledgement. A repeat delivery with the same fingerprint re-verifies and retries acknowledgement without duplicating the row; a different fingerprint for the request fails safely. `completed` acknowledgement is idempotently recorded and writes durable `license_acknowledged`.

## Diagnostics, audit, and failures

Authenticated diagnostics expose only safe configuration and timestamps—not request tokens, entitlements, payloads, keys, or database secrets. Structured application audit events use an allow-list of request ID, safe status/code, correlation ID, product, and environment. Portal unavailability is retryable. Rejected, expired, delivery-expired, suspended, revoked, and inactive outcomes retain safe recovery messages and never alter customer data or administrator access.

Public key or APP_KEY loss requires restoring the original key material from backup. The public key path must be readable by PHP and contain a valid RSA public PEM. Domain/UUID changes require restoration or portal-guided recovery; they are never silently accepted.

## Local validation commands

```bash
composer install
npm install
php artisan optimize:clear
vendor/bin/pint --dirty --format agent
php artisan route:list --except-vendor
php artisan migrate
php artisan test --compact tests/Feature/Installer
php artisan test --compact tests/Feature/Licensing
php artisan test --compact tests/Unit/Licensing
php artisan test
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize:clear
```

## Opt-in local end-to-end acceptance

Configure CMS URL `http://127.0.0.1:8000`, portal URL `http://127.0.0.1:8001`, local environment, local HTTP enabled, `127.0.0.1` trusted, and the portal public-key path. Never use these HTTP settings outside local development.

```bash
# Naxora CMS
php artisan serve --host=127.0.0.1 --port=8000
# Naxas License Portal (from its repository)
php artisan serve --host=127.0.0.1 --port=8001
```

Complete administrator setup, generate a request, copy its token into the portal, manually approve it, check status in Naxora, confirm signed entitlement retrieval and fingerprint acknowledgement, then confirm the Installation Ready page. Inspect storage/database/logs to ensure secrets are encrypted and never logged.

## Known limitations and Phase 4 handoff

Portal repository access must be available during local acceptance to reconfirm its deployed public key and exact signing implementation. This phase performs initial activation only; it does not schedule online revalidation, manage key distribution, finalize `.env`, create content, or write an installed marker. Phase 4 must atomically complete installation only after the durable acknowledged entitlement is revalidated.
