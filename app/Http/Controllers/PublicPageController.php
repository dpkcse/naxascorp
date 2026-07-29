<?php

namespace App\Http\Controllers;

use App\Domain\Pages\PageTemplateRegistry;
use App\Domain\Pages\PageViewData;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PublicPageController extends Controller
{
    public function __invoke(string $slug, PageViewData $pages): Response { $data = $pages->published($slug); abort_unless($data, 404); return $this->response($data, false); }
    /** @param array<string,mixed> $page */
    public function response(array $page, bool $preview): Response
    {
        $shared = app(PublicSiteController::class)->sharedViewData(request()); $siteRoot = rtrim($shared['settings']?->site_url ?: request()->root(), '/');
        $canonical = $preview ? $siteRoot : ($page['canonical_url'] ?: $siteRoot.'/'.rawurlencode($page['slug']));
        return response()->view('pages.show', ['page' => $page, 'preview' => $preview, 'templateView' => PageTemplateRegistry::view($page['template']), 'canonical' => $canonical] + $shared);
    }
}
