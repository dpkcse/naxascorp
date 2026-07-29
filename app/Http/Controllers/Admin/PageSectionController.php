<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Pages\PageSectionRegistry;
use App\Http\Controllers\Controller;
use App\Http\Requests\Pages\SaveSectionRequest;
use App\Models\Page;
use App\Models\PageSection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PageSectionController extends Controller
{
    public function store(SaveSectionRequest $request, Page $page): RedirectResponse
    {
        if ($page->sections()->count() >= PageSectionRegistry::MAXIMUM) { throw ValidationException::withMessages(['section_type' => 'A page may contain at most 20 sections.']); }
        $page->sections()->create($request->validated() + ['display_order' => ((int) $page->sections()->max('display_order')) + 1]); return back()->with('status', 'Section added.');
    }
    public function update(SaveSectionRequest $request, Page $page, PageSection $section): RedirectResponse { $this->guard($page, $section); $section->update($request->validated()); return back()->with('status', 'Section updated.'); }
    public function destroy(Page $page, PageSection $section): RedirectResponse { $this->guard($page, $section); DB::transaction(function () use ($page, $section): void { $section->delete(); $page->sections()->get()->each(fn (PageSection $item, int $index) => $item->updateQuietly(['display_order' => $index + 1])); }); return back()->with('status', 'Section removed.'); }
    public function move(Page $page, PageSection $section, \Illuminate\Http\Request $request): RedirectResponse { $this->guard($page, $section); $direction = $request->validate(['direction' => ['required', 'in:up,down']])['direction']; DB::transaction(function () use ($page, $section, $direction): void { $operator = $direction === 'up' ? '<' : '>'; $order = $direction === 'up' ? 'desc' : 'asc'; $swap = $page->sections()->where('display_order', $operator, $section->display_order)->orderBy('display_order', $order)->first(); if ($swap) { $current = $section->display_order; $section->update(['display_order' => $swap->display_order]); $swap->update(['display_order' => $current]); } }); return back()->with('status', 'Section order updated.'); }
    private function guard(Page $page, PageSection $section): void { abort_unless($section->page_id === $page->id, 404); }
}
