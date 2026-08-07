<?php
namespace App\Models\Concerns; use App\Domain\Faqs\FaqCache; trait InvalidatesFaqs { protected static function bootInvalidatesFaqs():void{static::saved(fn()=>FaqCache::forget());static::deleted(fn()=>FaqCache::forget());} }
