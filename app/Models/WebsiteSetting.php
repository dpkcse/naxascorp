<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebsiteSetting extends Model
{
    protected $fillable = ['site_name', 'legal_name', 'tagline', 'primary_email', 'primary_phone', 'country_code', 'timezone', 'default_locale', 'site_url'];

    protected $attributes = ['singleton_key' => 1];
}
