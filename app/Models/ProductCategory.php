<?php
namespace App\Models; use App\Models\Concerns\InvalidatesProducts; use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\Relations\HasMany;
class ProductCategory extends Model {use InvalidatesProducts; protected $fillable=['name','slug','description','display_order','is_active'];protected function casts():array{return ['is_active'=>'boolean'];}public function products():HasMany{return $this->hasMany(Product::class);}}
