<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MediaCollection extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'display_order'];

    public function assets(): HasMany
    {
        return $this->hasMany(MediaAsset::class);
    }
}
