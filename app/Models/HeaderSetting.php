<?php
namespace App\Models;
use App\Models\Concerns\InvalidatesPublicChrome;
use Illuminate\Database\Eloquent\Model;
class HeaderSetting extends Model { use InvalidatesPublicChrome; protected $fillable=['show_top_bar','sticky_header','show_site_name','show_language_switcher','show_search','show_primary_cta','primary_cta_label','primary_cta_url','primary_cta_target','primary_cta_style','top_bar_message','support_label','support_url','careers_label','careers_url']; protected $attributes=['singleton_key'=>1,'show_top_bar'=>true,'sticky_header'=>true,'show_site_name'=>true,'show_search'=>false,'show_language_switcher'=>false]; protected function casts(): array { return ['show_top_bar'=>'boolean','sticky_header'=>'boolean','show_site_name'=>'boolean','show_language_switcher'=>'boolean','show_search'=>'boolean','show_primary_cta'=>'boolean']; } }
