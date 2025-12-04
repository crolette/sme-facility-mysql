<?php

namespace App\Http\Middleware;

use Closure;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;
use Illuminate\Http\Request;
use App\Models\Tenants\Ticket;
use App\Models\Tenants\Company;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Barryvdh\Debugbar\Facades\Debugbar;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }



    public function handle(Request $request, Closure $next)
    {
        // remove Inertia cache to avoid displaying raw JSON
        return parent::handle($request, $next)->setCache([
            'no_cache' => true,
            'no_store' => true,
            'must_revalidate' => true,
            'private' => true,
        ]);
    }


    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        // dd(Company::first()->logo_path);

        $tenant = tenancy()->tenant;
        if (session()->missing('tenantName') || session()->missing('tenantLogo')) {
            if ($tenant) {
                $company = Company::first();
                session(['tenantName' => $company->name ?? config('app.name')]);
                session(['tenantLogo' => $company->logo ?? env('APP_LOGO')]);
            }
        }

        if ($tenant) {
            $ticketsCount = $request->user()?->hasRole('Maintenance Manager') ? Ticket::where('status', 'open')->orWhere('status', 'ongoing')->forMaintenanceManager()->count() : Ticket::where('status', 'open')->orWhere('status', 'ongoing')->count();

            // TODO improve/refactor in service ? Est-ce malin de rappeler chaque fois le cache pour une seule info sur les stats ?
            $limits = Cache::get("tenant:{$tenant->id}:limits");
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'version' => env('APP_VERSION'),
            'tenant' => [
                'name' => session('tenantName') ?? config('app.name'),
                'logo' => session('tenantLogo') ?? env('APP_LOGO')
            ],
            'auth' => [
                'user' => $request->user(),
                'permissions' => tenancy()->tenant ? $request->user()?->getAllPermissions()?->pluck('name') ?? null : null,
            ],
            'has_statistics' => $limits['has_statistics'] ?? false,
            'flash' => ['message' => session('message'), 'type' => session('type')],
            'ziggy' => fn(): array => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'openTicketsCount' => $ticketsCount ?? null,
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'indexLayout' => ! $request->hasCookie('index_layout') || $request->cookie('index_layout') === 'table',
        ];
    }
}
