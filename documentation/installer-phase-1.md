# Naxora CMS Installer — Phase 1

## Objective

Phase 1 provides a safe, pre-database installation wizard foundation for Naxora CMS. It checks hosting readiness, filesystem access, and MySQL connectivity without changing application data, writing environment configuration, creating an administrator, or marking the product installed.

## Route flow

| Step | Route | Name | Prerequisite |
|---|---|---|---|
| Welcome | `GET /install` | `installer.welcome` | None |
| Requirements | `GET /install/requirements` | `installer.requirements` | Welcome completed |
| Permissions | `GET /install/permissions` | `installer.permissions` | Requirements passed |
| Database | `GET /install/database` | `installer.database` | Permissions passed |

The routes are separate from public, authentication, dashboard, and settings routes. Laravel middleware guards direct URL access, while every Livewire action repeats its prerequisite check to prevent direct action calls from skipping steps.

## Architecture

- Class-based Volt single-file components provide the four installer screens and server actions.
- `RequirementChecker` and `PermissionChecker` return typed results for rendering and testing.
- `InstallationState` owns temporary progress and invalidation.
- `InstallationManager` centralizes installer availability and route access decisions.
- `DatabaseConnectionTester` owns the isolated runtime-only MySQL probe.
- Installer response middleware adds indexing and cache protections.
- The dedicated installer layout uses the existing Tailwind, Vite, Livewire, and Flux asset pipeline.

## Temporary state mechanism

Progress uses Laravel's encrypted cookie-backed session and contains only these boolean keys:

- `welcome_completed`
- `requirements_passed`
- `permissions_passed`
- `database_connection_verified`

The default session driver is `cookie`, allowing the wizard to work before an application database exists. Laravel encrypts session cookies through its standard web middleware. Database credentials are never added to session progress.

Re-running a failed requirement or permission check invalidates that step and every later step. Editing database fields or receiving a failed connection result invalidates database verification. This state is temporary and is not an installed marker.

## Server requirements

Required checks are PHP 8.2+, OpenSSL, PDO, PDO MySQL, Mbstring, Tokenizer, XML, Ctype, JSON, Fileinfo, BCMath, cURL, ZIP, and either GD or Imagick. Intl and OPcache are recommended but do not block progression.

## Permission paths

The checker reports writability for:

- `storage`
- `storage/app`
- `storage/framework`
- `storage/framework/cache`
- `storage/framework/sessions`
- `storage/framework/views`
- `storage/logs`
- `bootstrap/cache`

It also checks whether an existing `.env` is writable or, when absent, whether `.env.example` is readable and the project directory allows later creation. The installer does not read `.env` contents, write `.env`, or change operating-system permissions.

## Database connection behavior

The database form validates all fields on the server. Blank MySQL passwords are accepted. A test creates a randomly named runtime connection with a five-second PDO timeout, executes only `SELECT 1`, then purges the connection and removes its runtime configuration in a `finally` block. The default application connection is never changed.

Raw driver exceptions are not returned or logged. Failed tests receive a generic message. The Livewire password property is cleared after validation, throttling, success, or failure. Connection tests are limited to five attempts per IP and session per minute.

No migrations, schema changes, seeds, queues, events, external requests, or credential persistence occur.

## Security controls

- Server-side step guards on routes and Livewire actions.
- Standard web CSRF protection.
- Encrypted session state containing booleans only.
- Rate-limited, bounded database probes.
- Generic connection failures with no SQLSTATE output.
- Immediate password-property clearing.
- No `.env` mutation and no operating-system permission mutation.
- `noindex, nofollow`, `X-Robots-Tag`, and `no-store` protections.
- Future-ready installed-state method that currently returns false and creates no marker.

## Local validation commands

```bash
composer install
npm install
php artisan optimize:clear
php artisan route:list --except-vendor
php artisan test --compact tests/Feature/Installer
php artisan test --compact tests/Unit/Installation
php artisan test
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize:clear
```

After validation, manually visit each installer route on mobile and desktop, confirm direct URL redirects, try blank and invalid database values, and confirm the application log contains no submitted password.

## Known limitations

- Phase 1 verifies connectivity but intentionally does not save database configuration.
- No final installed marker exists, so installer access remains available.
- Temporary progress is browser/session specific and can be reset by clearing the session cookie.
- Host-level permission behavior varies by operating system and web-server identity; the wizard reports PHP's effective writability only.
- Production installations should select the appropriate durable session driver after database setup if multi-node session sharing is required.

## Phase 2 handoff

Phase 2 must add administrator creation, enforce initial administrator uniqueness, disable public registration at the correct lifecycle point, integrate the administrator with existing authentication, and define safe installer resume behavior following administrator creation. It must also determine when and how the final installed marker is written. None of those behaviors are present in Phase 1.
