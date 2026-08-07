<?php
namespace App\Models\Concerns;
use App\Domain\Industries\IndustryCache;
trait InvalidatesIndustries { protected static function bootInvalidatesIndustries():void { static::saved(fn($model)=>IndustryCache::forget($model->industry_id??$model->id)); static::deleted(fn($model)=>IndustryCache::forget($model->industry_id??$model->id)); } }
