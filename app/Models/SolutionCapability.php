<?php
namespace App\Models;
use App\Models\Concerns\InvalidatesSolutions; use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\Relations\BelongsTo;
class SolutionCapability extends Model { use InvalidatesSolutions; protected $table='solution_capabilities'; protected $fillable=['solution_id','title','description','sector','icon','display_order','is_active']; protected function casts():array{return ['is_active'=>'boolean'];} public function solution():BelongsTo{return $this->belongsTo(Solution::class);} }
