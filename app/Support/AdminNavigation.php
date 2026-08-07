<?php

namespace App\Support;

class AdminNavigation
{
    /** @return array<int, array{label: string, items: array<int, array{label: string, route: ?string}>}> */
    public static function groups(): array
    {
        return [
            ['label' => 'Overview', 'items' => [['label' => 'Dashboard', 'route' => 'dashboard']]],
            ['label' => 'Website', 'items' => [['label' => 'Homepage', 'route' => 'admin.homepage.edit'], ['label' => 'Pages', 'route' => 'admin.pages.index'],
                ['label' => 'Navigation', 'route' => 'admin.navigation.index'], ['label' => 'Header', 'route' => 'admin.header.edit'], ['label' => 'Footer', 'route' => 'admin.footer.edit'], ['label' => 'Branding', 'route' => 'admin.branding.edit'],
            ]],
            ['label' => 'Content', 'items' => array_merge([['label' => 'Solutions', 'route' => 'admin.solutions.index'], ['label' => 'Products', 'route' => 'admin.products.index'], ['label' => 'Industries', 'route' => 'admin.industries.index'], ['label' => 'Capabilities', 'route' => 'admin.capabilities.index'], ['label' => 'Work Process', 'route' => 'admin.work-processes.index'], ['label' => 'Clients', 'route' => 'admin.clients.index'], ['label' => 'Testimonials', 'route' => 'admin.testimonials.index'], ['label' => 'Statistics', 'route' => 'admin.statistics.index']])],
            ['label' => 'Insights', 'items' => [['label' => 'Insights / Articles', 'route' => 'admin.articles.index'], ['label' => 'Article Categories', 'route' => 'admin.articles.categories.index'], ['label' => 'Case Studies', 'route' => 'admin.case-studies.index'], ['label' => 'FAQs', 'route' => 'admin.faqs.index']]],
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
