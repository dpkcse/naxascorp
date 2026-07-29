<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Pages\PageDependencyInspector;
use App\Domain\Pages\PageManager;
use App\Domain\Pages\PageTemplateRegistry;
use App\Domain\Pages\PageViewData;
use App\Http\Controllers\Controller;
use App\Http\Controllers\PublicPageController;
use App\Http\Requests\Pages\SavePageRequest;
use App\Models\Page;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class PageController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate(['search' => ['nullable', 'string', 'max:160'], 'status' => ['nullable', Rule::in(['draft', 'published', 'scheduled', 'archived'])], 'parent' => ['nullable', 'integer'], 'template' => ['nullable', Rule::in(array_keys(PageTemplateRegistry::all()))], 'archived' => ['nullable', Rule::in(['with', 'only', 'without'])], 'sort' => ['nullable', Rule::in(['title', 'updated_at', 'published_at', 'display_order'])], 'direction' => ['nullable', Rule::in(['asc', 'desc'])]]);
        $sort = $filters['sort'] ?? 'display_order'; $direction = $filters['direction'] ?? 'asc';
        $pages = Page::query()->with('parent')->withCount(['children', 'navigationItems'])->when($filters['search'] ?? null, fn ($query, $search) => $query->where(fn ($query) => $query->where('title', 'like', "%{$search}%")->orWhere('slug', 'like', "%{$search}%")))->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))->when(array_key_exists('parent', $filters), fn ($query) => $filters['parent'] === '' ? $query->whereNull('parent_id') : $query->where('parent_id', $filters['parent']))->when($filters['template'] ?? null, fn ($query, $template) => $query->where('template', $template))->when(($filters['archived'] ?? 'without') === 'without', fn ($query) => $query->where('status', '!=', 'archived'))->when(($filters['archived'] ?? null) === 'only', fn ($query) => $query->where('status', 'archived'))->orderBy($sort, $direction)->orderBy('id')->paginate(20)->withQueryString();
        return view('admin.pages.index', ['pages' => $pages, 'parents' => Page::query()->where('status', '!=', 'archived')->orderBy('title')->get(['id', 'title']), 'templates' => PageTemplateRegistry::all(), 'filters' => $filters]);
    }
    public function create(): View { return $this->editor(new Page(['template' => 'standard', 'show_breadcrumb' => true, 'show_title' => true, 'robots_index' => true, 'robots_follow' => true])); }
    public function store(SavePageRequest $request, PageManager $manager): RedirectResponse { $page = $manager->save($request->validated(), null, $request->user()->id); return redirect()->route('admin.pages.edit', $page)->with('status', 'Draft page created.'); }
    public function edit(Page $page): View { return $this->editor($page->load('sections')); }
    public function update(SavePageRequest $request, Page $page, PageManager $manager): RedirectResponse { $manager->save($request->validated(), $page, $request->user()->id); return back()->with('status', 'Page saved without changing publication status.'); }
    public function publish(Page $page, PageManager $manager): RedirectResponse { $manager->publish($page); return back()->with('status', 'Page published.'); }
    public function schedule(Request $request, Page $page, PageManager $manager): RedirectResponse
    {
        $value = $request->validate(['scheduled_for' => ['required', 'date', 'after:now']])['scheduled_for']; $timezone = \App\Models\WebsiteSetting::query()->value('timezone') ?: 'UTC'; $utc = Carbon::parse($value, $timezone)->utc()->toDateTimeString(); $manager->schedule($page, $utc); return back()->with('status', 'Page scheduled.');
    }
    public function unpublish(Page $page, PageManager $manager): RedirectResponse { $manager->unpublish($page); return back()->with('status', 'Page returned to draft.'); }
    public function archive(Page $page, PageManager $manager): RedirectResponse { $manager->archive($page); return back()->with('status', 'Page archived.'); }
    public function restore(Page $page, PageManager $manager): RedirectResponse { $manager->restore($page); return back()->with('status', 'Page restored as a draft.'); }
    public function duplicate(Request $request, Page $page, PageManager $manager): RedirectResponse { $copy = $manager->duplicate($page, $request->user()->id); return redirect()->route('admin.pages.edit', $copy)->with('status', 'Draft duplicate created.'); }
    public function move(Request $request, Page $page, PageManager $manager): RedirectResponse { $direction = (string) $request->route('direction'); abort_unless(in_array($direction, ['up', 'down'], true), 404); $manager->move($page, $direction); return back()->with('status', 'Page order updated.'); }
    public function preview(Page $page, PageViewData $viewData, PublicPageController $controller): Response { return $controller->response($viewData->preview($page), true)->header('Cache-Control', 'no-store, private, max-age=0')->header('X-Robots-Tag', 'noindex, nofollow'); }
    private function editor(Page $page): View { return view('admin.pages.editor', ['page' => $page, 'parents' => Page::query()->where('status', '!=', 'archived')->whereKeyNot($page->id)->orderBy('title')->get(), 'templates' => PageTemplateRegistry::all(), 'dependencies' => $page->exists ? app(PageDependencyInspector::class)->inspect($page) : ['children' => 0, 'navigation' => 0, 'blocked' => false]]); }
}
