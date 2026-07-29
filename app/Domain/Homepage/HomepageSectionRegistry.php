<?php

namespace App\Domain\Homepage;

final class HomepageSectionRegistry
{
    public const BACKGROUNDS = ['default', 'alternate', 'navy', 'gradient'];
    public const WIDTHS = ['standard', 'narrow', 'wide'];
    public const ICONS = ['building', 'check', 'globe', 'heart', 'light-bulb', 'shield', 'sparkles', 'star', 'users', 'wrench'];

    /** @return array<string, array{label:string, description:string, item_type:?string, maximum:int, default_enabled:bool, secondary_item_type:?string, secondary_maximum:int}> */
    public static function all(): array
    {
        return [
            'hero' => self::definition('Hero', 'Manual, accessible hero slides and optional tabs.', 'hero_slide', 6, true, 'hero_tab', 6),
            'trust_strip' => self::definition('Trust strip', 'Administrator-entered trust statements.', 'trust_item', 6),
            'about' => self::definition('About', 'Structured company introduction.', null, 0),
            'featured_solutions' => self::definition('Featured solutions', 'Homepage-only previews pending Phase 9.', 'solution', 8),
            'featured_products' => self::definition('Featured products', 'Homepage-only previews pending Phase 9.', 'product', 8),
            'capabilities' => self::definition('Capabilities', 'Homepage capability summaries.', 'capability', 12),
            'industries' => self::definition('Industries', 'Homepage-only industry previews.', 'industry', 12),
            'process' => self::definition('Work process', 'Ordered process steps.', 'process_step', 8),
            'clients' => self::definition('Clients', 'Legitimate, administrator-provided clients.', 'client', 24),
            'statistics' => self::definition('Statistics', 'Truthful statistics rendered exactly as entered.', 'statistic', 8),
            'testimonials' => self::definition('Testimonials', 'Homepage-only attributed testimonials.', 'testimonial', 12),
            'insights' => self::definition('Insights', 'Homepage-only previews with optional safe destinations.', 'insight', 6),
            'faq' => self::definition('FAQ', 'Plain-text accessible disclosures.', 'faq', 20),
            'bottom_cta' => self::definition('Bottom CTA', 'Structured call to action.', null, 0),
        ];
    }

    /** @return array{label:string, description:string, item_type:?string, maximum:int, default_enabled:bool, secondary_item_type:?string, secondary_maximum:int} */
    private static function definition(string $label, string $description, ?string $itemType, int $maximum, bool $enabled = false, ?string $secondaryItemType = null, int $secondaryMaximum = 0): array
    {
        return compact('label', 'description') + ['item_type' => $itemType, 'maximum' => $maximum, 'default_enabled' => $enabled, 'secondary_item_type' => $secondaryItemType, 'secondary_maximum' => $secondaryMaximum];
    }
}
