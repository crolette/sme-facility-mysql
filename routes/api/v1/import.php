<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\V1\ApiImportUsersController;
use App\Http\Controllers\API\V1\ApiImportAssetsController;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;
use App\Http\Controllers\API\V1\ApiImportProvidersController;

Route::middleware([
    'web',
    InitializeTenancyBySubdomain::class,
    \Stancl\Tenancy\Middleware\ScopeSessions::class,
    \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
    'auth:tenant'
])->prefix('/v1/import')->group(
    function () {

        Route::post('/assets', [ApiImportAssetsController::class, 'store'])->name('api.tenant.import.assets');
        Route::post('/providers', [ApiImportProvidersController::class, 'store'])->name('api.tenant.import.providers');
        Route::post('/users', [ApiImportUsersController::class, 'store'])->name('api.tenant.import.users');
    }
);
