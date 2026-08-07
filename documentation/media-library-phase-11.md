# Phase 11 — Media Library and Asset Management

## Objective and status
Phase 11 establishes an administrator-only, image-first canonical Media Library without removing any legacy path column. It deliberately excludes Contact/Enquiry, Careers, remote import, arbitrary filesystem browsing, documents, audio/video, SVG, transformations, CDN, and cloud-storage administration.

## Full pre-change audit
The repository stored only validated local path strings and had no persistent upload workflow. Branding uses `site_logo_path`, `site_logo_dark_path`, and `favicon_path`. Homepage uses `homepage_settings.og_image_path`, `homepage_sections.image_path`, and `homepage_items.image_path` / `mobile_image_path`; canonical client, testimonial, article and other homepage records inherit the respective module paths. Pages use `pages.featured_image_path`, `pages.og_image_path`, and `page_sections.image_path`. Solutions, products, industries, capabilities, and work processes each use `featured_image_path` and `og_image_path`; products additionally use `product_gallery_items.image_path` and `product_integrations.logo_path`. Clients use `logo_path`; testimonials use `image_path`; articles and case studies use featured and OG image paths. Their adjacent `*_alt` columns remain context-specific.

`App\Domain\PublicChrome\PublicAssetPath` is the existing allow-list/traversal policy and public view-data classes apply it before rendering. `resources/views/components/public/image.blade.php` enforces alt text unless decorative and dimensions or an aspect ratio. The configured `public` disk roots at `storage/app/public`, generates `/storage` URLs, and `storage:link` maps `public/storage` there. System Health already checks writable storage and whether that link exists. No existing persistent admin upload handler or Livewire temporary-upload configuration was found.

Inspectable PHP limits were `upload_max_filesize=2M`, `post_max_size=8M`, `max_file_uploads=20`, and `memory_limit=128M`; local production PHP must be configured at least as high as the application limits. Composer contains no Intervention Image or equivalent. No GD/Imagick application assumption was found (native image probing is used, and local PHP needs format support). Existing models invalidate bounded module cache versions through model concerns and domain cache classes. Validation uses Form Requests, closure rules, allow-lists, and safe local path policies. Existing admin modal and file-input Blade components are available; there was no media picker. Tests use Pest, factories, `UploadedFile::fake()`, and `Storage::fake()` where filesystem behavior is involved.

Risks identified were traversal/absolute paths, executable content, extension/MIME spoofing, SVG XSS, oversized/decompression bombs, orphaned or duplicate physical files, deletion/replacement of referenced assets, filesystem-path disclosure, destructive path-column migrations, stale cached URLs, and DB/file transaction mismatches. Controls below address each risk. The planned files were the migration, three models, bounded registry/resolver/upload/URL/cache services, three admin controllers, routes/navigation, Media Library/detail/picker Blade views, tests, and this document.

## Architecture and schema
`media_assets` is canonical: UUID, single logical collection, fixed public disk, directory and random stored name, extension/MIME/bytes/dimensions/aspect ratio, reusable alt/title/caption/credit, original filename as metadata only, SHA-256, `image|favicon`, `active|archived`, uploader and timestamps. UUID is unique; status, type, creation time, and checksum are indexed. The internal path pair is unique.

`media_collections` is deliberately single-parent logical organization (`name`, unique `slug`, description, display order). It never controls physical paths. `media_usages` is the bounded attachment table with asset FK, morph alias/id, allow-listed slot, display order, lookup indexes, and a uniqueness constraint. `Relation::enforceMorphMap()` prevents stored PHP class names and unapproved morph types.

## Types, validation, and limits
Website images allow JPG/JPEG (`image/jpeg`), PNG (`image/png`), and WebP (`image/webp`) to 8 MiB. Favicons allow PNG and ICO (`image/x-icon`) to 1 MiB. SVG, PHP/PHTML/PHAR, HTML, JS, CSS, XML, executables, shells, archives, PDF/Office documents, and arbitrary binaries are prohibited by positive allow-list.

The server verifies successful upload, purpose, client extension, `finfo` MIME, an exact extension/MIME mapping, actual native image decoding/probing, bytes, dimensions, and pixels. Limits are 16×16 minimum, 8000×8000 maximum, and 40,000,000 pixels. A mismatch or unsupported decoder is rejected. Client `accept` is only a convenience. At most one upload is accepted per request and uploads are throttled to ten/minute.

## Storage, filenames, checksum, and metadata
Files are stored only on `public` beneath `media/YYYY/MM/`. The basename is a generated UUID plus validated extension; administrators cannot choose disk/directory/path and original names are never executable paths. Public URL data exposes only URL, alt suggestion, width, height, aspect ratio, and MIME. It never exposes server paths, disk configuration, checksum, or uploader.

SHA-256 is recorded for integrity and duplicate warning. Identical files are not silently merged; the administrator reuses the existing record or explicitly allows an intentional duplicate. Width, height, MIME, extension, byte size, ratio, original filename, and uploader are captured. EXIF is neither parsed nor displayed. With no safe processor dependency, embedded EXIF may remain: strip it before upload when required. No transformations or derivatives are generated; Phase 15 owns responsive optimization.

## Media Library, upload, and picker
The responsive browser provides server-side search (title/original name), type/status/collection filters, safe sort allow-list, 24-item pagination, lazy constrained previews, metadata, usage count, archive state, logical collections, upload, detail, replacement, and conservative deletion. It is not a file explorer.

The reusable Alpine/Blade picker is an accessible labelled modal, restores opener focus, closes with Escape, focuses search on open, searches paginated JSON, previews selections, and excludes archived assets server-side. It posts only an asset UUID and fixed model/slot route; there is no URL or path input. Branding exposes primary, dark, and favicon pickers. The component/service supports every approved Phase 11 type and can be embedded in the corresponding existing editors without changing legacy inputs.

## Usage allow-list and module integrations
Approved aliases/slots are: Branding (`primary_logo`, `dark_logo`, `favicon`); Homepage setting (`hero_image`, `mobile_hero_image`, `og_image`), section (`section_image`, `cta_background`), and item (`image`, `mobile_image`, `client_logo`, `testimonial_image`, `insight_image`); Page (`featured_image`, `og_image`) and Page Section (`section_image`); Solution, Industry, Capability, Work Process, Article, and Case Study (`featured_image`, `og_image`); Product (`featured_image`, `og_image`, ordered `gallery`, ordered `integration_logo`), Product Gallery Item (`gallery`), Product Integration (`integration_logo`); Client (`logo`); Testimonial (`image`). No request can supply a class name or unregistered slot.

`MediaResolver` implements canonical-first data resolution for each approved model/slot, with the existing `PublicAssetPath` legacy value second and a null/safe text fallback last. Active and archived records may resolve for existing references; only active records are selectable. Module alt fields remain authoritative context overrides, with asset alt text only a suggestion. OG consumers request an absolute configured-site URL. Existing path values and columns are never deleted or automatically rewritten.

A deterministic later adoption command may scan only the documented columns, accept only `PublicAssetPath`-approved paths under approved public roots, verify each physical image using the same inspector, create a record, then attach it after administrator confirmation. It must not run in a migration, assume existence, inspect arbitrary paths, or copy external files.

## Dependencies, archive, delete, and replacement
Details show a bounded (100) user-facing dependency list, never raw model names. Archiving prevents new selection but keeps existing references rendering. Hard deletion requires explicit confirmation, zero canonical usages, successful physical deletion before DB deletion, and never recursively deletes directories. A future adoption workflow must also register legacy dependencies before enabling their deletion.

Replacement validates and uploads a new random file first, updates the stable MediaAsset identity, deletes its temporary record, then removes only the old exact file. A failed validation/storage leaves the old record/file intact. UI warns that all usages change. Metadata edits never rename physical files.

## Cache invalidation
Metadata/replacement calls a focused usage map: Branding→Public Chrome; Homepage→Homepage; Page/section→Page; Solution→Solution; Product/gallery/integration→Product; Industry→Industry; Capability→Capability; Work Process→Work Process; Client and Testimonial→their caches (and their existing cache classes selectively propagate Homepage dependencies); Article and Case Study→their caches. No global cache flush occurs.

## Security, accessibility, and performance
All routes are inside `auth`, `administrator.active`, and `installed`; web CSRF applies, mutations are throttled, and upload/replace is stricter. There is no public upload, temporary unsigned endpoint, remote fetch, base64 database storage, disk/path chooser, SVG, executable support, role/permission change, registration change, licensing change, or secrets change. Detail serializers hide internal storage and hashes.

Upload has a labelled keyboard-accessible native input; drag/drop is optional. Cards are links with focus indicators and non-color status text. The modal has dialog semantics, label, Escape close, focus return, keyboard-selectable controls, and search status. Layouts reflow responsively and support zoom. Queries paginate, count usages without N+1, load no binary data, and render lazy images. Originals are visually constrained because transformations are unavailable.

## Local validation commands
Run: `composer install`, `npm install`, `php artisan optimize:clear`, `php artisan migrate`, `php artisan storage:link`, `vendor/bin/pint --dirty --format agent`, `php artisan route:list --except-vendor`, `php artisan test --compact tests/Feature/Media`, all listed Phase 1–10 regression suites, `php artisan test`, `npm run build`, then `php artisan config:cache`, `php artisan route:cache`, `php artisan view:cache`, and `php artisan optimize:clear`. Dependency installation commands are validation instructions only; Phase 11 adds no package.

## Manual acceptance
Locally perform the 68 requested checks: route protection; valid JPG/PNG/WebP/PNG favicon/ICO; all malicious, spoofed, oversize and bomb rejection; metadata/checksum/random naming; search/filter/pagination; archive/restore; picker keyboard/focus; attach every registered module slot including ordered product media; canonical priority and legacy fallback; dependency inspection; referenced-delete blocking and unreferenced file removal; replacement and selective invalidation; no remote import/server path/public upload/Phase 12 modules; responsive/200% zoom; all regression suites; route/config/view caches; production build; application/browser logs.

## Known limitations and Phase 12 handoff
No EXIF stripping, thumbnails, optimization, bulk upload, automatic legacy adoption, or enterprise DAM workflow is included. ICO acceptance depends on local native image probing. PHP's own upload/post limits can reject before Laravel and must be raised locally. Physical-file/DB operations cannot be globally atomic; recovery ordering intentionally preserves a record on delete failure and preserves the old asset until replacement persistence. Phase 12 remains exclusively Contact Enquiries, Demo/Proposal Requests, spam/rate limits, administration, and notification foundations; none is implemented here.
