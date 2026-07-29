<?php
namespace App\Domain\PublicChrome;
class PublicAssetPath
{
    public const IMAGE_EXTENSIONS=['png','jpg','jpeg','webp','gif','ico'];
    public static function isSafe(?string $path, bool $favicon=false): bool
    {
        if ($path === null || $path === '') { return true; }
        if (str_starts_with($path, '/') || str_contains($path, '..') || str_contains($path, '\\') || preg_match('#^[a-z][a-z0-9+.-]*:#i', $path)) { return false; }
        $allowed=$favicon ? ['ico','png','jpg','jpeg','webp'] : self::IMAGE_EXTENSIONS;
        return preg_match('#^(?:storage/|images/|assets/)[A-Za-z0-9/_-]+\.([A-Za-z0-9]+)$#', $path, $matches) === 1 && in_array(strtolower($matches[1]), $allowed, true);
    }
}
