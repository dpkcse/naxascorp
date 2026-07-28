# Phase 6: Public design-system foundation

## Objective

Phase 6 replaces the framework welcome screen with a reusable, premium public Blade shell for Naxora CMS. It establishes presentation primitives only; it does not add content records, public form processing, a homepage builder, navigation/footer CRUD, roles, permissions, or media management.

## Audit findings

- `/` was an unauthenticated closure rendering the stock `welcome` view. That view embedded a large generated stylesheet and requested Instrument Sans from Bunny Fonts, creating the principal page-weight, privacy, and render-blocking risks.
- There was no reusable public layout, navigation, footer, form, image, metadata, breadcrumb, sitemap, or public component test suite. `public/robots.txt` allowed crawling and remains intentionally unchanged.
- Auth, installer, settings, and admin layouts are separate Blade/Flux shells. Admin and installer layouts already emit `noindex, nofollow`, skip links, focus styles, and Vite/Flux assets. Their route middleware and views are not changed by Phase 6.
- `WebsiteSettings::current()` is the existing singleton accessor. The public controller calls it once and catches unavailable-database failures so the pre-install fallback remains safe. The stored fields support site/legal names, tagline, email, phone, country, locale, timezone, and canonical site URL.
- Product name/version fallbacks live in `config/app.php`; Phase 6 does not expose the version publicly. The prior shared head partial only supplied charset, viewport, title, Vite, and Flux appearance; it remains used by non-public layouts.
- Tailwind 4 is CSS-first in `resources/css/app.css`, with system/Instrument Sans fallback typography, admin tokens, standard Tailwind breakpoints, a class-driven admin dark mode, global reduced-motion handling, and Vite asset discovery. Phase 6 replaces Instrument Sans as the public/global first choice with a system and Bangla-ready fallback stack and adds related-but-distinct `public-*` tokens without altering admin tokens.
- Alpine was already used in auth/admin views and supplied through Flux scripts. The public shell uses that existing mechanism and a small component registered in `resources/js/app.js`; no library was installed. Flux/Heroicon-compatible patterns remain available, while public primitives avoid depending on authenticated state or Livewire components.
- Existing icon usage is Flux/Heroicons plus small purposeful text glyphs. No icon package, external font, external image, or JavaScript framework was added.
- Existing responsive behavior uses Tailwind defaults (`sm`, `md`, `lg`, `xl`, `2xl`); the public system follows those breakpoints. Existing accessibility includes landmarks, skip links, labels, focus rings, disabled states, and reduced motion, which the public shell extends.
- Regression risks are route name collisions, settings-table availability before installation, metadata duplication, Alpine focus/scroll cleanup, CSS token collision, and unintended calls to licensing services. The controller-only root replacement, prefixed public tokens, isolated components, single settings lookup, and focused tests contain those risks. Installer, authentication, dashboard, settings, and licensing routes are untouched.

## Architecture

`PublicSiteController` resolves website settings once per request and passes escaped scalar metadata and a configuration-backed navigation structure to `welcome.blade.php`. If the database is unavailable, the controller supplies `Naxora CMS` and `Naxas Innovations Limited` without fabricated contact information. The `x-layouts.public-site` document owns metadata, landmarks, announcements, header, footer, asset entry points, and notification live region.

### Top bar, header, and navigation

The top bar renders only configured email, phone, and country values; email and normalized phone destinations use `mailto:` and `tel:`. The sticky solid header is future-variant-ready and includes server-derived current-route state. Only Home links to a route. All future destinations and Book a Demo are disabled and announced as coming soon.

The mobile drawer is an Alpine-only interaction with an accessible opener, modal dialog semantics, close button, overlay close, Escape handling, focus entry/return, a focus loop, nested Company disclosure, at-least-44px controls, body scroll locking, and reduced-motion-compatible transitions. No authorization or validation logic is client-side.

### Footer

The footer renders configured branding, legal identity, tagline, and contact values. It omits absent real-world data, social profiles, addresses, certifications, and legal links. Future columns use honest disabled labels. Naxas Innovations Limited attribution is retained and the CMS version is not exposed.

## Design tokens and typography

Public tokens cover background, surface, alternate surface, primary/secondary/muted text, navy, primary/accent blue, border, semantic status colors, focus, radius, and shadow. They are prefixed to avoid duplicating or overriding Phase 5 admin semantics. Typography uses local system fonts plus Bangla-ready `Noto Sans Bengali`, `Nirmala UI`, and `Vrinda` fallbacks; there is no network font request. Headings use responsive sizing and balanced wrapping; body copy remains at least 16px with readable line heights.

## Containers, sections, and grids

`public-container`, `public-container-narrow`, and `public-container-wide` provide standard, narrow, and wide widths. The section component supports standard and alternate surfaces, decoration/action slots, consistent responsive spacing, and a reusable heading/eyebrow/description structure. The grid primitive supports two-, three-, and four-column mobile-first card patterns. Direct Tailwind grids in the preview demonstrate asymmetric content/form layouts; future modules can compose logo, statistic, card, or sidebar patterns from the same primitives without fixed widths.

## Buttons, cards, and CTA

The button supports anchors and native buttons, primary, secondary, outline, ghost, light-on-dark, dark-outline, and icon variants, plus sizing, loading, disabled, focus, and minimum touch-target states. A disabled link is rendered as a disabled button so unsafe destinations cannot be followed.

The polymorphic card foundation supports basic, feature, solution/product, industry, statistic, testimonial, article, logo/client, contact, and dark presentations through variant/slot composition. It accepts icon/media, title, description, action/link, disabled, hover/focus, and full-height behavior without defining database-backed content. CTA supports light, navy, restrained gradient, actions, and an optional media slot for a future image overlay.

## Breadcrumb, forms, images, and states

The breadcrumb is a semantic, schema-ready `BreadcrumbList`, always roots at Home, marks the current item, and truncates safely. Public form controls include text/email/phone through the typed input, select, textarea, checkbox/consent, file placeholder, helpers, required labels, associated errors, and a validation summary. Buttons expose loading state; alerts and empty states expose success/error feedback. These are presentation-only and expect future server-side validation.

The responsive image requires meaningful alt text unless explicitly decorative and requires dimensions or an aspect class to prevent layout shift. It supports `srcset`, `sizes`, `loading`, `fetchpriority`, and `decoding`; non-critical images default to lazy loading. Missing sources render a local fallback state. Critical future hero media must explicitly set eager loading/high priority. No remote placeholder is used.

Information, success, warning, and error alerts use semantic live roles. Empty, unavailable/coming-soon, loading/skeleton, and disabled states use readable text rather than color alone. Important errors are inline, not toast-only.

## Accessibility and motion

The implementation targets WCAG 2.1 AA-oriented behavior through skip navigation, semantic landmarks, proper heading order, visible focus, descriptive labels, current-page state, non-color status communication, keyboard-operable disclosures, drawer focus management, responsive touch targets, zoom-safe flexible sizing, and horizontal-overflow prevention. Motion is limited to hover/focus and drawer transitions. The global reduced-motion query disables meaningful animation durations; there is no parallax, scroll-jacking, autoplay, or reveal library.

## SEO, robots, and sitemap decision

The public document emits exactly one escaped title, description, normalized configured-root canonical URL, index/follow robots directive, Open Graph type/site/title/description/URL, optional OG image, Twitter card, and theme color. Empty OG images are omitted. Admin and installer retain their existing `noindex, nofollow` metadata.

The existing permissive static `robots.txt` remains appropriate because the only implemented public content route is `/`. A sitemap is deferred until a later SEO/content phase to avoid machinery for a single route. No admin, installer, settings, or license URL is advertised.

## Performance and temporary homepage

The page uses server-rendered Blade, production Vite entry points, system fonts, no external API/media/font requests, no SPA, and only a small Alpine drawer controller. Settings are queried once; no entitlement revalidation or license portal client is resolved. Images support dimensions and lazy loading. The old embedded generated stylesheet is removed. Flux scripts provide the repository's existing Alpine runtime; this is the known baseline cost until the asset strategy is revisited.

The root remains public before and after installation. Before settings/database availability it safely identifies Naxora CMS and hides the contact bar. After installation it uses configured website and legal names, contact details, locale, tagline, and site URL. The temporary preview truthfully states that content modules are pending and contains no client counts, statistics, awards, testimonials, case studies, or claimed deployments.

## Automated coverage

`tests/Feature/PublicSite/PhaseSixPublicSiteTest.php` covers anonymous access, fallbacks, configured settings, optional contacts, semantic landmarks, navigation/mobile hooks, disabled future destinations, metadata escaping/uniqueness/indexing, absence of outbound HTTP, public primitives, form accessibility, responsive-image contracts, and scope exclusions. Existing dashboard, auth, installer, and licensing suites remain the regression authority.

## Local validation commands

```bash
composer install
npm install
php artisan optimize:clear
vendor/bin/pint --dirty --format agent
php artisan route:list --except-vendor
php artisan migrate
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

- [ ] Open `/` before installation and confirm safe fallback; complete installation and confirm configured settings.
- [ ] Confirm missing top-bar/footer data disappears and configured phone/email destinations are correct.
- [ ] Check desktop header and active Home state; verify every future destination is disabled.
- [ ] At 320px and above, open/close the drawer by toggle, overlay, close button, and Escape.
- [ ] Confirm focus enters, loops within, and returns to the opener; body scrolling unlocks after close.
- [ ] Verify footer identities and absence of fake address, social, legal, certification, or metric data.
- [ ] Inspect one title, description, canonical, OG metadata, public indexing, and admin/installer noindex.
- [ ] Exercise buttons, cards, headings, CTA, breadcrumb, controls, alerts, empty/loading, and image states.
- [ ] Check mobile, tablet, laptop, desktop, wide desktop, 200% zoom, no horizontal overflow, keyboard-only navigation, visible focus, and reduced motion.
- [ ] Confirm browser network activity has no external fonts, sample images, or license portal requests.
- [ ] Confirm `/login`, `/dashboard`, license diagnostics, settings, registration closure, and installer lock behavior.
- [ ] Inspect recent logs/rendered output for secrets and validate route/config/view caches and production build.

## Known limitations

Runtime, browser, build, database, and cache validation is environment-dependent. The preview form intentionally does not submit. Future routes, mega menus, social/legal links, brand media, and CMS content remain disabled. Text glyphs are intentionally minimal; Phase 7 can replace them with established Flux/Heroicon components where dynamic branding warrants it. A sitemap and full SEO Manager are out of scope.

## Phase 7 handoff

Phase 7 may add navigation tables, menu locations, nested menu items, header settings, footer columns, configured social links, branding settings, logo/favicon media references, and public rendering from CMS navigation data. It should replace `config/public-site.php` through a cached presentation service while preserving disabled safety, semantic markup, focus behavior, fallbacks, and single-load request performance. Phase 6 intentionally adds none of those CRUD modules.
