<?php

namespace App\Domain\Pages;

use App\Domain\PublicChrome\PublicAssetPath;
use App\Domain\PublicChrome\PublicLink;
use App\Models\Page;
use Illuminate\Support\Facades\Cache;
use Throwable;

final class PageViewData
{
    /** @return array<string,mixed>|null */
    public function published(string $slug): ?array
    {
        try {
            $page = Page::query()->publiclyVisible()->where('slug', $slug)->first();
            if (! $page) { return null; }
            $ttl = now()->addMinutes(30);
            return Cache::remember(PageCache::key($page->id, $slug).'.'.PageCache::version($page->id), $ttl, fn (): array => $this->transform($this->load($page), false));
        } catch (Throwable) { return $this->safeDirect($slug); }
    }

    /** @return array<string,mixed> */
    public function preview(Page $page): array { return $this->transform($this->load($page), true); }

    private function load(Page $page): Page
    {
        return $page->load(['parent.parent', 'children' => fn ($query) => $query->publiclyVisible()->orderBy('display_order')->limit(30), 'sections' => fn ($query) => $query->orderBy('display_order')->orderBy('id')->limit(PageSectionRegistry::MAXIMUM)]);
    }

    /** @return array<string,mixed> */
    private function transform(Page $page, bool $preview): array
    {
        $sections = $page->sections->filter(fn ($section): bool => $preview || $section->is_enabled)->filter(fn ($section): bool => in_array($section->section_type, PageSectionRegistry::TYPES, true))->map(fn ($section): array => [
            'id' => $section->id, 'type' => $section->section_type, 'heading' => $section->heading, 'eyebrow' => $section->eyebrow, 'body' => $section->body,
            'image' => PublicAssetPath::isSafe($section->image_path) ? $section->image_path : null, 'image_alt' => $section->image_alt,
            'primary_cta' => $this->link($section->primary_cta_label, $section->primary_cta_url), 'secondary_cta' => $this->link($section->secondary_cta_label, $section->secondary_cta_url),
            'background' => in_array($section->background_style, PageSectionRegistry::BACKGROUNDS, true) ? $section->background_style : 'default', 'width' => in_array($section->content_width, PageSectionRegistry::WIDTHS, true) ? $section->content_width : 'standard', 'enabled' => (bool) $section->is_enabled,
        ])->values()->all();
        return ['id' => $page->id, 'title' => $page->title, 'slug' => $page->slug, 'eyebrow' => $page->eyebrow, 'summary' => $page->summary, 'body' => $page->body,
            'template' => array_key_exists($page->template, PageTemplateRegistry::all()) ? $page->template : 'standard', 'show_breadcrumb' => (bool) $page->show_breadcrumb, 'show_title' => (bool) $page->show_title,
            'featured_image' => PublicAssetPath::isSafe($page->featured_image_path) ? $page->featured_image_path : null, 'featured_image_alt' => $page->featured_image_alt,
            'meta_title' => $page->meta_title ?: $page->title, 'meta_description' => $page->meta_description ?: $page->summary, 'canonical_url' => $page->canonical_url,
            'og_title' => $page->og_title ?: ($page->meta_title ?: $page->title), 'og_description' => $page->og_description ?: ($page->meta_description ?: $page->summary), 'og_image' => PublicAssetPath::isSafe($page->og_image_path) ? $page->og_image_path : null,
            'robots' => $preview ? 'noindex, nofollow' : (($page->robots_index ? 'index' : 'noindex').', '.($page->robots_follow ? 'follow' : 'nofollow')), 'sections' => $sections,
            'ancestors' => app(PageHierarchy::class)->breadcrumbs($page), 'siblings' => $page->children->map(fn (Page $child): array => ['title' => $child->title, 'url' => route('pages.show', $child->slug)])->all(), 'updated_at' => $page->updated_at?->toAtomString()];
    }

    /** @return array<string,mixed>|null */
    private function safeDirect(string $slug): ?array { try { $page = Page::query()->publiclyVisible()->where('slug', $slug)->first(); return $page ? $this->transform($this->load($page), false) : null; } catch (Throwable) { return null; } }
    /** @return array{label:string,url:string}|null */
    private function link(?string $label, ?string $url): ?array { return $label && $url && PublicLink::isSafeUrl($url) ? ['label' => $label, 'url' => $url] : null; }
}
