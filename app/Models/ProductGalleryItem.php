<?php
namespace App\Models; use App\Models\Concerns\InvalidatesProducts; use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\Relations\BelongsTo;
class ProductGalleryItem extends Model {use InvalidatesProducts; protected $fillable=['product_id','image_path','image_alt','caption','display_order','is_active'];protected function casts():array{return ['is_active'=>'boolean'];}public function product():BelongsTo{return $this->belongsTo(Product::class);}}
