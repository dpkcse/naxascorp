<?php

namespace App\Models\Concerns;

use App\Domain\Pages\PageCache;
use App\Domain\PublicChrome\PublicChromeCache;

trait InvalidatesPages
{
    protected static function bootInvalidatesPages(): void
    {
        static::saved(function ($model): void { PageCache::forget((int) ($model->page_id ?: $model->id)); PublicChromeCache::forgetAll(); });
        static::deleted(function ($model): void { PageCache::forget((int) ($model->page_id ?: $model->id)); PublicChromeCache::forgetAll(); });
    }
}
