# Phase 9D — Industries

## Objective and architecture
Phase 9D adds a standalone, server-rendered Industries module without an unrestricted builder. `IndustryManager` owns transactional lifecycle/order operations; view-data services return bounded public-safe arrays; relation and dependency services enforce limits and archive safety; `IndustryCache` versions index/detail entries and selectively invalidates homepage and public chrome caches.

## Schema and categories
The migration creates `industry_categories`, `industries`, four structured child tables (`industry_challenges`, `industry_outcomes`, `industry_needs`, `industry_use_cases`), and restrictive pivots to Solutions, Products, and Pages. It adds nullable restrictive canonical references to homepage and navigation items. Categories have normalized unique slugs, active state, and contiguous manual ordering. Referenced categories cannot be deleted because the foreign key is restrictive; deactivation is preferred.

## Structured content
Challenges, outcomes, needs/capabilities, and use cases are administrator-authored escaped text records, capped at 12 each, ordered contiguously, and restricted to the fixed icon registry. They must not be treated as statistics, guarantees, or customer examples. “Needs / Capabilities” is Industry-local structured content, not a standalone Capability module.

## Relations
An Industry may relate to at most eight non-archived Solutions, eight non-archived Products, and six non-archived Pages. Unique restrictive pivots resolve current slugs at render time. Public payloads include only currently visible targets. No commerce data or duplicated raw URLs are inferred.

## Lifecycle, scheduling, preview, and dependencies
Draft saves never publish. Publish validates title and description, stamps publication time, and clears scheduling. Schedule input is interpreted in `WebsiteSetting` timezone and stored in UTC. Due scheduled records are visible; future records are private. Unpublish preserves content. Archive clears public timestamps and is blocked by active navigation, homepage, or public relationship dependencies. Restore always returns to draft. Duplicate copies structured children into a new unique-slug draft, clears lifecycle/audit timestamps, and does not copy canonical homepage/navigation or related-record pivots. Protected preview reuses the public renderer, bypasses cache, displays a banner, and sends no-store/noindex headers.

## Homepage and navigation integration
Existing homepage Industry previews remain intact. An optional canonical `industry_id` supplies the current title, description, and route when visible; private canonical records do not yield a public link and fallback preview content remains. Navigation `link_type=industry` resolves the current slug relationally; a private Industry becomes disabled. Referenced lifecycle/slug changes selectively invalidate homepage/public chrome cache.

## SEO, cache, and sitemap
Industry metadata supports title/description, same-host canonical URL, OG fields with local raster assets, and robots index/follow. Preview is always noindex. Public index/category/detail payloads use versioned cache keys with exception-safe direct-query fallback. Scheduled visibility is bounded by short TTL plus lifecycle invalidation. `/sitemap.xml` includes `/industries` and visible indexable details only.

## Accessibility, performance, and security
Public detail uses a semantic article, breadcrumbs, ordered headings, descriptive image alt text, responsive grids, focus-visible links, and plain escaped output. Admin ordering uses keyboard-operable buttons and responsive tables. Queries eager-load bounded children/relations; no SPA, external fonts, remote calls, or new JavaScript library is introduced. Validation rejects unsafe slugs/URLs, remote/traversal/SVG assets, arbitrary icons/templates, duplicate pivots, and incomplete CTA pairs. Admin routes retain installed/authenticated/active-administrator middleware, CSRF, mutation throttling, no-store, and noindex conventions.

## Local validation
Run, in order where practical:

```text
composer install
npm install
php artisan optimize:clear
vendor/bin/pint --dirty --format agent
php artisan route:list --except-vendor
php artisan migrate
php artisan test --compact tests/Feature/Industries
php artisan test --compact tests/Feature/Products
php artisan test --compact tests/Feature/Solutions
php artisan test --compact tests/Feature/Pages
php artisan test --compact tests/Feature/Homepage
php artisan test --compact tests/Feature/PublicChrome
php artisan test --compact tests/Feature/PublicSite
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

## Manual acceptance
1. Open the protected Industries list; create, rename, deactivate, and reorder a category.
2. Create a draft and confirm slug normalization; populate overview, challenges, outcomes, needs, and use cases.
3. Reorder each child type; attach visible Solutions, Products, and Pages; save without publishing.
4. Confirm public 404, then preview banner/noindex/no-store, breadcrumbs, metadata, images/alt, responsive and keyboard behavior at 200% zoom.
5. Publish and inspect index/detail; schedule another record and verify privacy before due and visibility after due.
6. Unpublish, archive, restore, and duplicate; verify content preservation and lifecycle reset.
7. Add homepage and navigation canonical references; change slug and verify current URLs/cache invalidation.
8. Attempt archive with each active dependency and verify blocking; remove dependencies and retry.
9. Inspect sitemap, logs, cached output, absence of portal calls/secrets/fabricated claims, and production Vite output.
10. Regress Products, Solutions, Pages, Homepage, Public Chrome/Site, Dashboard, Auth, Installer, Licensing, installer lock, and registration closure.

## Limitations and Phase 9E handoff
There is no upload UI or Media Library; asset paths reference approved local files. No arbitrary HTML/JSON-LD is accepted. Phase 9E may introduce canonical standalone Capabilities and Work Process and may later relate Industries to canonical Capabilities. Phase 9D intentionally creates no `capabilities` or `work_processes` standalone tables, models, or routes; current Industry needs/capabilities remain Industry-owned structured records.
