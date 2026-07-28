# Installer Phase 2 — Administrator Setup

## Objective

Phase 2 extends Naxora CMS from database verification through creation of one active initial administrator. It does not activate a license, install demo content, create roles or permissions, or mark the product installed.

## Flow and routes

The ordered flow is `GET /install` → `/install/requirements` → `/install/permissions` → `/install/database` → `/install/administrator`. Successful account creation leads to `/install/handoff`, which states that Phase 3 license activation is still required. Installer routes retain web/CSRF behavior, prerequisite enforcement, no-store headers, and noindex/nofollow protection.

## Database handoff decision

Phase 1 credentials could not support a later HTTP request because they existed only in the database form. Phase 2 therefore uses `DatabaseConfigurationStore`: after a successful isolated connection test, the six database values are JSON encoded, encrypted with Laravel's configured encrypter, written under `storage/framework/installer/database.enc` with mode `0600`, and atomically renamed into place. Credentials are never stored in the cookie session, URL, rendered Livewire state, logs, repository, or plaintext installer file.

`ActivateConfiguredDatabase` decrypts this server-only handoff at the start of web requests and applies only the normal MySQL connection's host, port, database, username, and password at runtime. It supports configuration/route caches because no `env()` calls occur outside configuration. The encrypted handoff remains necessary until a future deployment/recovery flow deliberately transfers configuration to the hosting platform. It depends on a stable `APP_KEY`; loss or rotation of that key requires re-entering database details.

## Migration behavior

Continuing after a verified connection invokes Laravel's console kernel migration API in-process with `migrate --force --no-interaction`; it never launches a shell. Only normal pending application migrations run. Before migration, the provisioner rejects a non-empty database without a migrations table, unknown tables, or existing users. It never runs `migrate:fresh`, `db:wipe`, seeding, table drops, or resets.

The normal migrations add `users.is_active`, `users.last_login_at`, and the minimal `installation_progress` table. Migration/provisioning exceptions are converted to generic recovery messages and do not advance installer progress. A retry runs only pending migrations.

## Administrator creation and uniqueness

`InitialAdministratorCreator` independently rechecks the database installer step, activates the secured connection, checks required tables and columns, and opens a transaction. The durable `administrator_created` primary-key record and the empty-users requirement protect initial-account uniqueness; a concurrent duplicate insert rolls back. The service trims/squishes the name, lowercases the email, hashes the password using the configured hasher, and creates an active `User`. It returns a typed result without the password.

The Volt form requires a name of at most 120 characters, a normalized valid unique email, and a confirmed password of at least 12 characters containing mixed case, a number, and a symbol. Attempts are rate-limited. Both password properties are cleared in a `finally` block after every outcome. Success marks only the temporary `administrator_created` step, authenticates the new administrator, regenerates the session, and redirects to the Phase 3 handoff. No installed marker is written.

The durable progress record reconciles lost cookie progress: it is the only database fact accepted as evidence that this installer created the administrator. An arbitrary user record is treated as a conflict, never as completed setup.

## Registration lifecycle

Public self-registration is closed throughout the installer lifecycle so it cannot bypass initial administrator creation. `GET /register` is redirected to login by middleware. Direct Livewire registration actions also redirect server-side to the installer before setup or login afterward, and clear password state. The starter-kit component and route remain for upgrade compatibility. Login, logout, forgot/reset password, email verification, password confirmation, dashboard, and settings routes remain.

## Active accounts and last login

All authenticated application routes use `EnsureAdministratorIsActive`. If an authenticated account becomes inactive, it is logged out, its session invalidated, its CSRF token regenerated, and it is redirected to login with the standard authentication failure message. Login includes `is_active = true` in the credential attempt, so inactive accounts cannot authenticate. After—and only after—a successful attempt, `last_login_at` is updated before session regeneration. No login IP is retained.

## Session decision

Phase 2 keeps the cookie session driver introduced in Phase 1. This avoids a mid-wizard session-driver switch and preserves pre-database progress. Only small boolean progress values and normal authentication session identifiers are stored; database credentials are not. Production must use HTTPS, a stable `APP_KEY`, HTTP-only cookies, and secure-cookie configuration. A future deliberate switch to database sessions must document reauthentication and migration of only non-sensitive state.

## Security and recovery

- CSRF and standard web middleware remain active.
- Installer responses are private/no-store and excluded from indexing.
- Database and administrator attempts are rate-limited.
- Passwords are validated server-side, hashed, never logged, never returned, and always cleared.
- Provisioning and creation errors expose no SQL or credential details.
- Unknown/existing schemas stop automatically and require a future explicit recovery flow.
- If encrypted database handoff storage is missing/unreadable, revisit the database step; do not edit its ciphertext.
- No role, permission, licensing request, demo data, final marker, or default credential exists.

## Local validation

```bash
composer install
npm install
php artisan optimize:clear
vendor/bin/pint --dirty --format agent
php artisan route:list --except-vendor
php artisan migrate
php artisan test --compact tests/Feature/Installer
php artisan test --compact tests/Feature/Auth
php artisan test --compact tests/Unit/Installation
php artisan test
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize:clear
```

For a safe migration check, back up the local validation database, run `php artisan migrate:status`, apply `php artisan migrate`, and inspect the two Phase 2 migrations. On a disposable validation database only, use `php artisan migrate:rollback --step=2`, verify that `installation_progress`, `users.is_active`, and `users.last_login_at` were removed while user records and starter-kit columns remain, then run `php artisan migrate` again. Never perform this rollback against a configured production installation.

## Known limitations and Phase 3 handoff

Phase 2 intentionally has no installer recovery UI for conflicting schemas or lost encrypted handoff keys. It does not finalize environment/platform secret management. It also does not finish installation: `/install/handoff` explicitly requires Phase 3 licensing. Phase 3 should consume the authenticated active administrator context and durable progress record without changing the uniqueness rule or inventing roles.
