<?php

namespace App\Models;

use App\Models\Concerns\InvalidatesPages;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Page extends Model
{
    use HasFactory, InvalidatesPages;

    protected $fillable = ['parent_id', 'title', 'slug', 'eyebrow', 'summary', 'body', 'template', 'status', 'featured_image_path', 'featured_image_alt', 'show_breadcrumb', 'show_title', 'display_order', 'meta_title', 'meta_description', 'canonical_url', 'og_title', 'og_description', 'og_image_path', 'robots_index', 'robots_follow', 'published_at', 'scheduled_for', 'archived_at', 'created_by', 'updated_by'];

    protected function casts(): array
    {
        return ['show_breadcrumb' => 'boolean', 'show_title' => 'boolean', 'robots_index' => 'boolean', 'robots_follow' => 'boolean', 'published_at' => 'datetime', 'scheduled_for' => 'datetime', 'archived_at' => 'datetime'];
    }

    public function parent(): BelongsTo { return $this->belongsTo(self::class, 'parent_id'); }
    public function children(): HasMany { return $this->hasMany(self::class, 'parent_id')->orderBy('display_order')->orderBy('id'); }
    public function sections(): HasMany { return $this->hasMany(PageSection::class)->orderBy('display_order')->orderBy('id'); }
    public function navigationItems(): HasMany { return $this->hasMany(NavigationItem::class); }
    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query->where(function (Builder $query): void {
            $query->where(fn (Builder $published) => $published->where('status', 'published')->whereNotNull('published_at')->where('published_at', '<=', now()))
                ->orWhere(fn (Builder $scheduled) => $scheduled->where('status', 'scheduled')->whereNotNull('scheduled_for')->where('scheduled_for', '<=', now()));
        })->whereNull('archived_at');
    }

    public function isPubliclyVisible(): bool
    {
        return $this->archived_at === null && (($this->status === 'published' && $this->published_at?->lte(now())) || ($this->status === 'scheduled' && $this->scheduled_for?->lte(now())));
    }
}
