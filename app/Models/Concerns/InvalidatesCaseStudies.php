<?php
namespace App\Models\Concerns; use App\Domain\CaseStudies\CaseStudyCache; trait InvalidatesCaseStudies { protected static function bootInvalidatesCaseStudies():void{static::saved(fn($m)=>CaseStudyCache::forget($m->id));static::deleted(fn($m)=>CaseStudyCache::forget($m->id));} }
