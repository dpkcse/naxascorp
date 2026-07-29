<?php

namespace App\Http\Controllers;

use App\Domain\Installation\WebsiteSettings;
use App\Domain\PublicChrome\PublicChrome;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Throwable;

class PublicSiteController extends Controller
{
    public function __invoke(Request $request, WebsiteSettings $websiteSettings, PublicChrome $publicChrome): View
    {
        try {
            $settings = $websiteSettings->current();
        } catch (Throwable) {
            $settings = null;
        }

        $chrome = $publicChrome->data();

        $siteName = $settings?->site_name ?: 'Naxora CMS';
        $canonical = rtrim($settings?->site_url ?: $request->root(), '/').'/';

        return view('welcome', [
            'settings' => $settings,
            'siteName' => $siteName,
            'legalName' => $settings?->legal_name ?: 'Naxas Innovations Limited',
            'pageTitle' => $siteName.' — Premium Corporate Website CMS',
            'description' => $settings?->tagline ?: 'A premium corporate website foundation designed for business, enterprise, and government organizations.',
            'canonical' => $canonical,
            'navigation' => $chrome['navigation'],
            'chrome' => $chrome,
        ]);
    }
}
