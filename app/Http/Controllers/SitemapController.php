<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Client;
use App\Models\Product;
use App\Models\Industry;
use App\Models\Capability;
use App\Models\WorkProcess;
use App\Models\Solution;
use App\Models\WebsiteSetting;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response { $root = rtrim(WebsiteSetting::query()->value('site_url') ?: url('/'), '/'); $clients = Client::query()->publiclyVisible()->where('robots_index', true)->orderBy('slug')->get(['slug', 'updated_at']); $pages = Page::query()->publiclyVisible()->where('robots_index', true)->orderBy('slug')->get(['slug', 'updated_at']); $products = Product::query()->publiclyVisible()->where('robots_index', true)->orderBy('slug')->get(['slug', 'updated_at']); $industries = Industry::query()->publiclyVisible()->where('robots_index', true)->orderBy('slug')->get(['slug', 'updated_at']); $capabilities = Capability::query()->publiclyVisible()->where('robots_index', true)->orderBy('slug')->get(['slug', 'updated_at']); $workProcesses = WorkProcess::query()->publiclyVisible()->where('robots_index', true)->orderBy('slug')->get(['slug', 'updated_at']); $solutions = Solution::query()->publiclyVisible()->where('robots_index', true)->orderBy('slug')->get(['slug', 'updated_at']); return response()->view('sitemap', compact('root', 'clients', 'pages', 'products', 'solutions', 'industries', 'capabilities', 'workProcesses'))->header('Content-Type', 'application/xml'); }
}
