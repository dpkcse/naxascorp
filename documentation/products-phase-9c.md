# Phase 9C — Products

## Objective and scope

Phase 9C adds a standalone, server-rendered Products content module to Naxora CMS. It deliberately models descriptive corporate product content, not commerce. It has no prices, offers, cart, checkout, billing, inventory, order, subscription, payment, licence-issuance, portal, or remote-data behavior.

## Architecture

The module follows Pages and Solutions: Eloquent models and restrictive pivots, form requests, thin controllers, transactional domain managers, bounded view-data transformers, versioned public cache keys, Blade public/admin views, and Pest feature coverage. `ProductManager` owns lifecycle and ordering, `ProductRelationManager` owns bounded pivots, `ProductDependencyInspector` owns archive safety, `ProductViewData` and `ProductIndexViewData` return public-safe arrays, `ProductCache` performs selective invalidation, and `ProductRegistry` holds all allow-lists and limits.

## Schema

Migration `2026_08_07_000000_create_products_tables.php` creates `product_categories`, `products`, `product_editions`, `product_feature_groups`, `product_features`, `product_benefits`, `product_specifications`, `product_gallery_items`, `product_use_cases`, `product_integrations`, `product_relations`, `product_solution_relations`, and `product_page_relations`. It adds nullable restrictive `product_id` foreign keys to `homepage_items` and `navigation_items`. Child rows cascade only with a product database deletion; the admin offers no published-product hard delete.

## Categories

Categories have normalized unique slugs, active state, descriptions, contiguous order, and move controls. The restrictive product foreign key prevents deleting a category that still has products. The supported operational path is rename, reorder, or deactivate.

## Editions

A product has at most eight editions. Slugs are unique within the product and edition types are restricted to standard, professional, enterprise, government, or custom. Editions have no price, checkout, billing, inventory, subscription, or licensing fields.

## Feature groups and features

A product has at most ten active or inactive feature groups and each group at most twenty features. Text is bounded and escaped. Icons come from a fixed allow-list; arbitrary SVG is not accepted. Ordering mutations are transactional and normalized.

## Benefits, specifications, gallery, use cases, and integrations

Benefits and use cases are limited to twelve each. Specifications are plain-text label/value records limited to forty; exact administrator-entered values are retained and no technical values are inferred. Gallery records are limited to twelve and require an approved local raster path and alt text. Integrations are limited to twenty and allow only safe external URLs plus optional safe local raster logos with paired alt text. No remote image download or upload UI is included.

## Related Products, Solutions, and Pages

Unique restrictive pivots hold at most six targets per relation type. Products cannot relate to themselves. Archived targets are rejected by the manager, and public view data includes only currently visible targets. URLs are always resolved from current relational slugs. Rendering is flat and bounded; no recursive relation traversal occurs.

## Lifecycle and scheduling

The statuses are draft, published, scheduled, and archived. Saving changes content without altering publication state. Publish Now validates required public content, sets `published_at`, clears schedules, and invalidates caches. Scheduling interprets administrator input in the configured `WebsiteSetting` timezone and stores UTC. Due scheduled records are visible without a status rewrite; future records are private. Unpublish preserves content and returns to draft. Archive clears public timestamps and is dependency-aware. Restore returns to draft and never auto-publishes. Duplicate copies editions, feature groups/features, benefits, specifications, gallery, use cases, and integrations into a uniquely slugged draft while clearing lifecycle and audit ownership; navigation, homepage, and relation pivots are not copied.

## Preview

The protected preview renders through the public product template for every lifecycle state. It bypasses public caches, displays a Preview Mode banner, sends `Cache-Control: no-store, private, max-age=0` and `X-Robots-Tag: noindex, nofollow`, and never creates a public visibility exception.

## Homepage integration

`featured_products` homepage items may select a canonical `product_id`. Existing homepage-only preview fields remain untouched and no title matching occurs. When the target is public, its current title, description, safe image, and relational route override preview display data. When private or archived, no working canonical link is emitted and fallback preview content remains. Product updates invalidate homepage cache only when referenced.

## Navigation integration

Navigation accepts the explicit `product` link type and nullable restrictive `product_id`. The editor lists non-archived products. Public chrome resolves the current product slug only for currently visible products; otherwise the item is disabled. No generated URL is persisted. Referenced product changes selectively invalidate public chrome, and active navigation references block archive.

## Dependency safety

Archive inspection counts active navigation items, active homepage items, and incoming relations from non-archived products. Structured child rows are reported and preserved but do not block archive. Active public references must be disabled or removed first. Restrictive foreign keys protect category, related Solution, related Page, homepage, and navigation records.

## Slugs, templates, assets, URLs, and SEO

Product slugs are lowercase kebab-case, unique in `/products`, stable unless explicitly edited, and reject slash, backslash, traversal, unsafe characters, and reserved system/action segments. Templates are restricted to standard, software, enterprise, government, and platform; hero styles to standard, split, dark, and gradient. Database input never selects a Blade view.

Featured, gallery, integration-logo, and OG paths use the established local-asset validator. Absolute, remote, traversal, backslash, SVG, and executable paths are rejected. CTA label/URL pairs are validated together. Documentation and integration URLs use the existing safe URL policy, rejecting unsafe schemes, protocol-relative values, credentials, and control characters. Canonicals must use the configured website host. Metadata is emitted once by the public layout; preview is always noindex.

## Public cache and query behavior

The index, filtered index/pages, and per-product transformed payload use versioned keys and public-safe arrays. Cache reads/writes degrade to bounded direct database loading on cache failure. Model changes, children, lifecycle, slug, order, SEO, gallery, and relations bump product/index versions. Referenced homepage and chrome caches are invalidated selectively. Visibility predicates are evaluated before cache lookup, preventing a future schedule from becoming stuck private and preventing stale public detail after unpublish/archive. Detail data eager-loads bounded children (8/10×20/12/40/12/12/20) and three relation sets of six; Blade performs no database query.

## Sitemap

`/sitemap.xml` includes `/products` and currently visible, indexable product URLs. Draft, future scheduled, archived, noindex, preview, and admin URLs are excluded.

## Accessibility and performance

Public detail is a semantic article with breadcrumb, one page heading, section headings, labelled cards and lists, descriptive image alt, keyboard-native links/buttons, visible focus inherited from public primitives, and no color-only lifecycle UI. Admin tables remain horizontally responsive and ordering controls have accessible labels. Layouts remain usable at mobile through wide desktop and 200% zoom; no animation dependency conflicts with reduced-motion preferences. Rendering remains Blade/server-side with bounded eager loads, local images, no external fonts, no SPA, no heavy JavaScript, no portal calls, and Vite production compatibility.

## Security and limitations

Output is escaped and no raw HTML, arbitrary PHP/Blade/CSS/JavaScript, iframe, embed, SVG, or JSON-LD input is accepted. Admin routes retain authenticated, active-administrator, installed-state, CSRF, no-store/noindex, and mutation-throttle conventions. Mass assignment is allow-listed. Registration, licensing, secrets, roles, permissions, and installer behavior are unchanged. There is no upload interface, media library, pricing, commerce, automatic scheduled-status promotion, generic builder, or Industry implementation.

## Local validation

Run locally with the supported dependencies and MySQL:

```bash
composer install
npm install
php artisan optimize:clear
vendor/bin/pint --dirty --format agent
php artisan route:list --except-vendor
php artisan migrate
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

1. Open and verify protection of the Products list and create categories.
2. Create a draft; verify slug normalization; add overview/value proposition.
3. Add and reorder editions, feature groups/features, benefits, specifications, gallery items, use cases, and integrations.
4. Add Product, Solution, and Page relations; verify self/duplicate/max rejection.
5. Save and confirm it is not public; preview and verify banner/noindex/no-store.
6. Publish; inspect index/detail, metadata, local images/alt, and absence of pricing/cart/checkout.
7. Schedule a future product and verify private-before/public-after due behavior.
8. Unpublish, archive, restore, and duplicate; verify content/lifecycle rules.
9. Add homepage and navigation references, change slug, verify resolved links and selective invalidation.
10. Attempt archive with each dependency, remove/disable it, and retry.
11. Verify sitemap, bounded queries, no portal calls, no request tokens/entitlements/secrets, responsive layouts, keyboard operation, and 200% zoom.
12. Regress Solutions, Pages, Homepage, Public Chrome/Site, Installer, Licensing, Login, Dashboard, registration closure, cache commands, production build, and logs.

## Phase 9D handoff

Phase 9D may add standalone Industries and later relate Industries to Solutions, Products, and Pages through explicit bounded pivots. Phase 9C intentionally creates no Industry model, table, route, controller, navigation entry, or CRUD behavior.
