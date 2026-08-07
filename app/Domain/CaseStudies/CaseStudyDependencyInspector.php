<?php
namespace App\Domain\CaseStudies; use App\Models\CaseStudy; final class CaseStudyDependencyInspector { public function inspect(CaseStudy $caseStudy):array{return ['blocked'=>false,'client_linked'=>$caseStudy->client_id!==null,'testimonial_linked'=>$caseStudy->testimonial_id!==null];} }
