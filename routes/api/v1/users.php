<?php

use Stancl\Tenancy\Middleware\ScopeSessions;
use App\Http\Controllers\API\V1\APIProviderController;
use App\Http\Controllers\API\V1\APIUserController;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;


Route::middleware([
    'web',
    InitializeTenancyBySubdomain::class,
    ScopeSessions::class,
    PreventAccessFromCentralDomains::class,
    'auth:tenant'
])->prefix('/v1/users')->group(function () {

    Route::post('/', [APIUserController::class, 'store'])->name('api.users.store');
    Route::patch('/{user}', [APIUserController::class, 'update'])->name('api.users.update');
    Route::delete('/{user}', [APIUserController::class, 'destroy'])->name('api.users.destroy');
});
