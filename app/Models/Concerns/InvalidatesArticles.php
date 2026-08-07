<?php
namespace App\Models\Concerns; use App\Domain\Articles\ArticleCache; trait InvalidatesArticles { protected static function bootInvalidatesArticles():void{static::saved(fn($m)=>ArticleCache::forget($m->id));static::deleted(fn($m)=>ArticleCache::forget($m->id));} }
