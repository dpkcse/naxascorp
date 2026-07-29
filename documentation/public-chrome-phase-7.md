# Phase 7 — Public Chrome CMS

## Objective and architecture
Phase 7 replaces temporary public chrome data with relational branding, header, navigation, footer, and social configuration. `PublicChrome` is the request-memoized, cache-backed view-data boundary; Blade never queries Eloquent. It makes no external or licensing calls and safely falls back before migrations or during database/cache failure.

## Schema and behavior
Singleton `branding_settings`, `header_settings`, and `footer_settings` tables use a unique `singleton_key`. `navigation_menus` owns bounded `navigation_items`; `footer_settings` owns `footer_columns` and `social_links`; columns own `footer_links`. Restrictive foreign keys and application checks prevent destructive dependency cascades.

Branding accepts only relative `images/`, `assets/`, or public `storage/` image references. Traversal, absolute paths, URLs, executable/SVG types, and private paths are rejected. Logo alt text is mandatory. Colors are strict six-digit hex values; custom colors can reduce contrast and require manual review. Missing files fall back to text; Phase 7 has no uploader or Media Library.

Header configuration controls sticky behavior, site-name visibility, the top bar, CTA, support, careers, and plain-text message. Search and locale controls are forcibly disabled. WebsiteSetting supplies real phone, email, and country; empty bars hide. Links accept safe relative paths or credential-free HTTP/HTTPS only.

## Navigation
Locations are `primary`, `footer_company`, `footer_solutions`, `footer_resources`, and `legal`, with one menu per location. Item types are approved route (`home` only in Phase 7), safe URL, and disabled. Admin/auth/installer/licensing routes are never allowed. Primary depth is 3; other menus depth is 2. Limits are 12 top-level items, 20 children per parent, and 100 total items per menu. Parent menu consistency, self-parenting, descendant cycles, and depth are server validated. Numeric order and Move Up/Down controls persist transactionally. Parents and nonempty menus/columns cannot be deleted. Mega-menu readiness is structured through descriptions, featured flags, badges, bounded descendants, and `opens_mega_menu`; arbitrary HTML, Blade, JavaScript, CSS, and SVG are prohibited.

## Footer and social links
The footer supports plain text description, contact/social/legal visibility, columns, safe links, runtime-year copyright, and mandatory Naxas Innovations Limited attribution. Newsletter is forcibly disabled. Empty columns and inactive records do not render. Social platforms/icons use fixed allow-lists; URLs require safe HTTP/HTTPS and public new-tab links use `noopener noreferrer`.

## Cache, performance, fallbacks
Versioned keys are `public.chrome.v1.branding`, `.header`, `.navigation.primary`, and `.footer`, expiring after 30 minutes. Model create/update/delete/reorder invalidates public chrome. Cache exceptions degrade to direct bounded queries; database/schema exceptions degrade to Phase 6-compatible defaults. Active records are eagerly loaded, ordered, capped, and transformed to safe arrays. No request token, signed entitlement, administrator data, or license state is cached. Missing branding uses site-name/mark; missing menu uses Home plus an honest disabled placeholder; missing footer uses the Phase 6 identity/contact fallback.

## Accessibility and security
Submenus use buttons with `aria-expanded`/`aria-controls`, click-outside and Escape closure, keyboard-reachable links, visible focus, reduced-motion-compatible transitions, and nested mobile disclosures. Disabled items emit no `href`. The mobile drawer preserves its dialog/focus behavior. Output is escaped and external new-tab links receive safe rel attributes. Admin routes require authentication, active administrator, installed state, CSRF protection, throttling, and the existing noindex/no-store admin layout.

## Local validation commands
Run: `composer install`; `npm install`; `php artisan optimize:clear`; `vendor/bin/pint --dirty --format agent`; `php artisan route:list --except-vendor`; `php artisan migrate`; `php artisan test --compact tests/Feature/PublicChrome`; `php artisan test --compact tests/Feature/PublicSite`; `php artisan test --compact tests/Feature/Dashboard`; `php artisan test --compact tests/Feature/Auth`; `php artisan test --compact tests/Feature/Installer`; `php artisan test --compact tests/Feature/Licensing`; `php artisan test`; `npm run build`; `php artisan config:cache`; `php artisan route:cache`; `php artisan view:cache`; `php artisan optimize:clear`.

## Manual acceptance checklist
1. Open Branding; save fallback/logo branding; reject traversal, absolute, remote, and unsafe paths.
2. Configure top bar and CTA; create Primary; add route, URL, disabled, nested, and mega-ready items.
3. Verify cycle/depth/limits, order controls, cache invalidation, and dependency-safe deletes.
4. Configure footer, columns, links, and social links; confirm newsletter remains unavailable.
5. Verify desktop/mobile menus, Escape/click-outside, keyboard focus, safe targets, CTA, top-bar wrapping, columns, empty/inactive hiding, logo fallback, and no portal calls.
6. Verify database-failure fallback, bounded queries/no N+1, no secrets, 320px-to-wide layout, no overflow, reduced motion, 200% zoom, protected admin routes, closed registration, installer lock, dashboard, licensing diagnostics, caches, build, and logs.

## Known limitations and Phase 8 handoff
There is no media browser/upload, route parameters, arbitrary icons, newsletter submission, drag-and-drop, or content-model linking. File existence depends on deployment and missing files fall back. Only `home` is an approved route until real public modules exist. Phase 8 may add structured homepage ordering, hero slides/tabs, trust strip, about, featured solutions/products, capabilities, industries, process, clients, statistics, testimonials, insights, FAQ, and bottom CTA; none are implemented here.
