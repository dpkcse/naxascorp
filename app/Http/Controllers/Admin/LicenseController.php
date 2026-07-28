<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Installation\EntitlementRevalidator;
use App\Http\Controllers\Controller;
use App\Models\LicenseState;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Throwable;

class LicenseController extends Controller
{
    public function status(Request $request, EntitlementRevalidator $revalidator): View
    {
        $status = 'Verification required';
        try {
            $state = $revalidator->validate($request->getHost());
            $status = 'Active';
        } catch (Throwable) {
            $state = LicenseState::query()->where('product_slug', config('naxas-license.product'))->first();
        }

        return view('admin.license', compact('state', 'status'));
    }

    public function diagnostics(): View
    {
        $state = LicenseState::query()->where('product_slug', config('naxas-license.product'))->first();

        return view('admin.license-diagnostics', compact('state'));
    }
}
