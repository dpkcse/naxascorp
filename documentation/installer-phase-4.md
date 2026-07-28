# Installer Phase 4 — Safe completion

## Objective and prerequisites

Phase 4 completes Naxora CMS installation after the database, active initial administrator, stable installation UUID, encrypted signed entitlement, fingerprint acknowledgement, and durable `license_acknowledged` progress exist. The flow is Welcome → Requirements → Permissions → Database → Administrator → License Activation → Website Setup → Demo Content Choice → Installation Complete.

## Website setup schema and lifecycle

`website_settings` is a singleton relational record enforced by unique `singleton_key`. It stores `site_name`, `legal_name`, nullable `tagline`, normalized `primary_email`, text `primary_phone`, ISO-style two-letter `country_code`, PHP timezone identifier, supported `default_locale`, and normalized `site_url`. It contains no license or environment secrets. Submission is validated and transactional; `updateOrCreate` makes retries update the incomplete singleton. `WebsiteSettings` provides runtime access without editing `.env`, `APP_KEY`, or database credentials.

The URL accepts HTTP/HTTPS, requires HTTPS outside local/testing hosts, and its normalized host must equal the cryptographically verified entitlement domain. The initial supported locale is `en`; Bangladesh and `Asia/Dhaka` are suggestions rather than restrictions.

## Demo-content decision and importer

The user must explicitly choose deferred professional demo content or an empty website. Since Phase 4 has no public CMS schema, the importer records only a singleton choice and `deferred`/`empty` status. `DemoContentInstaller` is transactional and idempotent. It never overwrites website settings, creates administrators, changes license state or keys, runs seeders, downloads assets, or creates future module tables. Premium demo content remains deferred until those modules exist.

## Finalization trust and durable completion

`EntitlementRevalidator` decrypts the stored entitlement through the encrypted model cast and invokes the existing RSA/SHA-256 verifier again. That verifies trusted key ID, fingerprint, product `naxora-cms`, `single_site` license type, installation UUID, normalized domain, active status, issue time, expiry, and critical claims. It additionally requires both acknowledgement timestamp and durable `license_acknowledged` progress. Mutable activation status is not trusted.

`InstallationFinalizer` checks connectivity, required tables, an active administrator, singleton website settings, explicit demo choice, and the revalidated entitlement. It then commits `installation_completed` in a database transaction. Only after commit does it atomically write the marker. A marker-write failure compensates by removing completion progress, leaving the installer recoverable. Repeated valid finalization is idempotent.

## Installed marker and installed state

`storage/app/system/installed.json` contains only product, installation UUID, installation timestamp, application version, marker schema version, and normalized domain. A lock file, same-directory temporary file, atomic rename, mode `0600`, exact-key JSON validation, and safe corrupt-file handling protect it.

The installed decision requires durable completion, a valid marker matching the stable UUID, an active administrator, website settings, and a currently valid acknowledged entitlement. The marker alone is never licensing authority.

## Installer lock and completion handoff

After installation, installer routes redirect an authenticated active administrator to the dashboard and guests to login. Login, dashboard, and license diagnostics remain reachable; registration stays closed. The completion page is available only through a one-time session flag immediately after finalization, avoiding redirect loops. It links to Dashboard, the existing public placeholder, and License Status. Direct Livewire installer actions pass through the same installed-state route middleware and cannot reopen steps.

## Recovery matrix

| Condition | Safe behavior |
|---|---|
| Database completion, missing/corrupt marker | Not installed; retry finalization; durable data retained. |
| Marker without database completion | Not installed; marker does not grant trust. |
| Expired, mismatched, corrupt, or fingerprint-invalid entitlement | No completion/marker; return to license recovery. |
| Missing settings or demo choice | No completion; return to the corresponding step. |
| Demo importer failure | Transaction rolls back; choice can be retried without duplication. |
| Marker permission/write failure | Completion progress is compensated; correct storage permissions and retry. |
| Session loss | Durable prerequisites remain; authenticate again and resume. |
| Repeated submission | Singleton updates/import and finalizer are idempotent. |

## Session and cache decisions

The existing cookie session driver is retained for the initial release. No database credentials, signed entitlement, request token, claims, or key material enter session cookies. Finalization clears installer progress booleans but preserves authentication and a one-time completion flag. Production requires a stable `APP_KEY`, HTTPS, secure and HTTP-only cookies, and an appropriate same-site policy.

No shell or Artisan cache command runs from HTTP. Administrators may run the local cache commands below after deployment. Runtime settings are read through the service rather than unsafe environment rewriting.

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
php artisan test --compact tests/Unit/Installation
php artisan test --compact tests/Unit/Licensing
php artisan test
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize:clear
```

## Manual acceptance flow

1. Start with a new empty MySQL 8 database.
2. Open `/install`.
3. Complete requirements and permissions.
4. Verify and provision the database.
5. Create the initial administrator.
6. Generate a license activation request.
7. Approve it in the Naxas License Portal.
8. Retrieve and acknowledge the signed entitlement.
9. Complete Website Setup.
10. Choose demo or empty setup.
11. Finalize installation.
12. Confirm the Complete page.
13. Confirm `/install` redirects afterward.
14. Confirm `/login` works.
15. Confirm `/dashboard` works.
16. Confirm license status/diagnostics works.
17. Confirm `/register` remains blocked.
18. Inspect installed marker for absence of secrets.
19. Inspect logs for absence of passwords, request tokens, entitlements and database credentials.
20. Confirm the public homepage remains the existing placeholder until Phase 5.

## Known limitations and Phase 5 handoff

Demo content is deliberately deferred because pages, navigation, media, and other public content modules do not exist. Recovery UI is limited to safe installer/license redirects; a dedicated recovery mechanism is future work. File/database atomicity cannot be a single distributed transaction, so marker failure uses explicit compensation.

Phase 5 may implement the admin shell, dashboard layout, public layout, global typography, buttons, cards, forms, and responsive navigation foundation. Phase 4 intentionally does not implement CMS modules, a homepage builder, roles, permissions, Gumroad, or an admin redesign.
