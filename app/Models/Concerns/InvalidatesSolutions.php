<?php
namespace App\Models\Concerns;
use App\Domain\Solutions\SolutionCache; use App\Domain\Industries\IndustryCache;
trait InvalidatesSolutions { protected static function bootInvalidatesSolutions():void { $invalidate=function($model):void{SolutionCache::forget($model->solution_id??$model->id);IndustryCache::forgetRelated('industry_solution_relations','solution_id',(int)($model->solution_id??$model->id));};static::saved($invalidate);static::deleted($invalidate); } }
