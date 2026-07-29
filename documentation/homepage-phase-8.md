# Phase 8 — Structured Homepage CMS

## Objective and architecture

Phase 8 replaces the design-system demonstration homepage with a server-rendered, structured CMS homepage. It is deliberately not a page builder: `HomepageSectionRegistry` is the only allow-list, every section is a singleton, presentation choices are bounded, and database values never select Blade components.

The singleton `homepage_settings` row controls metadata and publication. `homepage_sections` holds the fourteen fixed sections and their contiguous order. `homepage_items` is a normalized homepage-only child-record table using a constrained registry item type; it avoids thirteen nearly identical tables while keeping content relational rather than an unrestricted JSON document.

## Section registry and schema

The registry contains hero, trust strip, about, featured solutions, featured products, capabilities, industries, process, clients, statistics, testimonials, insights, FAQ, and bottom CTA. Each definition supplies its label, purpose, item type, default state, and maximum count. All are singleton sections. Featured solutions/products, industries, clients, testimonials, and insights are explicitly homepage-only previews and have no fabricated detail routes.

Settings contain the page eyebrow/title/highlight/description, paired CTAs, local OG image, status, and publication timestamp. Sections contain headings, bounded presentation variants, optional local image, and paired CTAs. Items contain bounded common fields covering hero slides, trust statements, cards, process steps, clients, exact-text statistics, attributed testimonials, insight dates/destinations, and FAQ answers. Unique and indexed columns protect the singleton and public ordering paths.

## Workflow, ordering, preview, and cache

Saving settings always creates a draft and never auto-publishes. Publish validates the enabled hero has an active slide, records `published_at`, and invalidates `public.homepage.published`. Unpublish preserves every record and clears the public payload. Published content edits invalidate homepage cache without flushing unrelated public chrome. Cache failures fall back to bounded database loading; preview always bypasses cache and displays a noindex/no-store Preview Mode banner.

Move Up/Move Down uses a database transaction, row locks, and contiguous normalization. Disabled sections retain their order. Sections cannot be deleted; singleton settings have no delete route. Leaf deletion uses a CSRF-protected DELETE form with confirmation and never deletes referenced files.

## Section behavior

- **Hero:** up to six active/manual slides, no autoplay or carousel dependency, eager primary image, keyboard arrow and labeled previous/next controls.
- **Hero tabs:** the relational item design reserves structured item types for tabs; the current public UI does not activate tabs until at least two validated tabs exist. No arbitrary SVG is accepted.
- **Trust strip:** up to six administrator-entered statements; no claims are seeded.
- **About:** singleton plain-text fields, paired calls to action, and optional local image.
- **Featured previews:** homepage-specific solution and product cards only; no standalone records or detail routes.
- **Capabilities / industries / process:** bounded ordered summaries. Process numbering is derived from public order.
- **Clients:** up to 24 legitimate entries, safe optional website URL, text fallback without a logo.
- **Statistics:** exact text values with bounded prefix/suffix; no animation or generated claims.
- **Testimonials:** bounded plain-text quotes and optional 1–5 rating; no review schema.
- **Insights:** up to six homepage-only previews with optional truthful date and safe URL.
- **FAQ:** up to 20 plain-text disclosures using button, `aria-expanded`, and `aria-controls`; no FAQ schema.
- **Bottom CTA:** singleton light/navy/gradient presentation using the existing public CTA component.

## Asset and content policy

No upload or Media Library was introduced. Images are local `storage/`, `images/`, or `assets/` references with approved raster extensions; traversal, schemes, absolute paths, backslashes, SVG, and executables are rejected. Alt text is mandatory with images. CTAs must have both label and safe relative/HTTP(S) URL. All content is rendered with escaped Blade output. Raw HTML, Blade, PHP, JavaScript, CSS, iframe, SVG, embeds, remote downloads, and external APIs are unsupported.

## Query, performance, accessibility, and deletion safety

`HomepageViewData` performs one settings query and one bounded section query with a bounded eager-loaded item query. Public Blade contains no Eloquent access. Only transformed public-safe arrays are cached for 30 minutes. The page remains server rendered with existing Vite assets and minimal Alpine. Below-fold images lazy-load; hero images are eager and have aspect ratios to reduce layout shift. FAQ and hero controls are keyboard operable, focus-visible, labeled, motion-neutral, and semantic. No content or asset is silently deleted.

## Local validation commands

```sh
composer install
npm install
php artisan optimize:clear
vendor/bin/pint --dirty --format agent
php artisan route:list --except-vendor
php artisan migrate
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

1. Open Homepage management and verify guest, inactive-account, and uninstalled protection.
2. Save draft settings; reorder and enable/disable sections.
3. Add one and multiple hero slides; confirm manual/keyboard controls, no autoplay, reduced-motion safety, and eager primary image.
4. Configure tabs (when enabled), trust strip, About, capabilities, industries, and process.
5. Add only legitimate clients, truthful statistics, real testimonials, insight previews, and FAQs.
6. Configure the bottom CTA; preview and confirm the banner, noindex/no-store response, and absence from the public page.
7. Publish; verify order, hidden disabled sections, safe missing-image behavior, and cache invalidation.
8. Verify there is no fake content, portal request, request token, entitlement, remote asset, or raw HTML.
9. Check mobile through wide desktop, overflow, keyboard focus, FAQ/hero labels, and 200% zoom.
10. Run all commands above; inspect logs; regress installer, license, authentication, registration closure, dashboard, and public chrome.

## Known limitations and Phase 9 handoff

There is no Media Library or upload UI, rich text, autoplay, arbitrary layout, standalone content relationship, or public detail route. Homepage-specific previews will require an explicit migration/handoff once canonical modules exist. Phase 9 should split into: **9A Pages**, **9B Solutions**, **9C Products**, **9D Industries**, **9E Capabilities and Work Process**, and **9F Clients, Testimonials and Statistics**. Phase 9 can then replace homepage preview records with bounded references without changing the registry or allowing arbitrary builders.
