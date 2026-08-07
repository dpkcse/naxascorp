<?php
namespace App\Models; use App\Models\Concerns\InvalidatesProducts; use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\Relations\BelongsTo;
class ProductFeature extends Model {use InvalidatesProducts; protected $fillable=['product_feature_group_id','title','description','icon','display_order','is_active'];protected function casts():array{return ['is_active'=>'boolean'];}public function group():BelongsTo{return $this->belongsTo(ProductFeatureGroup::class,'product_feature_group_id');}}
