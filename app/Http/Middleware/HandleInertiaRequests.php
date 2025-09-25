<?php

namespace App\Http\Middleware;

use Closure;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;
use Illuminate\Http\Request;
use App\Models\Tenants\Ticket;
use App\Models\Tenants\Company;

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

        
        if(session()->missing('tenantName') || session()->missing('tenantLogo')){
            if(tenancy()->tenant) {
                $company = Company::first();
                session(['tenantName' => $company->name ?? config('app.name')]);
                session(['tenantLogo' => $company->logo ?? env('APP_LOGO')]);
            }
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'tenant' => [
                'name' => session('tenantName') ?? config('app.name'), 
                'logo' => session('tenantLogo')
            ],
            'auth' => [
                'user' => $request->user(),
            ],
            'flash' => ['message' => session('message'), 'type' => session('type')],
            'ziggy' => fn(): array => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'openTicketsCount' => tenancy()->tenant ? Ticket::where('status', 'open')->orWhere('status', 'ongoing')->count() : '',
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
