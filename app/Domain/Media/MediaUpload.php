<?php

namespace App\Domain\Media;

use App\Models\MediaAsset;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class MediaUpload
{
    public const MAX_IMAGE_BYTES = 8 * 1024 * 1024;
    public const MAX_FAVICON_BYTES = 1024 * 1024;
    public const MAX_DIMENSION = 8000;
    public const MAX_PIXELS = 40_000_000;
    public const MIN_DIMENSION = 16;

    /** @var array<string, string> */
    private const IMAGE_MIMES = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'];

    /** @var array<string, string> */
    private const FAVICON_MIMES = ['png' => 'image/png', 'ico' => 'image/x-icon'];

    public function store(UploadedFile $file, string $mediaType, int $userId): MediaAsset
    {
        $metadata = $this->inspect($file, $mediaType);
        $uuid = (string) Str::uuid();
        $directory = 'media/'.now()->format('Y/m');
        $storedName = $uuid.'.'.$metadata['extension'];
        $path = $file->storeAs($directory, $storedName, 'public');

        if ($path === false) {
            throw ValidationException::withMessages(['file' => 'The media file could not be stored.']);
        }

        try {
            return MediaAsset::query()->create([...$metadata, 'uuid' => $uuid, 'directory' => $directory, 'filename' => $path, 'stored_name' => $storedName, 'media_type' => $mediaType, 'status' => MediaAsset::STATUS_ACTIVE, 'uploaded_by' => $userId]);
        } catch (\Throwable $exception) {
            Storage::disk('public')->delete($path);
            throw $exception;
        }
    }

    /** @return array{extension: string, mime_type: string, size_bytes: int, width: int, height: int, aspect_ratio: float, original_filename: string, checksum_sha256: string} */
    public function inspect(UploadedFile $file, string $mediaType): array
    {
        if (! $file->isValid() || ! in_array($mediaType, ['image', 'favicon'], true)) {
            throw ValidationException::withMessages(['file' => 'The upload is invalid.']);
        }

        $extension = strtolower($file->getClientOriginalExtension());
        $allowed = $mediaType === 'favicon' ? self::FAVICON_MIMES : self::IMAGE_MIMES;
        $size = (int) $file->getSize();
        $limit = $mediaType === 'favicon' ? self::MAX_FAVICON_BYTES : self::MAX_IMAGE_BYTES;
        $mime = strtolower((string) (new \finfo(FILEINFO_MIME_TYPE))->file($file->getRealPath()));
        if ($mime === 'image/vnd.microsoft.icon') {
            $mime = 'image/x-icon';
        }

        $dimensions = @getimagesize($file->getRealPath());
        if (! isset($allowed[$extension]) || $allowed[$extension] !== $mime || $size < 1 || $size > $limit || $dimensions === false) {
            throw ValidationException::withMessages(['file' => 'The file type, content, or size is not allowed.']);
        }

        [$width, $height] = [(int) $dimensions[0], (int) $dimensions[1]];
        if ($width < self::MIN_DIMENSION || $height < self::MIN_DIMENSION || $width > self::MAX_DIMENSION || $height > self::MAX_DIMENSION || ($width * $height) > self::MAX_PIXELS) {
            throw ValidationException::withMessages(['file' => 'Image dimensions must be 16–8000 pixels with at most 40 million pixels.']);
        }

        return ['extension' => $extension, 'mime_type' => $mime, 'size_bytes' => $size, 'width' => $width, 'height' => $height, 'aspect_ratio' => round($width / $height, 6), 'original_filename' => basename(str_replace('\\', '/', $file->getClientOriginalName())), 'checksum_sha256' => hash_file('sha256', $file->getRealPath())];
    }
}
