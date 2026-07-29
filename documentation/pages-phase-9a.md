# Phase 9A — Standalone Pages

## Objective and audit

Phase 9A adds standalone corporate pages without implementing later content modules or an unrestricted builder. Before implementation, the repository was audited: `/` used `PublicSiteController` and the cached structured homepage; explicit web, installer, authentication, settings, licensing, and website-management routes existed; no wildcard page route or sitemap route existed; `public/robots.txt` was static. The public layout already provided escaped title/description/canonical/Open Graph/robots metadata, dynamic cached chrome, skip navigation, header, footer, and Vite assets. Reusable public breadcrumb and dimension-aware image components existed. The admin shell already provided noindex/no-store markup, navigation groups, form/table/card/status/validation components, and keyboard-focus styles.

Public navigation accepted only an allow-listed `home` route, safe URL, or disabled item. Local assets were constrained to relative `storage/`, `images/`, or `assets/` image paths. Website settings supplied the site URL and timezone. Homepage content used fixed registries, relational structured records, explicit draft/publish transitions, eager loading, safe transformed arrays, and narrowly invalidated caches. Database conventions use explicit fillable fields, cast methods, restrictive foreign keys, and indexed order/status columns. Archive behavior was not previously available for content records; navigation deletion was dependency-blocked.

The principal risks identified were wildcard slug shadowing, hierarchy cycles/depth expansion, draft or archived leakage, executable markup, arbitrary database-selected views/classes, stale cache at scheduled publication, and navigation references breaking after slug/status changes. These are addressed through route order and regex, reserved/unique normalized slugs, bounded hierarchy validation, an explicit visibility scope, escaped plain text, code registries, short bounded navigation cache plus request-time visibility, model invalidation, and restrictive page references. Hard deletion has no admin route or UI.

## Architecture and schema

`pages` stores hierarchy, safe content, a template key, lifecycle timestamps, featured-image references, SEO values, ordering, and creator/updater audit references. `page_sections` stores at most 20 ordered, relational sections. `navigation_items.page_id` is a restrictive foreign key used only when `link_type=page`. Indexes cover slug uniqueness, status, parent/order, publication, and scheduling. No JSON component definitions, HTML, executable code, arbitrary Blade view, role, permission, or media tables are introduced.

Focused classes are `PageManager` (transactions, lifecycle, duplication and order), `PageHierarchy` (depth/cycles/breadcrumbs), `PageViewData` (eager loading and public-safe arrays), `PageCache`, `PageTemplateRegistry`, `PageSectionRegistry`, `PageSlug`, and `PageDependencyInspector`. Public and preview rendering share the same template resolution and Blade rendering path.

## Slug and public route policy

Slugs are lower-case ASCII kebab-case, unique, non-empty, and reject slashes, backslashes and traversal before normalization. Reserved segments include installer, authentication, administrator, website, API, asset/build, verification, health, sitemap, and password-reset routes. Explicit routes are registered before `GET /{slug}`; the wildcard accepts one safe segment only. Published pages require `published_at <= now`; scheduled pages become visible when `scheduled_for <= now` while retaining `scheduled` status (derived publication, no cron); drafts, future schedules, and archives return the same 404.

## Hierarchy and ordering

Pages have an optional parent and a maximum total depth of three. The service blocks self-parenting, archived parents, descendant-parenting, existing cycles, and moves that would push any descendant beyond the bound. Breadcrumb traversal is bounded and unpublished ancestors are labels rather than public links. Archive is blocked while active children exist. Changing parent normalizes both scopes; Move Up and Move Down swap peers transactionally and normalize contiguous order. There is no drag-and-drop dependency.

## Templates and content policy

The fixed templates are `standard`, `full_width`, `sidebar`, `landing`, and `contact_ready`. Registry records include label, description, trusted view mapping, section capabilities, breadcrumb support, and sidebar support. Unknown database keys safely fall back to standard, while administrator validation rejects unknown input. Content and section bodies are plain text rendered with escaped Blade output and preserved line breaks. Blade, PHP, script, style, iframe, SVG, embeds, arbitrary component names, CSS classes, and raw HTML are never interpreted.

Allowed section types are `rich_text`, `text_image`, `feature_grid`, `statistic_strip`, `quote`, `cta`, and `contact_prompt`; Phase 9A represents these with the shared safe heading/body/image/CTA vocabulary rather than module records or generic JSON. Backgrounds (`default`, `alternate`, `navy`, `gradient`) and widths (`narrow`, `standard`, `wide`) are fixed. CTA label/URL pairs, safe URL policy, local image policy, required alt, enabled state, bounded count, and transactional order are enforced.

## Lifecycle, preview and duplication

Save creates or edits content without publishing. Publish Now sets an immediate UTC timestamp. Schedule interprets administrator input in `WebsiteSetting.timezone` and stores UTC. Unpublish returns to draft and preserves content. Archive clears public timestamps and preserves all content, but dependency inspection blocks active children and navigation references. Restore always returns to draft and never republishes. Normal UI has no hard delete. Duplicate copies fillable safe page content and sections into a uniquely slugged draft, resets lifecycle timestamps/audit authors, and creates no navigation item.

Authenticated, active, installed administrators may preview every status at `/website/pages/{page}/preview`. Preview bypasses public page cache, includes a visible banner, forces `noindex, nofollow`, and returns `Cache-Control: no-store, private, max-age=0` and `X-Robots-Tag`. It performs no mutation and does not expose a public preview token.

## SEO, images, navigation and dependencies

SEO fields are bounded and page-local. Canonical input must be HTTP(S) on the configured website host; otherwise the current site URL and slug are used. Public robots settings apply only to visible pages; preview always overrides them. Open Graph images and featured images are approved local paths only. Featured and section images require alt text and render through the existing aspect-ratio image component to avoid layout shift; they load lazily below the header. There is no upload or Media Library.

Navigation now supports a narrow `page_id` relation. The selector lists non-archived pages, and public chrome resolves the current slug. Draft, future scheduled, and archived destinations become disabled rather than broken working links. Page mutations invalidate page and chrome caches. Archive is blocked until all navigation dependencies are explicitly removed or moved. The restrictive foreign key also blocks silent hard deletion.

## Cache, scheduling, query, sitemap and robots decisions

Public pages cache only transformed, public-safe arrays using per-page/version/slug keys. Page or section save/delete, lifecycle change, hierarchy change, template/SEO/slug change, and order changes invalidate page and navigation caches. Cache exceptions fall back to bounded direct queries. Preview never reads or writes public page cache. Route resolution is a single indexed slug query followed by eager-loaded bounded parent/section/child relations. No query occurs in deep Blade components and no license portal call occurs.

Scheduled page route resolution is evaluated on every request before reading a page payload, so a stale negative cache cannot hide a due page. Navigation is cached for at most one minute and re-evaluates publication timestamps when rebuilt; this is the documented maximum navigation-state delay without cron. Page availability itself has no delay.

A minimal dynamic `/sitemap.xml` was added because it could be implemented safely. It contains home plus visible, indexable pages using the configured site root and last-modified time. It excludes draft, future scheduled, archived, noindex, preview, admin, installer, auth, license and later-module URLs. The existing static `public/robots.txt` is unchanged.

## Accessibility, performance and security

Public output uses semantic `article`, header, heading, breadcrumb, related-page `aside`, escaped output, meaningful alt, visible focus and the existing responsive public shell. Admin lists scroll only at their table boundary; forms use labelled existing controls; lifecycle/order controls are native keyboard-operable and confirmations describe consequences. Existing reduced-motion, zoom, responsive and contrast foundations remain authoritative.

Rendering remains server-side Blade with no SPA, remote image/font, editor, or JavaScript package. Hierarchy, sections, sibling links, and navigation trees are bounded. Pages are paginated and sorting is allow-listed. Explicit fillable fields prevent mass assignment, CSRF remains enabled, mutations are throttled with the protected route group, and public arrays omit audit IDs and all licensing data.

## Local validation commands

Run in order where appropriate:

```bash
composer install
npm install
php artisan optimize:clear
vendor/bin/pint --dirty --format agent
php artisan route:list --except-vendor
php artisan migrate
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

## Manual acceptance checklist

- [ ] Open Pages; verify anonymous, inactive and uninstalled protection.
- [ ] Create a draft; verify normalization, unique/reserved/traversal rejection.
- [ ] Assign parents; attempt self, descendant, archived-parent and depth violations.
- [ ] Add, edit, reorder, disable and remove structured sections; verify the 20 limit.
- [ ] Confirm a draft is 404 publicly and preview has banner/noindex/no-store.
- [ ] Publish and verify template, escaped body, metadata, canonical, breadcrumb and featured image.
- [ ] Schedule in a non-UTC website timezone; verify 404 before and visibility after due time.
- [ ] Unpublish, archive and restore; verify content preservation and draft restoration.
- [ ] Duplicate; verify unique slug, copied sections, cleared dates and no navigation copy.
- [ ] Link a page in navigation; change its slug and verify the resolved link changes.
- [ ] Verify unpublished navigation is disabled and archive is blocked by dependencies.
- [ ] Verify unsafe CTA/canonical/asset paths and missing image alt are rejected.
- [ ] Verify sitemap includes only published/due indexable pages.
- [ ] Confirm no HTTP portal calls and no request token, entitlement or secret output.
- [ ] Check mobile through wide desktop, keyboard use, visible focus and 200% zoom.
- [ ] Run homepage/public chrome/public site/dashboard/auth/installer/licensing regressions.
- [ ] Run production build and route/config/view cache commands; inspect recent logs.

## Known limitations and Phase 9B handoff

Phase 9A intentionally has no hard-delete UI, media upload, rich-text editor, arbitrary HTML, nested public URL paths, redirect history, background status transition, or independent SEO Manager. Scheduled records retain `scheduled` status after becoming due. Navigation visibility can lag by at most one minute, while direct page visibility is immediate. `feature_grid` and `statistic_strip` use the bounded common section vocabulary and do not introduce repeatable module/item builders.

Phase 9B will implement standalone Solutions. It may migrate or link existing homepage featured-solution preview records to canonical Solution records. Phase 9A does not create Solution models, tables, routes, selectors, or polymorphic content references prematurely.
