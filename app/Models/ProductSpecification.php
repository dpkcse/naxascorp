<?php
namespace App\Models; use App\Models\Concerns\InvalidatesProducts; use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\Relations\BelongsTo;
class ProductSpecification extends Model {use InvalidatesProducts; protected $fillable=['product_id','label','value','group_name','display_order','is_active'];protected function casts():array{return ['is_active'=>'boolean'];}public function product():BelongsTo{return $this->belongsTo(Product::class);}}
