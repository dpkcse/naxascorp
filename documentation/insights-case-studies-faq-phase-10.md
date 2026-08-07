# Phase 10 — Insights, Case Studies, and FAQs

## Objective and audit
Phase 10 introduces canonical Articles and categories, truthful Case Studies, and grouped reusable FAQs without a page builder or Media Library. The pre-change audit found Homepage `insight` and `faq` items are generic `homepage_items` fallback records; they have titles, descriptions, images, dates and URLs but previously no canonical relation. Pages, Solutions, Products, Industries, Capabilities, Work Processes, Clients, Testimonials, and Statistics share draft/published/scheduled/archived lifecycles, UTC storage after parsing in `WebsiteSetting.timezone`, public visibility scopes, protected no-store previews, restrictive pivots, dependency inspectors, versioned caches, and transformed public arrays.

Navigation supports bounded route/module types and safe URLs; Phase 10 uses the `insights.index` route rather than direct Article menu relations. The sitemap is server-rendered XML. Metadata is supplied through the public layout. `PublicAssetPath` rejects remote, absolute, traversal, SVG and executable paths, while `PublicLink` validates URLs. Existing public cards, breadcrumbs, grids and previous/next pagination are reused. Admin pages use reusable page headers, tables, fields, status badges and form controls. Factories and Pest feature tests use `RefreshDatabase` and installed markers.

Key risks identified were executable rich text/XSS, unsafe assets and URLs, duplicate or traversal slugs, future schedules leaking, fake authors, fabricated clients/outcomes/metrics, duplicate FAQs, inaccurate/admin-supplied FAQ schema, stale Homepage references, and the Pages wildcard swallowing module routes. Mitigations are plain-text storage and escaped Blade output, unique normalized slugs, visibility scopes, optional authors, no seeded content, exact administrator-entered metrics, server-generated schema, selective invalidation, and explicit routes before the wildcard.

## Architecture and schemas
`article_categories` provides unique slug, description, active state and contiguous display order. `articles` contains editorial plain text, optional local raster assets and author, server-derived approximate reading time (word count divided by 200, rounded up), lifecycle/audit timestamps, feature/order state and bounded SEO fields. Restrictive flat pivots relate at most six Articles/Solutions/Products/Industries/Capabilities/Pages; Article self-links and duplicates are rejected.

`case_studies` contains optional canonical Client and Testimonial references, challenge/solution/outcome plain text, optional project context, lifecycle/audit fields, CTA and SEO. Metrics (maximum eight) retain exact prefix/value/suffix strings without inferred formatting. Highlights are bounded to twelve. Restrictive pivots connect Solutions, Products, Industries and Capabilities (eight each) and Pages (six). No records are seeded, so no names, claims, quotes, screenshots, dates or outcomes are fabricated.

`faq_groups` supplies optional active groups and ordering. `faqs` stores bounded plain-text question/answer content with the shared lifecycle. FAQPage JSON-LD is generated server-side from exactly the publicly visible listing, safely JSON-escaped, and omitted from preview.

## Lifecycle, preview, scheduling, and dependencies
Save creates a draft. Publish stamps `published_at` and clears schedule; schedule parses the administrator value in the configured website timezone and stores UTC; unpublish preserves content; archive hides it; restore returns to draft; duplicate resets publication and audit state and receives a unique slug where applicable. Authenticated active administrators on installed sites can preview every state; responses are private/no-store and noindex/nofollow and bypass public caches. Article and FAQ dependency inspectors identify Homepage references; category/group restrictive keys prevent deleting referenced parents. Canonical Client/Testimonial data renders only while public.

## Homepage migrations
Nullable restrictive `homepage_items.article_id` and `homepage_items.faq_id` preserve every Phase 8 fallback field. A public canonical Article overrides title, excerpt, image, date and route only when visible. A public FAQ overrides its question and answer only when visible. Private targets reveal nothing and fallback data remains intact. Canonical changes selectively invalidate Homepage cache.

## SEO, caching, sitemap, performance
Articles and Case Studies validate title/description, same-host canonical URLs, robots directives, and safe local OG images. Minute-bucketed, versioned caches cover Article index/category/detail, Case Study index/filter/detail and FAQ grouped listing so due schedules cannot remain stale for longer than a minute; failures fall back safely and no global flush is used. Sitemap adds `/insights`, public indexable Article details, `/case-studies`, public indexable Case Study details and `/faq`. Queries paginate, eager-load bounded relations, cap selection/list data, and do not query from Blade.

## Content safety, security, and accessibility
Article, Case Study and FAQ narrative fields are plain text. Blade escaping and paragraph splitting preserve readable breaks without HTML, Markdown, Blade, PHP, JavaScript, CSS, iframe, embed or SVG execution. Images require alt text. Admin routes retain auth, active-administrator, installed-state, CSRF and throttling middleware. There are no roles, permissions, portal calls, external requests, registration changes or secret changes.

Public details use semantic articles, ordered headings, breadcrumbs, readable widths and text alternatives. FAQs use native buttons with `aria-expanded`, `aria-controls`, visible focus and Alpine disclosure state without hover dependency. Admin tables/forms remain responsive and labelled using existing components.

## Local validation and manual acceptance
Run migrations, route/config/view caches, focused and regression Pest suites, Pint, and the Vite production build locally. Manually exercise category/group creation, every lifecycle transition, UTC schedules before/after due time, previews, duplicates, archive dependencies, filters, relation limits, canonical Homepage fallback behavior, sitemap filtering, cache invalidation, raw markup escaping, metric truthfulness, FAQ disclosure/schema, mobile/keyboard/200% zoom, logs, registration lock, installer lock, licensing, and all Phase 9 modules.

## Known limitations and Phase 11 handoff
Phase 10 intentionally provides plain text rather than WYSIWYG/Markdown and does not animate metrics. It does not implement direct Article navigation types. Existing local asset path fields remain ready for a future migration. Phase 11 owns Media Library and Asset Management: uploaders, browsers, folders, attachment metadata, transformations and pickers are explicitly outside this phase.
