<?php
namespace App\Domain\Faqs; use App\Models\Faq; final class FaqDependencyInspector { public function inspect(Faq $faq):array{$homepage=$faq->homepageItems()->count();return ['blocked'=>$homepage>0,'homepage'=>$homepage];} }
