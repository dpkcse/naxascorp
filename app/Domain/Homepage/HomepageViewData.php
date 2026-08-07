<?php

namespace App\Domain\Homepage;

use App\Domain\PublicChrome\PublicAssetPath;
use App\Domain\PublicChrome\PublicLink;
use App\Models\HomepageSection;
use App\Models\HomepageSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Throwable;

final class HomepageViewData
{
    /** @return array<string, mixed>|null */
    public function published(): ?array
    {
        try {
            return Cache::remember(HomepageCache::PUBLISHED, now()->addMinutes(30), fn (): ?array => $this->load(false));
        } catch (Throwable) {
            return $this->safelyLoad(false);
        }
    }

    /** @return array<string, mixed>|null */
    public function preview(): ?array
    {
        return $this->safelyLoad(true);
    }

    /** @return array<string, mixed>|null */
    private function safelyLoad(bool $preview): ?array
    {
        try {
            return $this->load($preview);
        } catch (Throwable) {
            return null;
        }
    }

    /** @return array<string, mixed>|null */
    private function load(bool $preview): ?array
    {
        if (! Schema::hasTable('homepage_settings') || ! Schema::hasTable('homepage_sections')) {
            return null;
        }

        $settingsQuery = HomepageSetting::query()->where('singleton_key', 1);
        if (! $preview) {
            $settingsQuery->where('status', 'published');
        }
        $settings = $settingsQuery->first();
        if (! $settings) {
            return null;
        }

        $registry = HomepageSectionRegistry::all();
        $sections = HomepageSection::query()
            ->when(! $preview, fn ($query) => $query->where('is_enabled', true))
            ->orderBy('display_order')->orderBy('id')
            ->with(['workProcess.stages.deliverables', 'items' => fn ($query) => $query->with(['solution','product','industry','capability','client','testimonial.client','statistic'])->where('is_active', true)->orderBy('display_order')->orderBy('id')->limit(24)])
            ->limit(count($registry))->get()
            ->filter(fn (HomepageSection $section): bool => isset($registry[$section->section_key]))
            ->map(function (HomepageSection $section) use ($registry): array {
                $definition = $registry[$section->section_key];
                return [
                    'key' => $section->section_key,
                    'enabled' => (bool) $section->is_enabled,
                    'eyebrow' => $section->eyebrow,
                    'heading' => $section->heading,
                    'description' => $section->description,
                    'secondary_description' => $section->secondary_description,
                    'image_path' => PublicAssetPath::isSafe($section->image_path) ? $section->image_path : null,
                    'image_alt' => $section->image_alt,
                    'primary_cta' => $this->link($section->primary_cta_label, $section->primary_cta_url),
                    'secondary_cta' => $this->link($section->secondary_cta_label, $section->secondary_cta_url),
                    'background' => in_array($section->background_style, HomepageSectionRegistry::BACKGROUNDS, true) ? $section->background_style : 'default',
                    'width' => in_array($section->content_width, HomepageSectionRegistry::WIDTHS, true) ? $section->content_width : 'standard',
                    'tabs' => $section->items->where('item_type', $definition['secondary_item_type'])->take($definition['secondary_maximum'])->map(fn ($item): array => ['id' => $item->id, 'label' => $item->title, 'description' => $item->description, 'icon' => $item->icon])->values()->all(),
                    'canonical_process' => $section->workProcess?->isPubliclyVisible() ? ['title'=>$section->workProcess->title,'url'=>route('work-processes.show',$section->workProcess->slug),'stages'=>$section->workProcess->stages->where('is_active',true)->take(12)->map(fn($stage)=>['title'=>$stage->title,'description'=>$stage->description,'duration'=>$stage->duration_text])->values()->all()] : null,
                    'items' => $section->items->where('item_type', $definition['item_type'])->take($definition['maximum'])->map(fn ($item): array => [
                        'id' => $item->id, 'title' => $this->itemTitle($item), 'eyebrow' => $item->eyebrow, 'description' => $this->itemDescription($item), 'canonical_capability' => $item->capability?->isPubliclyVisible() ? ['url'=>route('capabilities.show',$item->capability->slug)] : null, 'canonical_industry' => $item->industry?->isPubliclyVisible() ? ['url' => route('industries.show', $item->industry->slug)] : null, 'canonical_solution' => $item->solution?->isPubliclyVisible() ? ['url' => route('solutions.show', $item->solution->slug)] : null, 'canonical_product' => $item->product?->isPubliclyVisible() ? ['url' => route('products.show', $item->product->slug)] : null,
                        'secondary_text' => $item->secondary_text, 'highlighted_text' => $item->highlighted_text, 'icon' => $item->icon,
                        'badge' => $item->badge, 'image_path' => $this->itemImage($item), 'mobile_image_path' => PublicAssetPath::isSafe($item->mobile_image_path) ? $item->mobile_image_path : null,
                        'image_alt' => $item->client?->isPubliclyVisible() ? $item->client->logo_alt : ($item->testimonial?->isPubliclyVisible() ? $item->testimonial->image_alt : $item->image_alt), 'primary_cta' => $this->link($item->primary_cta_label, $item->primary_cta_url),
                        'secondary_cta' => $this->link($item->secondary_cta_label, $item->secondary_cta_url), 'organization' => $item->testimonial?->isPubliclyVisible() ? ($item->testimonial->client?->isPubliclyVisible() ? $item->testimonial->client->name : $item->testimonial->organization_name) : $item->organization,
                        'value' => $item->statistic?->isPubliclyVisible() ? $item->statistic->value : $item->value, 'prefix' => $item->statistic?->isPubliclyVisible() ? $item->statistic->prefix : $item->prefix, 'suffix' => $item->statistic?->isPubliclyVisible() ? $item->statistic->suffix : $item->suffix, 'rating' => $item->testimonial?->isPubliclyVisible() ? $item->testimonial->rating : $item->rating,
                        'canonical_client' => $item->client?->isPubliclyVisible() ? ['name'=>$item->client->name,'logo_path'=>PublicAssetPath::isSafe($item->client->logo_path)?$item->client->logo_path:null,'logo_alt'=>$item->client->logo_alt,'website_url'=>PublicLink::isSafeUrl($item->client->website_url)?$item->client->website_url:null,'url'=>route('clients.show',$item->client->slug)] : null,
                        'canonical_testimonial' => $item->testimonial?->isPubliclyVisible() ? ['person_name'=>$item->testimonial->person_name,'designation'=>$item->testimonial->designation,'quote'=>$item->testimonial->quote,'image_path'=>PublicAssetPath::isSafe($item->testimonial->image_path)?$item->testimonial->image_path:null,'image_alt'=>$item->testimonial->image_alt] : null,
                        'canonical_statistic' => $item->statistic?->isPubliclyVisible() ? ['label'=>$item->statistic->label,'description'=>$item->statistic->description,'source_note'=>$item->statistic->source_note,'as_of_date'=>$item->statistic->as_of_date?->toDateString()] : null,
                        'published_on' => $item->published_on?->toDateString(),
                    ])->values()->all(),
                ];
            })->values()->all();

        return [
            'settings' => ['eyebrow' => $settings->eyebrow, 'title' => $settings->title, 'highlighted_text' => $settings->highlighted_text, 'description' => $settings->description, 'primary_cta' => $this->link($settings->primary_cta_label, $settings->primary_cta_url), 'secondary_cta' => $this->link($settings->secondary_cta_label, $settings->secondary_cta_url), 'og_image_path' => PublicAssetPath::isSafe($settings->og_image_path) ? $settings->og_image_path : null, 'status' => $settings->status],
            'sections' => $sections,
        ];
    }


    private function itemTitle($item): string
    {
        foreach (['client' => 'name', 'testimonial' => 'person_name', 'statistic' => 'label', 'capability' => 'title', 'industry' => 'title', 'product' => 'title', 'solution' => 'title'] as $relation => $field) {
            if ($item->{$relation}?->isPubliclyVisible()) { return (string) $item->{$relation}->{$field}; }
        }
        return (string) $item->title;
    }

    private function itemDescription($item): ?string
    {
        foreach (['client' => 'short_description', 'testimonial' => 'quote', 'statistic' => 'description', 'capability' => 'short_description', 'industry' => 'short_description', 'product' => 'short_description', 'solution' => 'short_description'] as $relation => $field) {
            if ($item->{$relation}?->isPubliclyVisible()) { return $item->{$relation}->{$field}; }
        }
        return $item->description;
    }

    private function itemImage($item): ?string
    {
        foreach (['client' => 'logo_path', 'testimonial' => 'image_path', 'product' => 'featured_image_path', 'solution' => 'featured_image_path'] as $relation => $field) {
            if ($item->{$relation}?->isPubliclyVisible() && PublicAssetPath::isSafe($item->{$relation}->{$field})) { return $item->{$relation}->{$field}; }
        }
        return PublicAssetPath::isSafe($item->image_path) ? $item->image_path : null;
    }

    /** @return array{label:string,url:string,external:bool}|null */
    private function link(?string $label, ?string $url): ?array
    {
        if (! $label || ! $url || ! PublicLink::isSafeUrl($url)) {
            return null;
        }

        return ['label' => $label, 'url' => $url, 'external' => preg_match('#^https?://#i', $url) === 1];
    }
}
