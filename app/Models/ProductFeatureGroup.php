<?php
namespace App\Models; use App\Models\Concerns\InvalidatesProducts; use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\Relations\HasMany;
class ProductFeatureGroup extends Model {use InvalidatesProducts; protected $fillable=['product_id','title','description','display_order','is_active'];protected function casts():array{return ['is_active'=>'boolean'];}public function features():HasMany{return $this->hasMany(ProductFeature::class)->orderBy('display_order');}}
