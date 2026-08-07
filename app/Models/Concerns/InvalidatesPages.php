<?php

namespace App\Models\Concerns;

use App\Domain\Pages\PageCache;
use App\Domain\Industries\IndustryCache;
use App\Domain\PublicChrome\PublicChromeCache;

trait InvalidatesPages
{
    protected static function bootInvalidatesPages(): void
    {
        static::saved(function ($model): void { PageCache::forget((int) ($model->page_id ?: $model->id)); PublicChromeCache::forgetAll(); IndustryCache::forgetRelated('industry_page_relations','page_id',(int) ($model->page_id ?: $model->id)); });
        static::deleted(function ($model): void { PageCache::forget((int) ($model->page_id ?: $model->id)); PublicChromeCache::forgetAll(); IndustryCache::forgetRelated('industry_page_relations','page_id',(int) ($model->page_id ?: $model->id)); });
    }
}
