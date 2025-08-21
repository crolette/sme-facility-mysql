<?php

use App\Helpers\ApiResponse;
use App\Http\Controllers\API\V1\APIContractController;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;

Route::middleware([
    'web',
    InitializeTenancyBySubdomain::class,
    \Stancl\Tenancy\Middleware\ScopeSessions::class,
    \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
    'auth:tenant'
])->prefix('/v1/contracts')->group(function () {

    Route::post('/', [APIContractController::class, 'store'])->name('api.contracts.store');

    Route::prefix('/{contract}')->group(function () {
        Route::patch('/', [APIContractController::class, 'update'])->name('api.contracts.update');
    });
});
