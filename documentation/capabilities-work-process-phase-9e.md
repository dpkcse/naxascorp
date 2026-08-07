# Phase 9E — Capabilities and Work Process

## Objective and audit
Phase 9E adds canonical Capabilities and reusable Work Processes without introducing a builder, workflow engine, project management, or Phase 9F content. The audit found established Phase 9A–9D conventions: dedicated Eloquent models and domain services, four-state lifecycle, UTC persistence, local-asset and URL policies, restrictive pivots, versioned public caches, server-rendered Blade, protected previews, and public sitemap filtering. These conventions are retained.

## Architecture
Each module has models, validation, transactional managers, dependency inspection, bounded relation management, independent cache versions, eager-loaded public view-data services, admin controllers, public controllers, Blade views, and Pest feature coverage. Database input stores allow-listed template keys, never Blade paths.

## Capability schema and categories
`capability_categories` supplies normalized unique slugs, active state, and contiguous order; restrictive deletion protects referenced categories. `capabilities` stores plain-text content, lifecycle, presentation allow-list keys, CTA, SEO, local assets, audit users, feature flag, and order. Highlights (12), benefits (12), and exact administrator-entered facts (8) are bounded, ordered children. Facts are never generated or animated.

## Work Process schema, stages, and deliverables
`work_processes` mirrors the safe lifecycle/SEO presentation fields. A process has at most 12 ordered stages. Visible numbering derives from collection order. Duration remains descriptive and performs no schedule calculation. Each stage has at most eight escaped, plain-text deliverables; there are no files, executable content, tasks, automation, approvals, or BPM behavior.

## Relations
Restrictive normalized pivots connect both modules to up to eight Solutions, Products, and Industries, and six Pages. Duplicate and archived targets are rejected; public view data filters targets through current visibility and resolves current slugs relationally. Existing Solution capabilities and Product features remain untouched.

## Lifecycle and scheduling
New and duplicated records are drafts. Publish requires public content and sets `published_at`; schedule interprets Website Settings timezone then stores UTC; unpublish preserves content; archive removes public visibility after dependency inspection; restore returns to draft. Capability duplication copies highlights, benefits, and facts. Process duplication copies stages and deliverables. Neither copies relations, navigation, or homepage references.

## Preview and SEO
Authenticated, active, installed administrators can preview every lifecycle state. Preview bypasses public caches, displays a banner, and sends private/no-store and noindex/nofollow headers. Both modules support title, description, canonical, Open Graph, and robots controls. Canonical URLs must use the configured site host; images use the existing safe local asset policy and require alt text. Administrator JSON-LD is not accepted.

## Homepage migration strategy
Capability homepage items gain a nullable restrictive `capability_id`; canonical visible content and route may be used while legacy preview copy remains the fallback. No title matching or destructive migration occurs. Because Phase 8 process items are individual steps, they are **not** linked individually. The process section instead gains one optional `work_process_id`; its active canonical stages can be derived while all legacy steps remain fallback content. Phase 8 client, testimonial, and statistic preview records remain intact.

## Industry canonical Capability strategy
`industry_needs.capability_id` is optional and restrictive. Industry-owned title/description remains authoritative fallback, with no automatic matching or requirement that a need be canonical. Capability lifecycle changes selectively invalidate affected Industry caches; public links only exist for visible canonical records.

## Navigation integration and dependency safety
Navigation supports relational `capability` and `work_process` types with restrictive foreign keys. Admin selection excludes archived targets; private targets render disabled; current slugs generate URLs. Lifecycle changes selectively invalidate public chrome. Archive inspection blocks active navigation, homepage, Industry-need, and visible incoming relations. Structured children do not block archive.

## Cache, sitemap, and query behavior
Capability index/category/detail and Work Process index/detail use independent versioned keys. Cache failures fall back to direct bounded queries; due schedules are rechecked before detail cache lookup. Mutations change only relevant versions and selectively invalidate referenced homepage, public chrome, and Industry caches. Public queries eager load bounded children/relations. Sitemap adds module indexes plus currently visible, indexable details only.

## Accessibility, performance, and security
Public details use semantic articles, breadcrumbs, heading hierarchy, ordered stages, explicit stage numbers, and deliverable lists. Admin tables remain responsive and move controls are keyboard operable. Rendering is Blade/server-side, bounded, eager loaded, and contains no remote calls, fonts, images, or heavy JavaScript. Validation rejects unsafe slugs, URLs, assets, icons, HTML-like executable inputs, duplicate/unbounded relations, and lifecycle leakage. Registration, installation, licensing, secrets, roles, and permissions are unchanged.

## Local validation commands
Run: `composer install`; `npm install`; `php artisan optimize:clear`; `vendor/bin/pint --dirty --format agent`; `php artisan route:list --except-vendor`; `php artisan migrate`; each requested focused suite under `tests/Feature/{Capabilities,WorkProcesses,Industries,Products,Solutions,Pages,Homepage,PublicChrome,PublicSite,Dashboard,Auth,Installer,Licensing}`; `php artisan test`; `npm run build`; `php artisan config:cache`; `php artisan route:cache`; `php artisan view:cache`; and `php artisan optimize:clear`.

## Manual acceptance checklist
Validate the requested 69-step flow locally: route guards; categories; drafts; all bounded children and relations; preview/private access; publish/schedule/unpublish/archive/restore/duplicate; homepage, Industry need, and navigation references; dependency blocks; cache and sitemap; canonical process stages and legacy fallback; responsive layouts, keyboard operation and 200% zoom; all Phase 1–9D regressions; registration/installer locks; absence of portal calls and secrets; cache compilation, production build, and recent logs.

## Known limitations and local validation required
The remote workspace may not contain Composer dependencies or a database, so runtime, migration, route-cache, view-cache, Pest, Pint, and Vite verification must be repeated locally. Asset fields intentionally accept safe local references only because Media Library is outside this phase. Work Process stages are descriptive content, not executable workflows.

## Phase 9F handoff
Phase 9F will implement standalone Clients, Testimonials, and Statistics. Phase 9E deliberately creates none of those modules and does not alter their Phase 8 homepage preview records.
