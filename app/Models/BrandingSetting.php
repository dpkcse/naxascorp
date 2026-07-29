<?php
namespace App\Models;
use App\Models\Concerns\InvalidatesPublicChrome;
use Illuminate\Database\Eloquent\Model;
class BrandingSetting extends Model { use InvalidatesPublicChrome; protected $fillable=['site_logo_path','site_logo_dark_path','favicon_path','logo_alt_text','brand_mark_text','primary_color','secondary_color','accent_color','show_product_attribution']; protected $attributes=['singleton_key'=>1,'show_product_attribution'=>true]; protected function casts(): array { return ['show_product_attribution'=>'boolean']; } }
