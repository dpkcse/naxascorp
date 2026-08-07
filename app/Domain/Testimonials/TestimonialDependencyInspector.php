<?php
namespace App\Domain\Testimonials; use App\Models\Testimonial; final class TestimonialDependencyInspector { public function inspect(Testimonial $item):array{$count=$item->homepageItems()->where('is_active',true)->count();return ['homepage'=>$count,'blocked'=>$count>0];} }
