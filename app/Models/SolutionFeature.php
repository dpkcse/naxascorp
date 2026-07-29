<?php
namespace App\Models;
use App\Models\Concerns\InvalidatesSolutions; use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\Relations\BelongsTo;
class SolutionFeature extends Model { use InvalidatesSolutions; protected $table='solution_features'; protected $fillable=['solution_id','title','description','sector','icon','display_order','is_active']; protected function casts():array{return ['is_active'=>'boolean'];} public function solution():BelongsTo{return $this->belongsTo(Solution::class);} }
