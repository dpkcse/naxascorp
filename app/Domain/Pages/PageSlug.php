<?php

namespace App\Domain\Pages;

use Illuminate\Support\Str;

final class PageSlug
{
    public const RESERVED = ['install', 'login', 'logout', 'register', 'dashboard', 'settings', 'system', 'website', 'api', 'storage', 'build', 'vendor', 'password', 'email', 'verification', 'up', 'sitemap.xml', 'robots.txt', 'forgot-password', 'reset-password', 'confirm-password'];

    public static function normalize(string $value): string
    {
        if (str_contains($value, '/') || str_contains($value, '\\') || str_contains($value, '..')) { return ''; }
        return Str::slug(Str::lower(trim($value)));
    }

    public static function isReserved(string $slug): bool { return in_array($slug, self::RESERVED, true); }
}
