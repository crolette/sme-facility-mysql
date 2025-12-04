<?php

namespace App\Http\Controllers\Settings;

use Inertia\Inertia;
use Inertia\Response;
use App\Models\Tenants\Site;
use App\Models\Tenants\User;
use App\Models\Tenants\Company;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

class CompanyProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function show(): Response
    {
        $tenant = tenant();

        if ($tenant) {
            $limits = Cache::remember(
                "tenant:{$tenant->id}:limits",
                now()->addDay(),
                fn() => $this->loadLimits($tenant)
            );
        }


        return Inertia::render('settings/company', [
            'item' => Company::first(),
            'billingPortal' => tenant()->billingPortalUrl(route('tenant.company.show')),
            'limits' => $limits,
            'usage' => ['current_sites_count' => Site::count(), 'current_users_count' => User::withoutRole('Super Admin')->where('can_login', true)->count()]
        ]);
    }
}
