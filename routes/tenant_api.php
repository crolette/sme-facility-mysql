<?php

use App\Http\Controllers\API\V1\ApiSearchLocationController;
use App\Http\Controllers\API\V1\ApiSearchTrashedAssetController;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubDomain;



Route::prefix('api/v1')->group(
    function () {
        Route::middleware([
            'web',

            InitializeTenancyBySubDomain::class,
            \Stancl\Tenancy\Middleware\ScopeSessions::class,
            \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
        ])->group(function () {

            Route::middleware(['auth'])->group(function () {
                Route::get('/locations', [ApiSearchLocationController::class, 'index'])->name('api.locations');
                Route::get('/assets/trashed', [ApiSearchTrashedAssetController::class, 'index'])->name('api.assets.trashed');
            });
        });
    }
);
