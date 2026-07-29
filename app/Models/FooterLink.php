<?php
namespace App\Models;
use App\Models\Concerns\InvalidatesPublicChrome;
use Illuminate\Database\Eloquent\Relations\BelongsTo; use Illuminate\Database\Eloquent\Model;
class FooterLink extends Model { use InvalidatesPublicChrome; protected $fillable=['footer_column_id','label','link_type','route_name','url','target','display_order','is_active']; protected function casts():array{return ['is_active'=>'boolean'];} public function column():BelongsTo{return $this->belongsTo(FooterColumn::class,'footer_column_id');} }
