<?php
namespace App\Models;
use App\Models\Concerns\InvalidatesPublicChrome;
use Illuminate\Database\Eloquent\Relations\BelongsTo; use Illuminate\Database\Eloquent\Model;
class SocialLink extends Model { use InvalidatesPublicChrome; public const PLATFORMS=['facebook','linkedin','youtube','x','instagram','github','whatsapp','other']; public const ICONS=['facebook','linkedin','youtube','x','instagram','github','whatsapp','link']; protected $fillable=['footer_setting_id','platform','label','url','icon','display_order','is_active']; protected function casts():array{return ['is_active'=>'boolean'];} public function footerSetting():BelongsTo{return $this->belongsTo(FooterSetting::class);} }
