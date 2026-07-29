<?php
namespace App\Models;
use App\Models\Concerns\InvalidatesPublicChrome;
use Illuminate\Database\Eloquent\Relations\BelongsTo; use Illuminate\Database\Eloquent\Relations\HasMany; use Illuminate\Database\Eloquent\Model;
class FooterColumn extends Model { use InvalidatesPublicChrome; protected $fillable=['footer_setting_id','title','display_order','is_active']; protected function casts():array{return ['is_active'=>'boolean'];} public function footerSetting():BelongsTo{return $this->belongsTo(FooterSetting::class);} public function links():HasMany{return $this->hasMany(FooterLink::class)->orderBy('display_order')->orderBy('id');} }
