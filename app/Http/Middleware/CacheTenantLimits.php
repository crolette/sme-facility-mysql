<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Services\TenantLimits;
use Illuminate\Support\Facades\Cache;


class CacheTenantLimits
{
    public function handle($request, $next)
    {
        $tenant = tenant();

        if ($tenant) {
            Cache::remember(
                "tenant:{$tenant->id}:limits",
                now()->addDay(),
                fn() => TenantLimits::loadLimitsFromDatabase($tenant)
            );
        }

        return $next($request);
    }

    // private function loadLimits(Tenant $tenant)
    // {
    //     return [
    //         'subscription_status' => $tenant->active_subscription?->stripe_status ?? 'inactive',
    //         "max_sites" => $tenant->max_sites,
    //         "max_users" => $tenant->max_users,
    //         "max_storage_gb" => $tenant->max_storage_gb,
    //         "has_statistics" => $tenant->has_statistics,
    //     ];
    // }
}
