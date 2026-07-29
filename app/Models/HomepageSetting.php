<?php

namespace App\Models;

use App\Models\Concerns\InvalidatesHomepage;
use Illuminate\Database\Eloquent\Model;

class HomepageSetting extends Model
{
    use InvalidatesHomepage;

    protected $fillable = ['eyebrow', 'title', 'highlighted_text', 'description', 'primary_cta_label', 'primary_cta_url', 'secondary_cta_label', 'secondary_cta_url', 'og_image_path', 'status', 'published_at'];

    protected $attributes = ['singleton_key' => 1, 'status' => 'draft'];

    protected function casts(): array
    {
        return ['published_at' => 'datetime'];
    }
}
