<?php

namespace App\Models\Concerns;

use App\Domain\Homepage\HomepageCache;
use App\Models\HomepageSetting;

trait InvalidatesHomepage
{
    protected static function bootInvalidatesHomepage(): void
    {
        $invalidate = function (): void {
            if (HomepageSetting::query()->where('singleton_key', 1)->where('status', 'published')->exists()) {
                HomepageCache::forget();
            }
        };

        static::saved($invalidate);
        static::deleted($invalidate);
    }
}
