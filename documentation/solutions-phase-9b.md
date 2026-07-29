# Phase 9B — Solutions

## Objective and architecture
Phase 9B adds only the standalone, server-rendered Solutions module. It uses normalized Eloquent records, bounded structured children, focused manager/view-data/cache/dependency services, protected Blade administration, public Blade index/detail pages, and Pest coverage. It does not add a general page builder, uploads, roles, permissions, Products, Industries, or Media Library.

## Schema and categories
`solution_categories` stores ordered, uniquely slugged active/inactive categories. Categories are deactivated rather than deleted; the restrictive foreign key prevents deleting referenced categories. `solutions` stores category, safe structured copy, allow-listed presentation keys, lifecycle dates, CTA and SEO configuration, local asset references, ordering and audit users. Five ordered child tables store features (12), benefits (12), capabilities (12), process steps (8), and use cases (12). Two unique pivots store at most six related solutions and pages. Relation constraints reject self/duplicates and archived targets.

## Slug, templates, assets, and icons
Solution slugs are lower-case kebab-case, unique within `/solutions`, slash/traversal free, and reject reserved system/action segments. Page slug collisions are harmless because solution URLs are namespaced. Templates (`standard`, `detailed`, `enterprise`, `government`, `technology`) and hero styles are keys, never database view paths. Images reuse the approved local raster path policy; remote, absolute, traversal, SVG paths are rejected and alt text is required. Icons use a fixed registry; text is escaped and no HTML/SVG/embed input is accepted.

## Lifecycle, scheduling, preview, and dependencies
New and duplicated solutions are drafts. Publish validates required content, stamps `published_at`, and clears schedules. Scheduling interprets the Website Setting timezone and stores UTC. Unpublish preserves content; archive preserves content and is blocked by active navigation, homepage, or incoming related-solution references; restore always returns to draft. Preview is administrator-only, bypasses public cache, renders every lifecycle state with a banner, `noindex`, and `no-store`.

## Structured content and relations
Overview, challenge, approach and outcome are bounded plain text. Child items are bounded and ordered transactionally with move actions; process numbers derive from the current order. Public relations are flat and never recursively expanded. Related pages resolve their current slug relationally and public transforms filter draft, future, and archived targets.

## Homepage and navigation integration
Existing homepage `featured_solutions` preview rows remain intact. A nullable restrictive `solution_id` opt-in reference uses the canonical public solution title, summary, image and current route when visible; otherwise the existing preview record remains as safe fallback without a working canonical link. No automatic matching or data migration occurs. Navigation adds `solution_id`/`link_type=solution`; selectors exclude archived records, current slugs resolve at render time, and non-public targets are disabled. Reference-aware model invalidation refreshes homepage or public chrome caches only when needed.

## SEO, cache, sitemap, performance, and accessibility
Detail supports one metadata source for title/description/canonical/Open Graph/robots. Canonicals must match the configured host and OG images must be local. Versioned public-safe caches cover bounded index/category pages and per-solution transforms; cache exceptions fall back to bounded direct queries. Due schedules are checked before cache lookup, and short TTL/index versioning prevents stale visibility. Sitemap includes `/solutions` plus currently public, indexable details only. Queries eagerly load bounded children/relations. Semantic articles, breadcrumbs, ordered process steps, accessible labels/confirmations, visible focus conventions, responsive grids/tables, server rendering and reduced-motion-compatible existing styles support accessibility and performance.

## Local validation commands
Run: `composer install`, `npm install`, `php artisan optimize:clear`, `vendor/bin/pint --dirty --format agent`, `php artisan route:list --except-vendor`, `php artisan migrate`, `php artisan test --compact tests/Feature/Solutions`, `php artisan test --compact tests/Feature/Pages`, `php artisan test --compact tests/Feature/Homepage`, `php artisan test --compact tests/Feature/PublicChrome`, `php artisan test --compact tests/Feature/PublicSite`, `php artisan test --compact tests/Feature/Dashboard`, `php artisan test --compact tests/Feature/Auth`, `php artisan test --compact tests/Feature/Installer`, `php artisan test --compact tests/Feature/Licensing`, `php artisan test`, `npm run build`, `php artisan config:cache`, `php artisan route:cache`, `php artisan view:cache`, and finally `php artisan optimize:clear`.

## Manual acceptance checklist
1. Open the protected list/create/preview pages and verify inactive/uninstalled handling.
2. Create, rename, deactivate and reorder categories; confirm referenced categories cannot be deleted.
3. Create a draft; verify normalized/reserved/unsafe/duplicate slug behavior.
4. Populate overview, challenge, approach, outcome, every child type, CTAs, images, alt text and SEO.
5. Reorder children; confirm process numbering follows order and leaf removal confirms.
6. Add up to six related solutions/pages; reject self, duplicates, archived targets, and hide non-public targets.
7. Save draft and confirm public 404; preview every lifecycle state and verify banner/noindex/no-store.
8. Publish, schedule future/due, unpublish, archive, restore and duplicate; verify timestamps/content/copies.
9. Browse index/filter/pagination/detail/breadcrumb/metadata/empty state at mobile, desktop and 200% zoom using keyboard.
10. Reference from homepage and navigation; change slug/status; verify canonical content/current route/disabled behavior and dependency blocks.
11. Inspect caches, sitemap, query log, application/browser logs, portal HTTP fakes and rendered source for secrets/raw content.
12. Regress Pages, Homepage, Public Chrome, Public Site, Installer, Licensing, Auth/registration closure and Dashboard; run route/config/view caches and production build.

## Known limitations and Phase 9C handoff
No upload UI, hard-delete UI, automated scheduler command, drag/drop, generic polymorphic references, or SEO Manager is included. Visibility becomes due on request; short-lived/versioned caches and pre-cache visibility checks are used. Existing homepage previews require explicit canonical selection. Phase 9C may allow Products to relate to Solutions and may add canonical references to homepage featured-product previews. Phase 9B intentionally creates no Product tables, routes, models, or CRUD.
