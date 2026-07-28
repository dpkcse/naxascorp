<?php

namespace App\Support;

class AdminNavigation
{
    /** @return array<int, array{label: string, items: array<int, array{label: string, route: ?string}>}> */
    public static function groups(): array
    {
        return [
            ['label' => 'Overview', 'items' => [['label' => 'Dashboard', 'route' => 'dashboard']]],
            ['label' => 'Website', 'items' => self::disabled(['Homepage', 'Pages', 'Navigation', 'Header', 'Footer', 'Branding'])],
            ['label' => 'Content', 'items' => self::disabled(['Solutions', 'Products', 'Industries', 'Capabilities', 'Work Process', 'Clients', 'Testimonials', 'Statistics'])],
            ['label' => 'Insights', 'items' => self::disabled(['Articles', 'Categories', 'Case Studies', 'FAQs'])],
            ['label' => 'Communication', 'items' => self::disabled(['Demo Requests', 'Contact Enquiries', 'Career Applications'])],
            ['label' => 'Media', 'items' => self::disabled(['Media Library'])],
            ['label' => 'SEO', 'items' => self::disabled(['General SEO', 'Redirects', 'Sitemap'])],
            ['label' => 'System', 'items' => [
                ['label' => 'Website Settings', 'route' => 'settings.profile'],
                ['label' => 'License', 'route' => 'license.status'],
                ['label' => 'System Health', 'route' => 'dashboard'],
                ['label' => 'Profile', 'route' => 'settings.profile'],
                ['label' => 'Audit Logs', 'route' => null],
            ]],
        ];
    }

    /** @param array<int, string> $labels
     *  @return array<int, array{label: string, route: null}>
     */
    private static function disabled(array $labels): array
    {
        return array_map(fn (string $label): array => ['label' => $label, 'route' => null], $labels);
    }
}
