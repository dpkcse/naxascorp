<?php

namespace App\Domain\Media;

use App\Domain\Articles\ArticleCache;
use App\Domain\Capabilities\CapabilityCache;
use App\Domain\CaseStudies\CaseStudyCache;
use App\Domain\Clients\ClientCache;
use App\Domain\Homepage\HomepageCache;
use App\Domain\Industries\IndustryCache;
use App\Domain\Pages\PageCache;
use App\Domain\Products\ProductCache;
use App\Domain\PublicChrome\PublicChromeCache;
use App\Domain\Solutions\SolutionCache;
use App\Domain\Testimonials\TestimonialCache;
use App\Domain\WorkProcesses\WorkProcessCache;
use App\Models\MediaAsset;

final class MediaCacheInvalidator
{
    public function invalidate(MediaAsset $asset): void
    {
        $asset->usages()->get(['mediable_type', 'mediable_id'])->each(function ($usage): void {
            match ($usage->mediable_type) {
                'branding' => PublicChromeCache::forgetAll(),
                'homepage_setting', 'homepage_section', 'homepage_item' => HomepageCache::forget(),
                'page' => PageCache::forget($usage->mediable_id),
                'page_section' => $usage->mediable?->page_id ? PageCache::forget((int) $usage->mediable->page_id) : null,
                'solution' => SolutionCache::forget($usage->mediable_id),
                'product' => ProductCache::forget($usage->mediable_id),
                'product_gallery_item', 'product_integration' => $usage->mediable?->product_id ? ProductCache::forget((int) $usage->mediable->product_id) : null,
                'industry' => IndustryCache::forget($usage->mediable_id),
                'capability' => CapabilityCache::forget($usage->mediable_id),
                'work_process' => WorkProcessCache::forget($usage->mediable_id),
                'client' => ClientCache::forget($usage->mediable_id),
                'testimonial' => TestimonialCache::forget($usage->mediable_id),
                'article' => ArticleCache::forget($usage->mediable_id),
                'case_study' => CaseStudyCache::forget($usage->mediable_id),
                default => null,
            };
        });
    }
}
