<?php
namespace App\Models\Concerns;
use App\Domain\Solutions\SolutionCache;
trait InvalidatesSolutions { protected static function bootInvalidatesSolutions():void { static::saved(fn($model)=>SolutionCache::forget($model->solution_id??$model->id)); static::deleted(fn($model)=>SolutionCache::forget($model->solution_id??$model->id)); } }
