<?php

namespace App\Domain\Pages;

use App\Models\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class PageManager
{
    public function save(array $data, ?Page $page, int $userId): Page
    {
        return DB::transaction(function () use ($data, $page, $userId): Page {
            $page ??= new Page; $oldParent = $page->parent_id; $parent = isset($data['parent_id']) ? Page::find($data['parent_id']) : null;
            app(PageHierarchy::class)->validate($page, $parent); $data['parent_id'] = $parent?->id; $data['updated_by'] = $userId;
            if (! $page->exists) { $data['created_by'] = $userId; $data['status'] = 'draft'; $data['display_order'] = $this->nextOrder($parent?->id); }
            $page->fill($data)->save(); $this->normalize($oldParent); $this->normalize($page->parent_id); return $page;
        });
    }

    public function publish(Page $page): void { DB::transaction(fn () => $page->update(['status' => 'published', 'published_at' => now(), 'scheduled_for' => null, 'archived_at' => null])); }
    public function schedule(Page $page, string $utcDate): void { $page->update(['status' => 'scheduled', 'scheduled_for' => $utcDate, 'published_at' => null, 'archived_at' => null]); }
    public function unpublish(Page $page): void { $page->update(['status' => 'draft', 'published_at' => null, 'scheduled_for' => null, 'archived_at' => null]); }
    public function archive(Page $page): void
    {
        $dependencies = app(PageDependencyInspector::class)->inspect($page);
        if ($dependencies['blocked']) { throw ValidationException::withMessages(['page' => "Resolve {$dependencies['children']} active child page(s) and {$dependencies['navigation']} navigation reference(s) before archiving."]); }
        $page->update(['status' => 'archived', 'archived_at' => now(), 'published_at' => null, 'scheduled_for' => null]);
    }
    public function restore(Page $page): void { $page->update(['status' => 'draft', 'archived_at' => null, 'published_at' => null, 'scheduled_for' => null]); }
    public function duplicate(Page $page, int $userId): Page
    {
        return DB::transaction(function () use ($page, $userId): Page {
            $copy = $page->replicate(['status', 'published_at', 'scheduled_for', 'archived_at', 'created_by', 'updated_by']); $copy->title = $page->title.' (Copy)'; $copy->slug = $this->uniqueSlug($page->slug.'-copy'); $copy->status = 'draft'; $copy->published_at = $copy->scheduled_for = $copy->archived_at = null; $copy->created_by = $copy->updated_by = $userId; $copy->display_order = $this->nextOrder($page->parent_id); $copy->save();
            $page->sections()->orderBy('display_order')->each(fn ($section) => $copy->sections()->create($section->only($section->getFillable()))); return $copy;
        });
    }
    public function move(Page $page, string $direction): void
    {
        DB::transaction(function () use ($page, $direction): void { $operator = $direction === 'up' ? '<' : '>'; $order = $direction === 'up' ? 'desc' : 'asc'; $swap = Page::query()->where('parent_id', $page->parent_id)->where('display_order', $operator, $page->display_order)->orderBy('display_order', $order)->first(); if ($swap) { [$pageOrder, $swapOrder] = [$page->display_order, $swap->display_order]; $page->update(['display_order' => $swapOrder]); $swap->update(['display_order' => $pageOrder]); } $this->normalize($page->parent_id); });
    }
    public function uniqueSlug(string $base): string { $slug = PageSlug::normalize($base) ?: 'page'; $candidate = $slug; for ($suffix = 2; Page::where('slug', $candidate)->exists(); $suffix++) { $candidate = Str::limit($slug, 150, '').'-'.$suffix; } return $candidate; }
    private function nextOrder(?int $parentId): int { return (int) Page::query()->where('parent_id', $parentId)->max('display_order') + 1; }
    private function normalize(?int $parentId): void
    {
        Page::query()->where('parent_id', $parentId)->orderBy('display_order')->orderBy('id')->get()->each(function (Page $page, int $index): void {
            if ($page->display_order !== $index + 1) { $page->updateQuietly(['display_order' => $index + 1]); }
        });
    }
}
