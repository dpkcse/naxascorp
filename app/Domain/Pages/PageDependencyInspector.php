<?php

namespace App\Domain\Pages;

use App\Models\Page;

final class PageDependencyInspector
{
    /** @return array{children:int,navigation:int,blocked:bool} */
    public function inspect(Page $page): array
    {
        $children = $page->children()->where('status', '!=', 'archived')->count(); $navigation = $page->navigationItems()->count();
        return ['children' => $children, 'navigation' => $navigation, 'blocked' => $children > 0 || $navigation > 0];
    }
}
