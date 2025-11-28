<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\V1\ApiImportController;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;

Route::middleware([
    'web',
    InitializeTenancyBySubdomain::class,
    \Stancl\Tenancy\Middleware\ScopeSessions::class,
    \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
    'auth:tenant'
])->prefix('/v1/import')->group(
    function () {

        Route::post('/', [ApiImportController::class, 'store'])->name('api.tenant.import');
    }
);
