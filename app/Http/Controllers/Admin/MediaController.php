<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Media\{MediaCacheInvalidator, MediaRegistry, MediaUpload, MediaUrl};
use App\Http\Controllers\Controller;
use App\Models\{MediaAsset, MediaCollection, MediaUsage};
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MediaController extends Controller
{
    public function index(Request $request, MediaUrl $urls): View|JsonResponse
    {
        $validated = $request->validate(['search' => ['nullable', 'string', 'max:100'], 'type' => ['nullable', Rule::in(['image', 'favicon'])], 'status' => ['nullable', Rule::in(['active', 'archived', 'all'])], 'collection' => ['nullable', 'integer', Rule::exists('media_collections', 'id')], 'sort' => ['nullable', Rule::in(['newest', 'oldest', 'title', 'size'])]]);
        $sort = $validated['sort'] ?? 'newest';
        $assets = MediaAsset::query()->with('collection')->withCount('usages')
            ->when($validated['search'] ?? null, fn ($query, $search) => $query->where(fn ($nested) => $nested->where('title', 'like', '%'.addcslashes($search, '%_\\').'%')->orWhere('original_filename', 'like', '%'.addcslashes($search, '%_\\').'%')))
            ->when($validated['type'] ?? null, fn ($query, $type) => $query->where('media_type', $type))
            ->when(($validated['status'] ?? 'active') !== 'all', fn ($query) => $query->where('status', $validated['status'] ?? 'active'))
            ->when($validated['collection'] ?? null, fn ($query, $collection) => $query->where('media_collection_id', $collection))
            ->when($sort === 'oldest', fn ($query) => $query->oldest())
            ->when($sort === 'title', fn ($query) => $query->orderByRaw('COALESCE(title, original_filename)'))
            ->when($sort === 'size', fn ($query) => $query->orderByDesc('size_bytes'))
            ->when($sort === 'newest', fn ($query) => $query->latest())
            ->paginate(24)->withQueryString();
        $assets->through(fn (MediaAsset $asset): array => ['uuid' => $asset->uuid, 'title' => $asset->title ?: $asset->original_filename, 'alt' => $asset->alt_text, 'url' => $urls->url($asset), 'width' => $asset->width, 'height' => $asset->height, 'size' => $asset->size_bytes, 'type' => $asset->media_type, 'status' => $asset->status, 'usages_count' => $asset->usages_count, 'created' => $asset->created_at?->toDateString()]);

        if ($request->expectsJson()) {
            return response()->json($assets);
        }

        return view('admin.media.index', ['assets' => $assets, 'collections' => MediaCollection::query()->orderBy('display_order')->orderBy('name')->get(), 'filters' => $validated]);
    }

    public function store(Request $request, MediaUpload $upload): RedirectResponse|JsonResponse
    {
        $validated = $request->validate(['file' => ['required', 'file'], 'media_type' => ['required', Rule::in(['image', 'favicon'])]]);
        $duplicate = hash_file('sha256', $validated['file']->getRealPath());
        if (MediaAsset::query()->where('checksum_sha256', $duplicate)->exists() && ! $request->boolean('allow_duplicate')) {
            return back()->withErrors(['file' => 'This exact file already exists. Reuse it, or explicitly allow a duplicate.']);
        }
        $asset = $upload->store($validated['file'], $validated['media_type'], (int) $request->user()->id);

        return $request->expectsJson() ? response()->json(['uuid' => $asset->uuid], 201) : back()->with('status', 'Media uploaded securely.');
    }

    public function show(MediaAsset $mediaAsset, MediaUrl $urls): View|JsonResponse
    {
        $mediaAsset->load(['collection', 'usages.mediable'])->loadCount('usages');
        $usages = $mediaAsset->usages->take(100)->map(function (MediaUsage $usage): array {
            $definition = MediaRegistry::definition($usage->mediable_type);
            return ['type' => $definition['label'], 'label' => $usage->mediable?->title ?? $usage->mediable?->name ?? $usage->mediable?->person_name ?? '#'.$usage->mediable_id, 'slot' => $definition['slots'][$usage->slot] ?? $usage->slot, 'order' => $usage->display_order];
        });
        $data = ['asset' => ['uuid' => $mediaAsset->uuid, 'title' => $mediaAsset->title, 'alt_text' => $mediaAsset->alt_text, 'caption' => $mediaAsset->caption, 'credit' => $mediaAsset->credit, 'url' => $urls->url($mediaAsset), 'width' => $mediaAsset->width, 'height' => $mediaAsset->height, 'mime_type' => $mediaAsset->mime_type, 'size_bytes' => $mediaAsset->size_bytes, 'original_filename' => $mediaAsset->original_filename, 'status' => $mediaAsset->status], 'usages' => $usages];

        return request()->expectsJson() ? response()->json($data) : view('admin.media.show', $data);
    }

    public function update(Request $request, MediaAsset $mediaAsset, MediaCacheInvalidator $cache): RedirectResponse
    {
        $mediaAsset->update($request->validate(['title' => ['nullable', 'string', 'max:180'], 'alt_text' => ['nullable', 'string', 'max:180'], 'caption' => ['nullable', 'string', 'max:2000'], 'credit' => ['nullable', 'string', 'max:180'], 'media_collection_id' => ['nullable', Rule::exists('media_collections', 'id')]]));
        $cache->invalidate($mediaAsset);
        return back()->with('status', 'Media metadata updated.');
    }

    public function replace(Request $request, MediaAsset $mediaAsset, MediaUpload $upload, MediaCacheInvalidator $cache): RedirectResponse
    {
        $validated = $request->validate(['file' => ['required', 'file']]);
        $replacement = $upload->store($validated['file'], $mediaAsset->media_type, (int) $request->user()->id);
        $oldPath = $mediaAsset->relativePath();
        $attributes = $replacement->only(['directory', 'filename', 'stored_name', 'extension', 'mime_type', 'size_bytes', 'width', 'height', 'aspect_ratio', 'original_filename', 'checksum_sha256']);
        $mediaAsset->update($attributes);
        $replacement->delete();
        Storage::disk('public')->delete($oldPath);
        $cache->invalidate($mediaAsset);
        return back()->with('status', 'File replaced. All existing usages now use the new file.');
    }

    public function archive(MediaAsset $mediaAsset): RedirectResponse { $mediaAsset->update(['status' => MediaAsset::STATUS_ARCHIVED]); return back()->with('status', 'Media archived; existing references remain available.'); }
    public function restore(MediaAsset $mediaAsset): RedirectResponse { $mediaAsset->update(['status' => MediaAsset::STATUS_ACTIVE]); return back()->with('status', 'Media restored.'); }

    public function destroy(Request $request, MediaAsset $mediaAsset): RedirectResponse
    {
        $request->validate(['confirm' => ['accepted']]);
        abort_if($mediaAsset->usages()->exists(), 409, 'Referenced media cannot be deleted. Archive it instead.');
        $path = $mediaAsset->relativePath();
        if (Storage::disk('public')->exists($path) && ! Storage::disk('public')->delete($path)) {
            return back()->withErrors(['media' => 'The physical file could not be deleted; its database record was retained.']);
        }
        $mediaAsset->delete();
        return redirect()->route('admin.media.index')->with('status', 'Unreferenced media deleted.');
    }
}
