<?php
namespace App\Models;
use App\Models\Concerns\InvalidatesIndustries; use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\Relations\HasMany;
class IndustryCategory extends Model { use InvalidatesIndustries; protected $fillable=['name','slug','description','display_order','is_active']; protected function casts():array{return ['is_active'=>'boolean'];} public function industries():HasMany{return $this->hasMany(Industry::class);} }
