<?php

namespace App\Domain\Pages;

use App\Models\Page;
use Illuminate\Validation\ValidationException;

final class PageHierarchy
{
    public const MAX_DEPTH = 3;

    public function validate(Page $page, ?Page $parent): void
    {
        if (! $parent) { $ancestorDepth = 0; } else {
            if ($page->exists && $page->is($parent)) { $this->fail('A page cannot be its own parent.'); }
            if ($parent->status === 'archived') { $this->fail('An archived page cannot be a parent.'); }
            $ancestorIds = []; $cursor = $parent;
            for ($depth = 1; $cursor && $depth <= self::MAX_DEPTH; $depth++) {
                if ($page->exists && $cursor->is($page)) { $this->fail('A descendant cannot become the page parent.'); }
                if (in_array($cursor->id, $ancestorIds, true)) { $this->fail('The page hierarchy contains a cycle.'); }
                $ancestorIds[] = $cursor->id; $cursor = $cursor->parent()->first();
            }
            if ($cursor) { $this->fail('Pages support a maximum hierarchy depth of three.'); }
            $ancestorDepth = count($ancestorIds);
        }
        $descendantDepth = $page->exists ? $this->descendantDepth($page) : 0;
        if ($ancestorDepth + 1 + $descendantDepth > self::MAX_DEPTH) { $this->fail('This parent would move descendants beyond the maximum hierarchy depth.'); }
    }

    /** @return array<int, array{label:string,url:?string}> */
    public function breadcrumbs(Page $page): array
    {
        $items = []; $cursor = $page->parent;
        for ($depth = 0; $cursor && $depth < self::MAX_DEPTH - 1; $depth++) {
            array_unshift($items, ['label' => $cursor->title, 'url' => $cursor->newQuery()->publiclyVisible()->whereKey($cursor->id)->exists() ? route('pages.show', $cursor->slug) : null]);
            $cursor = $cursor->parent;
        }
        $items[] = ['label' => $page->title, 'url' => null]; return $items;
    }

    private function descendantDepth(Page $page): int
    {
        $ids = [$page->id]; $maximum = 0;
        for ($depth = 1; $depth < self::MAX_DEPTH; $depth++) { $ids = Page::query()->whereIn('parent_id', $ids)->pluck('id')->all(); if ($ids === []) { break; } $maximum = $depth; }
        return $maximum;
    }
    private function fail(string $message): never { throw ValidationException::withMessages(['parent_id' => $message]); }
}
