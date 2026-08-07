<?php

namespace App\Domain\Media;

use App\Models\MediaAsset;
use App\Domain\PublicChrome\PublicAssetPath;
use Illuminate\Support\Facades\Storage;

final class MediaUrl
{
    /** @return array{url: ?string, alt: ?string, width: ?int, height: ?int, aspect_ratio: ?float, mime_type: ?string} */
    public function data(?MediaAsset $asset, ?string $legacyPath = null, bool $absolute = false): array
    {
        $url = $this->url($asset, $absolute);
        if ($url === null && $legacyPath !== null) {
            $safePath = PublicAssetPath::isSafe($legacyPath) ? $legacyPath : null;
            $url = $safePath === null ? null : ($absolute ? url($safePath) : asset($safePath));
        }

        return ['url' => $url, 'alt' => $asset?->alt_text, 'width' => $asset?->width, 'height' => $asset?->height, 'aspect_ratio' => $asset?->aspect_ratio, 'mime_type' => $asset?->mime_type];
    }

    public function url(?MediaAsset $asset, bool $absolute = false): ?string
    {
        if ($asset === null || $asset->disk !== 'public' || ! $this->isSafePath($asset->relativePath()) || ! Storage::disk('public')->exists($asset->relativePath())) {
            return null;
        }

        $path = '/storage/'.$asset->relativePath();

        return $absolute ? url($path) : $path;
    }

    private function isSafePath(string $path): bool
    {
        return str_starts_with($path, 'media/') && ! str_contains($path, '..') && ! str_contains($path, '\\') && ! str_starts_with($path, '/');
    }
}
