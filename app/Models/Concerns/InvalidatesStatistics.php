<?php
namespace App\Models\Concerns; use App\Domain\Statistics\StatisticCache; trait InvalidatesStatistics { protected static function bootInvalidatesStatistics():void{static::saved(fn($model)=>StatisticCache::forget($model->id));static::deleted(fn($model)=>StatisticCache::forget($model->id));} }
