<?php

namespace App\Domain\Homepage;

use Illuminate\Support\Facades\Cache;
use Throwable;

final class HomepageCache
{
    public const VERSION = 'public.homepage.version';
    public const PUBLISHED = 'public.homepage.published';

    public static function forget(): void
    {
        try {
            Cache::forget(self::PUBLISHED);
            Cache::increment(self::VERSION);
        } catch (Throwable) {
            // Cache failure must never prevent content management.
        }
    }
}
