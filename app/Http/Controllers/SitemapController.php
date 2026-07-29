<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\WebsiteSetting;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response { $root = rtrim(WebsiteSetting::query()->value('site_url') ?: url('/'), '/'); $pages = Page::query()->publiclyVisible()->where('robots_index', true)->orderBy('slug')->get(['slug', 'updated_at']); return response()->view('sitemap', compact('root', 'pages'))->header('Content-Type', 'application/xml'); }
}
