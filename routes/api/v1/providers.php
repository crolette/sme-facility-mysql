<?php

use Stancl\Tenancy\Middleware\ScopeSessions;
use App\Http\Controllers\API\V1\APIProviderController;
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
    Route::patch('/{provider}', [APIProviderController::class, 'update'])->name('api.providers.update');
    Route::delete('/{provider}', [APIProviderController::class, 'destroy'])->name('api.providers.destroy');
});
