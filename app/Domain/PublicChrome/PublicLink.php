<?php
namespace App\Domain\PublicChrome;
use Illuminate\Support\Facades\Route;
class PublicLink
{
    public const ROUTES = ['home','clients.index','testimonials.index'];
    public static function isSafeUrl(?string $url, bool $relative = true): bool
    {
        if (! is_string($url) || trim($url) === '' || preg_match('/[\x00-\x1F\x7F]/', $url)) { return false; }
        if ($relative && str_starts_with($url, '/') && ! str_starts_with($url, '//')) { return ! str_contains($url, '..'); }
        $parts = parse_url($url);
        return is_array($parts) && in_array(strtolower($parts['scheme'] ?? ''), ['http','https'], true) && isset($parts['host']) && ! isset($parts['user']) && ! isset($parts['pass']);
    }
    public static function isApprovedRoute(?string $name): bool { return is_string($name) && in_array($name, self::ROUTES, true) && Route::has($name); }
    public static function href(string $type, ?string $route, ?string $url): ?string { return match ($type) {'route' => self::isApprovedRoute($route) ? route($route) : null, 'url' => self::isSafeUrl($url) ? $url : null, default => null}; }
    public static function isExternal(?string $url): bool { return is_string($url) && preg_match('#^https?://#i', $url) === 1; }
}
