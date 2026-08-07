<?php

namespace App\Domain\Media;

use App\Models\{Article, BrandingSetting, Capability, CaseStudy, Client, HomepageItem, HomepageSection, HomepageSetting, Industry, Page, PageSection, Product, ProductGalleryItem, ProductIntegration, Solution, Testimonial, WorkProcess};
use Illuminate\Database\Eloquent\Model;

final class MediaRegistry
{
    /** @var array<string, array{class: class-string<Model>, label: string, slots: array<string, string>, multiple: array<int, string>}> */
    public const TYPES = [
        'branding' => ['class' => BrandingSetting::class, 'label' => 'Branding', 'slots' => ['primary_logo' => 'Primary logo', 'dark_logo' => 'Dark logo', 'favicon' => 'Favicon'], 'multiple' => []],
        'homepage_setting' => ['class' => HomepageSetting::class, 'label' => 'Homepage', 'slots' => ['hero_image' => 'Hero image', 'mobile_hero_image' => 'Mobile hero image', 'og_image' => 'Open Graph image'], 'multiple' => []],
        'homepage_section' => ['class' => HomepageSection::class, 'label' => 'Homepage section', 'slots' => ['section_image' => 'Section image', 'cta_background' => 'CTA background'], 'multiple' => []],
        'homepage_item' => ['class' => HomepageItem::class, 'label' => 'Homepage item', 'slots' => ['image' => 'Image', 'mobile_image' => 'Mobile image', 'client_logo' => 'Client logo', 'testimonial_image' => 'Testimonial image', 'insight_image' => 'Insight image'], 'multiple' => []],
        'page' => ['class' => Page::class, 'label' => 'Page', 'slots' => ['featured_image' => 'Featured image', 'og_image' => 'Open Graph image'], 'multiple' => []],
        'page_section' => ['class' => PageSection::class, 'label' => 'Page section', 'slots' => ['section_image' => 'Section image'], 'multiple' => []],
        'solution' => ['class' => Solution::class, 'label' => 'Solution', 'slots' => ['featured_image' => 'Featured image', 'og_image' => 'Open Graph image'], 'multiple' => []],
        'product' => ['class' => Product::class, 'label' => 'Product', 'slots' => ['featured_image' => 'Featured image', 'og_image' => 'Open Graph image', 'gallery' => 'Gallery', 'integration_logo' => 'Integration logo'], 'multiple' => ['gallery', 'integration_logo']],
        'product_gallery_item' => ['class' => ProductGalleryItem::class, 'label' => 'Product gallery item', 'slots' => ['gallery' => 'Gallery image'], 'multiple' => []],
        'product_integration' => ['class' => ProductIntegration::class, 'label' => 'Product integration', 'slots' => ['integration_logo' => 'Integration logo'], 'multiple' => []],
        'industry' => ['class' => Industry::class, 'label' => 'Industry', 'slots' => ['featured_image' => 'Featured image', 'og_image' => 'Open Graph image'], 'multiple' => []],
        'capability' => ['class' => Capability::class, 'label' => 'Capability', 'slots' => ['featured_image' => 'Featured image', 'og_image' => 'Open Graph image'], 'multiple' => []],
        'work_process' => ['class' => WorkProcess::class, 'label' => 'Work Process', 'slots' => ['featured_image' => 'Featured image', 'og_image' => 'Open Graph image'], 'multiple' => []],
        'client' => ['class' => Client::class, 'label' => 'Client', 'slots' => ['logo' => 'Logo'], 'multiple' => []],
        'testimonial' => ['class' => Testimonial::class, 'label' => 'Testimonial', 'slots' => ['image' => 'Person image'], 'multiple' => []],
        'article' => ['class' => Article::class, 'label' => 'Article', 'slots' => ['featured_image' => 'Featured image', 'og_image' => 'Open Graph image'], 'multiple' => []],
        'case_study' => ['class' => CaseStudy::class, 'label' => 'Case Study', 'slots' => ['featured_image' => 'Featured image', 'og_image' => 'Open Graph image'], 'multiple' => []],
    ];

    /** @return array<string, class-string<Model>> */
    public static function morphMap(): array
    {
        return collect(self::TYPES)->mapWithKeys(fn (array $definition, string $key): array => [$key => $definition['class']])->all();
    }

    /** @return array{class: class-string<Model>, label: string, slots: array<string, string>, multiple: array<int, string>} */
    public static function definition(string $type): array
    {
        abort_unless(isset(self::TYPES[$type]), 404);

        return self::TYPES[$type];
    }

    public static function assertSlot(string $type, string $slot): void
    {
        abort_unless(isset(self::definition($type)['slots'][$slot]), 422, 'Unsupported media slot.');
    }
}
