<?php

namespace App\Http\Controllers\Settings;

use Inertia\Inertia;
use Inertia\Response;
use App\Services\TenantLimits;
use App\Models\Tenants\Company;
use App\Http\Controllers\Controller;


class CompanyProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function show(): Response
    {

        return Inertia::render('settings/company', [
            'item' => Company::first(),
            'billingPortal' => null,
            // 'billingPortal' => tenant()->billingPortalUrl(route('tenant.company.show')),
            'usage' => ['sites' => TenantLimits::getSitesUsage(), 'users' => TenantLimits::getUsersUsage(), 'storage' => TenantLimits::getStorageUsage()]
        ]);
    }
}
