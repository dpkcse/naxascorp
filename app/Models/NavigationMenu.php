<?php
namespace App\Models;
use App\Models\Concerns\InvalidatesPublicChrome;
use Illuminate\Database\Eloquent\Relations\HasMany; use Illuminate\Database\Eloquent\Model;
class NavigationMenu extends Model { use InvalidatesPublicChrome; public const LOCATIONS=['primary','footer_company','footer_solutions','footer_resources','legal']; protected $fillable=['name','location','description','is_active']; protected function casts(): array{return ['is_active'=>'boolean'];} public function items(): HasMany{return $this->hasMany(NavigationItem::class);} }
