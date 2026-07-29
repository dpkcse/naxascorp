<?php
namespace App\Domain\Solutions;
use App\Models\Solution;
final class SolutionDependencyInspector { public function inspect(Solution $s):array{$navigation=$s->navigationItems()->where('is_active',true)->count();$homepage=$s->homepageItems()->where('is_active',true)->count();$relations=Solution::whereHas('relatedSolutions',fn($q)=>$q->whereKey($s))->where('status','!=','archived')->count();return ['navigation'=>$navigation,'homepage'=>$homepage,'relations'=>$relations,'children'=>$s->features()->count()+$s->benefits()->count()+$s->capabilities()->count()+$s->processSteps()->count()+$s->useCases()->count(),'blocked'=>$navigation+$homepage+$relations>0];} }
