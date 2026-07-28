<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstallationChoice extends Model
{
    protected $fillable = ['demo_content_selected', 'demo_content_status', 'demo_content_completed_at'];

    protected $attributes = ['singleton_key' => 1];

    protected function casts(): array
    {
        return ['demo_content_selected' => 'boolean', 'demo_content_completed_at' => 'datetime'];
    }
}
