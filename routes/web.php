<?php

use App\Http\Controllers\Central\AdminBuildingTypeController;
use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Central\CentralTenantController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Central\AdminLocationTypeController;
use App\Http\Controllers\Central\AdminSiteTypeController;
use App\Http\Controllers\Central\CentralAssetCategoryController;
use App\Http\Controllers\Central\RegisterCentralTenantController;

foreach (config('tenancy.central_domains') as $domain) {
    Route::domain($domain)->group(function () {
        Route::get('/', function () {
            return Inertia::render('welcome');
        })->name('home');


        Route::middleware(['auth', 'verified'])->group(function () {
            Route::get('dashboard', function () {


                return Inertia::render('central/dashboard');
            })->name('central.dashboard');

            Route::get('tenants', [CentralTenantController::class, 'index'])->name('central.tenants.index');

            Route::get('tenants/create', [RegisterCentralTenantController::class, 'create'])->name('central.tenants.create');
            Route::post('tenants/create', [RegisterCentralTenantController::class, 'store'])->name('central.tenants.store');

            Route::get('tenants/{tenant}', [CentralTenantController::class, 'show'])->name('central.tenants.show');

            Route::get('tenants/{tenant}/update', [CentralTenantController::class, 'edit'])->name('central.tenants.edit');
            Route::patch('tenants/{tenant}/update', [CentralTenantController::class, 'update'])->name('central.tenants.update');

            Route::delete('tenants/{tenant}', [CentralTenantController::class, 'destroy'])->name('central.tenants.delete');

            // Route::resource('tenants', CentralTenantController::class)->parameters(['tenants' => 'tenant'])->names('central.tenants');


            Route::resource('location-types', AdminLocationTypeController::class)->parameters(['location-types' => 'locationType'])->names('central.locations');
            Route::resource('asset-categories', CentralAssetCategoryController::class)->parameters(['asset-categories' => 'assetCategory'])->names('central.assets');
        });
    });
}

require __DIR__ . '/settings.php';
require __DIR__ . '/central_auth.php';
