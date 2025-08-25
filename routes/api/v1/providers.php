<?php

use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Models\Tenants\Provider;
use Stancl\Tenancy\Middleware\ScopeSessions;
use App\Http\Controllers\API\V1\APIProviderController;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use App\Http\Controllers\API\V1\APIRemoveProviderLogoController;
use App\Http\Controllers\API\V1\APIUploadProviderLogoController;
use Barryvdh\Debugbar\Facades\Debugbar;

Route::middleware([
    'web',
    InitializeTenancyBySubdomain::class,
    ScopeSessions::class,
    PreventAccessFromCentralDomains::class,
    'auth:tenant'
])->prefix('/v1/providers')->group(function () {

    Route::get('/search', function (Request $request) {
        $query  = Provider::select('id', 'name', 'category_type_id');

        if ($request->query('q')) {
            $query->where(function ($subquery) use ($request) {
                $subquery->where('name', 'like', '%' . $request->query('q') . '%');
            });
        }

        // $providers = $query->get();

        return ApiResponse::success($query->get());
    })->name('api.providers.search');

    Route::get('/{provider}/contracts', function (Provider $provider) {

        $provider->contracts;

        return ApiResponse::success($provider->contracts);
    })->name('api.providers.contracts');

    Route::post('/', [APIProviderController::class, 'store'])->name('api.providers.store');
    Route::get('/{provider}', [APIProviderController::class, 'show'])->name('api.providers.show');
    Route::patch('/{provider}', [APIProviderController::class, 'update'])->name('api.providers.update');
    Route::patch('/{provider}/password', [APIProviderController::class, 'updatePassword'])->name('api.providers.update-password');
    Route::delete('/{provider}', [APIProviderController::class, 'destroy'])->name('api.providers.destroy');
    Route::post('/{provider}/logo', [APIUploadProviderLogoController::class, 'store'])->name('api.providers.logo.store');
    Route::delete('/{provider}/logo', [APIRemoveProviderLogoController::class, 'destroy'])->name('api.providers.logo.destroy');
});
