<?php

use Carbon\Carbon;
use App\Helpers\ApiResponse;
use App\Enums\MaintenanceFrequency;
use App\Models\Tenants\Maintainable;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\V1\ApiImportController;
use App\Http\Controllers\API\V1\ApiImportProvidersController;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;

Route::middleware([
    'web',
    InitializeTenancyBySubdomain::class,
    \Stancl\Tenancy\Middleware\ScopeSessions::class,
    \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
    'auth:tenant'
])->prefix('/v1/import')->group(
    function () {

        Route::post('/assets', [ApiImportController::class, 'store'])->name('api.tenant.import.assets');
        Route::post('/providers', [ApiImportProvidersController::class, 'store'])->name('api.tenant.import.providers');
    }
);
