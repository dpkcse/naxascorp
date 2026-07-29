<?php

namespace App\Models;

use App\Models\Concerns\InvalidatesPages;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageSection extends Model
{
    use InvalidatesPages;
    protected $fillable = ['page_id', 'section_type', 'heading', 'eyebrow', 'body', 'image_path', 'image_alt', 'primary_cta_label', 'primary_cta_url', 'secondary_cta_label', 'secondary_cta_url', 'background_style', 'content_width', 'display_order', 'is_enabled'];
    protected function casts(): array { return ['is_enabled' => 'boolean']; }
    public function page(): BelongsTo { return $this->belongsTo(Page::class); }
}
