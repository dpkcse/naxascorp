<?php
namespace App\Models\Concerns; use App\Domain\Testimonials\TestimonialCache; trait InvalidatesTestimonials { protected static function bootInvalidatesTestimonials():void{static::saved(fn($model)=>TestimonialCache::forget($model->id));static::deleted(fn($model)=>TestimonialCache::forget($model->id));} }
