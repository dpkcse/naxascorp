<?php
namespace App\Domain\Statistics; use App\Models\Statistic; final class StatisticDependencyInspector { public function inspect(Statistic $item):array{$count=$item->homepageItems()->where('is_active',true)->count();return ['homepage'=>$count,'blocked'=>$count>0];} }
