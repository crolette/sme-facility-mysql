<?php

declare(strict_types=1);

use Inertia\Inertia;
use App\Models\Tenant;
use App\Jobs\DeleteDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\AuthenticateTenant;
use Stancl\Tenancy\Middleware\ScopeSessions;
use App\Http\Controllers\Tenants\TicketController;
use App\Http\Controllers\Tenants\TenantRoomController;
use App\Http\Controllers\Tenants\TenantSiteController;
use App\Http\Controllers\Tenants\TenantAssetController;
use App\Http\Controllers\Tenants\TenantFloorController;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use App\Http\Controllers\Tenants\TenantBuildingController;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;
use App\Http\Controllers\Tenants\ForceDeleteAssetController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use App\Http\Controllers\Tenants\RestoreSoftDeletedAssetController;
use App\Http\Controllers\Tenants\Auth\TenantAuthenticatedSessionController;
use App\Http\Controllers\Tenants\InterventionController;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware([
    'web',
    InitializeTenancyBySubdomain::class,
    ScopeSessions::class,
    PreventAccessFromCentralDomains::class,
    'auth:tenant'
])->group(function () {
    Route::get('dashboard', function () {
        // dd(Auth::user()->hasVerifiedEmail());
        return Inertia::render('tenants/dashboard');
    })->name('tenant.dashboard');

    Route::resource('sites', TenantSiteController::class)->parameters(['sites' => 'site'])->names('tenant.sites');
    Route::resource('buildings', TenantBuildingController::class)->parameters(['buildings' => 'building'])->names('tenant.buildings');
    Route::resource('floors', TenantFloorController::class)->parameters(['floors' => 'floor'])->names('tenant.floors');
    Route::resource('rooms', TenantRoomController::class)->parameters(['rooms' => 'room'])->names('tenant.rooms');


    Route::resource('assets', TenantAssetController::class)->parameters(['assets' => 'asset'])->names('tenant.assets');

    Route::get('/assets/{id}/deleted', [TenantAssetController::class, 'showDeleted'])->name('tenant.assets.deleted');


    // TICKETS
    Route::prefix('tickets')->group(function () {
        Route::get('/', [TicketController::class, 'index'])->name('tenant.tickets.index');
        Route::get('/create', [TicketController::class, 'create'])->name('tenant.tickets.create');
        Route::get('/{ticket}', [TicketController::class, 'show'])->name('tenant.tickets.show');
    });

    Route::get('interventions/', [InterventionController::class, 'index'])->name('tenant.interventions.index');
    Route::get('interventions/create/{ticket}', [InterventionController::class, 'create'])->name('tenant.interventions.create');
    Route::get('interventions/{intervention}', [InterventionController::class, 'show'])->name('tenant.interventions.show');
});

require __DIR__ . '/tenant_auth.php';
require __DIR__ . '/tenant_api.php';
