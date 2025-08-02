<?php

use Stancl\Tenancy\Middleware\ScopeSessions;
use App\Http\Controllers\API\V1\APIProviderController;
use App\Http\Controllers\API\V1\APIRemoveProviderLogoController;
use App\Http\Controllers\API\V1\APIUploadProviderLogoController;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;


Route::middleware([
    'web',
    InitializeTenancyBySubdomain::class,
    ScopeSessions::class,
    PreventAccessFromCentralDomains::class,
    'auth:tenant'
])->prefix('/v1/providers')->group(function () {

    Route::post('/', [APIProviderController::class, 'store'])->name('api.providers.store');
    Route::get('/{provider}', [APIProviderController::class, 'show'])->name('api.providers.show');
    Route::patch('/{provider}', [APIProviderController::class, 'update'])->name('api.providers.update');
    Route::patch('/{provider}/password', [APIProviderController::class, 'updatePassword'])->name('api.providers.update-password');
    Route::delete('/{provider}', [APIProviderController::class, 'destroy'])->name('api.providers.destroy');
    Route::post('/{provider}/logo', [APIUploadProviderLogoController::class, 'store'])->name('api.providers.logo.store');
    Route::delete('/{provider}/logo', [APIRemoveProviderLogoController::class, 'destroy'])->name('api.providers.logo.destroy');
});
