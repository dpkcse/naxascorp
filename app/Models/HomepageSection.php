<?php

namespace App\Models;

use App\Models\Concerns\InvalidatesHomepage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HomepageSection extends Model
{
    use InvalidatesHomepage;

    protected $fillable = ['section_key', 'work_process_id', 'display_order', 'is_enabled', 'eyebrow', 'heading', 'description', 'secondary_description', 'image_path', 'image_alt', 'primary_cta_label', 'primary_cta_url', 'secondary_cta_label', 'secondary_cta_url', 'background_style', 'content_width'];

    protected function casts(): array
    {
        return ['is_enabled' => 'boolean'];
    }

    public function workProcess(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(WorkProcess::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(HomepageItem::class);
    }
}
