<?php
namespace App\Models;
use App\Models\Concerns\InvalidatesSolutions; use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\Relations\HasMany;
class SolutionCategory extends Model { use InvalidatesSolutions; protected $fillable=['name','slug','description','display_order','is_active']; protected function casts():array{return ['is_active'=>'boolean'];} public function solutions():HasMany{return $this->hasMany(Solution::class);} }
