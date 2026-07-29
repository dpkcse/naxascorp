<?php
namespace App\Models;
use App\Models\Concerns\InvalidatesPublicChrome;
use Illuminate\Database\Eloquent\Relations\HasMany; use Illuminate\Database\Eloquent\Model;
class FooterSetting extends Model { use InvalidatesPublicChrome; protected $fillable=['short_description','show_contact_details','show_social_links','show_newsletter','newsletter_heading','newsletter_description','copyright_text','developed_by_text','developed_by_url','show_legal_links']; protected $attributes=['singleton_key'=>1,'show_contact_details'=>true,'show_social_links'=>true,'show_newsletter'=>false,'show_legal_links'=>true,'developed_by_text'=>'Developed by Naxas Innovations Limited']; protected function casts():array{return ['show_contact_details'=>'boolean','show_social_links'=>'boolean','show_newsletter'=>'boolean','show_legal_links'=>'boolean'];} public function columns():HasMany{return $this->hasMany(FooterColumn::class);} public function socialLinks():HasMany{return $this->hasMany(SocialLink::class);} }
