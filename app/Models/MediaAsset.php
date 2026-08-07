<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class MediaAsset extends Model
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = ['uuid', 'media_collection_id', 'directory', 'filename', 'stored_name', 'extension', 'mime_type', 'size_bytes', 'width', 'height', 'aspect_ratio', 'alt_text', 'title', 'caption', 'credit', 'original_filename', 'checksum_sha256', 'media_type', 'status', 'uploaded_by'];

    protected $hidden = ['disk', 'directory', 'filename', 'stored_name', 'checksum_sha256', 'uploaded_by'];

    protected static function booted(): void
    {
        static::creating(function (MediaAsset $asset): void {
            $asset->uuid ??= (string) Str::uuid();
            $asset->disk = 'public';
        });
    }

    protected function casts(): array
    {
        return ['size_bytes' => 'integer', 'width' => 'integer', 'height' => 'integer', 'aspect_ratio' => 'float'];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function scopeSelectable(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function collection(): BelongsTo
    {
        return $this->belongsTo(MediaCollection::class, 'media_collection_id');
    }

    public function usages(): HasMany
    {
        return $this->hasMany(MediaUsage::class);
    }

    public function relativePath(): string
    {
        return trim($this->directory, '/').'/'.$this->stored_name;
    }
}
