<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\V1\ApiImportController;
use App\Http\Middleware\CustomInitializeTenancyBySubdomain;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;

Route::middleware([
    'web',
    CustomInitializeTenancyBySubdomain::class,
    \Stancl\Tenancy\Middleware\ScopeSessions::class,
    \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
    'auth:tenant'
])->prefix('/v1/import')->group(
    function () {

        Route::post('/', [ApiImportController::class, 'store'])->name('api.tenant.import');
    }
);
