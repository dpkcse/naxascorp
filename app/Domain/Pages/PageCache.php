<?php

namespace App\Domain\Pages;

use Illuminate\Support\Facades\Cache;
use Throwable;

final class PageCache
{
    public static function key(int $pageId, string $slug): string { return "public.pages.v1.{$pageId}.{$slug}"; }
    public static function forget(int $pageId): void
    {
        try { Cache::put("public.pages.version.{$pageId}", hrtime(true)); } catch (Throwable) { }
    }
    public static function version(int $pageId): int
    {
        try { return (int) Cache::rememberForever("public.pages.version.{$pageId}", fn (): int => hrtime(true)); } catch (Throwable) { return hrtime(true); }
    }
}
