<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Administration\SystemHealth;
use App\Domain\Installation\EntitlementRevalidator;
use App\Domain\Installation\WebsiteSettings;
use App\Http\Controllers\Controller;
use App\Models\LicenseState;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Throwable;

class DashboardController extends Controller
{
    public function __invoke(Request $request, WebsiteSettings $settings, EntitlementRevalidator $entitlements, SystemHealth $health): View
    {
        $license = null;
        $licenseStatus = 'Verification required';

        try {
            $license = $entitlements->validate($request->getHost());
            $licenseStatus = 'Active';
        } catch (Throwable) {
            $license = LicenseState::query()->where('product_slug', config('naxas-license.product'))->first();
        }

        return view('dashboard', [
            'administrator' => $request->user(),
            'website' => $settings->current(),
            'license' => $license,
            'licenseStatus' => $licenseStatus,
            'healthChecks' => $health->checks(),
        ]);
    }
}
