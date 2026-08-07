<?php

namespace App\Models;

use App\Models\Concerns\InvalidatesHomepage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomepageItem extends Model
{
    use InvalidatesHomepage;

    protected $fillable = ['solution_id', 'product_id', 'industry_id', 'capability_id', 'item_type', 'title', 'eyebrow', 'description', 'secondary_text', 'highlighted_text', 'icon', 'badge', 'image_path', 'mobile_image_path', 'image_alt', 'primary_cta_label', 'primary_cta_url', 'secondary_cta_label', 'secondary_cta_url', 'organization', 'value', 'prefix', 'suffix', 'rating', 'published_on', 'display_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'published_on' => 'date'];
    }

    public function solution(): BelongsTo
    {
        return $this->belongsTo(Solution::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function industry(): BelongsTo
    {
        return $this->belongsTo(Industry::class);
    }

    public function capability(): BelongsTo
    {
        return $this->belongsTo(Capability::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(HomepageSection::class, 'homepage_section_id');
    }
}
