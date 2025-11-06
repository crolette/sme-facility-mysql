<?php

use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Models\Tenants\Provider;
use Illuminate\Support\Facades\Auth;
use Barryvdh\Debugbar\Facades\Debugbar;
use Stancl\Tenancy\Middleware\ScopeSessions;
use App\Http\Controllers\API\V1\APIProviderController;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use App\Http\Controllers\API\V1\APIRemoveProviderLogoController;
use App\Http\Controllers\API\V1\APIUploadProviderLogoController;

Route::middleware([
    'web',
    InitializeTenancyBySubdomain::class,
    ScopeSessions::class,
    PreventAccessFromCentralDomains::class,
    'auth:tenant'
])->prefix('/v1/providers')->group(function () {

    Route::get('/search', function (Request $request) {
        if (Auth::user()->cannot('viewAny', Provider::class))
            return ApiResponse::notAuthorized();

        $query  = Provider::select('id', 'name', 'category_type_id', 'email');

        Debugbar::info($request->query('users'));

        if ($request->query('q')) {
            $query->where(function ($subquery) use ($request) {
                $subquery->where('name', 'like', '%' . $request->query('q') . '%');
            });
        }

        if ($request->query('users') === '1' || $request->query('users') === 1) {
            Debugbar::info('QUERY USERS PROVIDERS');
            $query->with('users');
        }

        return ApiResponse::success($query->get());
    })->name('api.providers.search');



    Route::get('/{provider}/contracts', function (Provider $provider) {
        if (Auth::user()->cannot('view', $provider))
            return ApiResponse::notAuthorized();

        return ApiResponse::success($provider->contracts()->with('provider')->paginate());
    })->name('api.providers.contracts');

    Route::get('/{provider}/assets', function (Provider $provider) {
        if (Auth::user()->cannot('view', $provider))
            return ApiResponse::notAuthorized();

        return ApiResponse::success($provider->assets()->paginate());
    })->name('api.providers.assets');

    Route::get('/{provider}/locations', function (Provider $provider) {
        if (Auth::user()->cannot('view', $provider))
            return ApiResponse::notAuthorized();

        return ApiResponse::success($provider->locations()->paginate());
    })->name('api.providers.locations');

    Route::post('/', [APIProviderController::class, 'store'])->name('api.providers.store');
    Route::get('/{provider}', [APIProviderController::class, 'show'])->name('api.providers.show');
    Route::patch('/{provider}', [APIProviderController::class, 'update'])->name('api.providers.update');

    // Route::patch('/{provider}/password', [APIProviderController::class, 'updatePassword'])->name('api.providers.update-password');
    Route::delete('/{provider}', [APIProviderController::class, 'destroy'])->name('api.providers.destroy');
    Route::post('/{provider}/logo', [APIUploadProviderLogoController::class, 'store'])->name('api.providers.logo.store');
    Route::delete('/{provider}/logo', [APIRemoveProviderLogoController::class, 'destroy'])->name('api.providers.logo.destroy');
});
