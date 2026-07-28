# Naxora CMS administration design — Phase 5

## Objective and principles

Phase 5 establishes a premium, restrained, corporate administration shell and honest dashboard foundation. It prioritizes clear hierarchy, accessible interaction, safe data, responsive behavior, bounded server work, and reusable Blade/Flux primitives. It does not introduce content CRUD, permissions, roles, fake metrics, or a public-site redesign.

## Audit and layout architecture

The starter dashboard was a placeholder Blade view at `/dashboard`, protected by authentication, verification, and active-administrator middleware. The authenticated layout used Flux sidebar/header variants, external starter-kit links, Instrument Sans from Bunny Fonts, forced dark mode, Heroicons, Flux forms/dropdowns/modals, and Volt class-based settings/auth components. Settings existed for profile, password, and appearance. Installer license activation and diagnostics routes existed, while website settings used the singleton `WebsiteSettings` accessor. Installed state was a service rather than route middleware. Alpine was used through Flux and small `x-data` interactions; no reusable table or dashboard-specific accessibility tests existed.

The new layout is a server-rendered Blade shell: a sticky desktop sidebar, Alpine-powered mobile drawer, sticky header, breadcrumb, main landmark, and footer. It uses no extra package and hydrates no dashboard Livewire component. Admin metadata is `noindex, nofollow`, and authenticated pages use meaningful Naxora titles.

## Navigation, sidebar, header, and breadcrumb

`AdminNavigation` is the single future-ready navigation configuration. Dashboard, settings/profile, license status, and diagnostics link only when named routes exist. Website, Content, Insights, Communication, Media, SEO, and unavailable System destinations render as non-interactive “Later” items. Active state is derived from the server request. Groups remain keyboard-operable.

The navy sidebar contains the Naxora CMS wordmark, Naxas Innovations Limited attribution, version, active state, and all future groups. The top header provides the mobile toggle, responsive semantic breadcrumb, View Website action, profile summary, profile link, and POST logout.

## Dashboard data sources and license behavior

The dashboard reads the singleton website settings, authenticated administrator, application/framework/PHP configuration, locally stored license state, and bounded local health checks. The existing `EntitlementRevalidator` is the license truth source and verifies the signed entitlement locally once per request. Failure produces “Verification required” without blocking rendering or making a portal call. Tokens, signed entitlements, claims, public keys, application keys, credentials, and paths are never rendered.

## System health

`SystemHealth` reports safe statuses for installed marker, database connectivity, writable storage, public storage link, acknowledged license state, mail transport, queue driver, and scheduler setup. Checks are bounded, catch failures into safe messages, execute no shell or Artisan commands, perform no writes, and make no external calls.

## Reusable components

Components include page header, breadcrumb, card, stat card, status badge, alert, empty state, skeleton loading state, primary/secondary/danger/icon buttons, field/input, select, textarea, validation summary, responsive table shell, Flux modal confirmation shell, section divider, and Flux dropdown shell. Flux supplies accessible field, input, select, textarea, dropdown, menu, and modal behavior rather than duplicating those primitives.

Form foundations cover required labels, help, validation errors, inline validation summaries, controls, and action buttons. Flux checkbox/switch/file inputs remain the preferred primitives for future business forms. Future forms should track a dirty boolean in Alpine/Livewire and invoke the confirmation shell before navigation. The table confines horizontal scrolling to its container, preserves semantic table markup, supports captions, and accepts empty/loading components through its slot.

## Accessibility and performance

The shell supplies a skip link, semantic landmarks, named navigation, accessible current/disabled states, visible focus rings, 44px controls, Escape/overlay drawer closing, body scroll locking, reduced-motion overrides, non-color status text, responsive truncation, and overflow containment. Flux supplies focus trapping and focus return for modal/dropdown foundations. Validate keyboard navigation, focus return, 200% zoom, and 320px layout manually.

No new JavaScript, icon, SPA, table, or CSS library was added. External font loading was removed in favor of the existing system fallback stack, which includes broad Unicode fallbacks suitable for Bangla. Dashboard queries are bounded singleton/existence queries, and there are no remote dashboard requests or fabricated counts.

## Dark-mode decision

The starter kit had Flux appearance controls and dark variants, but its authenticated HTML forcibly selected dark mode. Phase 5 uses one reliable premium light admin theme and does not create a competing theme mechanism. Account appearance routes remain intact for regression compatibility; full admin dark-mode parity is deferred.

## Local validation commands

```bash
composer install
npm install
php artisan optimize:clear
vendor/bin/pint --dirty --format agent
php artisan route:list --except-vendor
php artisan migrate
php artisan test --compact tests/Feature/Dashboard
php artisan test --compact tests/Feature/Auth
php artisan test --compact tests/Feature/Installer
php artisan test --compact tests/Feature/Licensing
php artisan test
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize:clear
```

## Manual acceptance checklist

- [ ] Log in with an active administrator and open `/dashboard`.
- [ ] Verify desktop sidebar, active state, disabled future items, header, profile, logout, website action, and license indicator.
- [ ] At 320px, tablet, desktop, and wide widths, verify the drawer and absence of page-level horizontal overflow.
- [ ] Verify overlay and Escape close the drawer and body scrolling is restored.
- [ ] Verify keyboard-only traversal, visible focus, dropdown/modal focus behavior, reduced motion, and 200% zoom.
- [ ] Verify website, system, administrator, readiness, recent activity, and system health content is accurate.
- [ ] Verify `/login`, settings, safe license pages, installer lock, and registration closure.
- [ ] Inspect HTML and recent logs for absence of license and database secrets.
- [ ] Verify production build and route/config/view caches.

## Known limitations and Phase 6 handoff

Recent activity is an explicit empty state because audit persistence does not exist. Scheduler readiness requires operator confirmation. Public storage, mail, and queue warnings are advisory. Content/communication counts are intentionally absent. Browser-level focus, zoom, and overflow acceptance require local validation.

Phase 6 may implement the public layout, top contact bar, header/footer shells, global public typography, containers, buttons, cards, CTA, responsive grid, public breadcrumb/forms, image component, and animation rules. It must not infer that Phase 5 delivered a homepage, homepage builder, or content modules.
