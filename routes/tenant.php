<?php

declare(strict_types=1);

use Inertia\Inertia;
use App\Models\Tenant;
use App\Jobs\DeleteDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\AuthenticateTenant;
use Stancl\Tenancy\Middleware\ScopeSessions;
use App\Http\Controllers\Tenants\QRCodeController;
use App\Http\Controllers\Tenants\TicketController;
use App\Http\Controllers\Tenants\DashboardController;
use App\Http\Controllers\Tenants\TenantRoomController;
use App\Http\Controllers\Tenants\TenantSiteController;
use App\Http\Controllers\Tenants\TenantAssetController;
use App\Http\Controllers\Tenants\TenantFloorController;
use App\Http\Controllers\Tenants\InterventionController;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use App\Http\Controllers\Tenants\TenantBuildingController;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;
use App\Http\Controllers\Tenants\ForceDeleteAssetController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Tenants\AssetTicketController;
use App\Http\Controllers\Tenants\InterventionActionController;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use App\Http\Controllers\Tenants\RestoreSoftDeletedAssetController;
use App\Http\Controllers\Tenants\Auth\TenantAuthenticatedSessionController;
use App\Http\Controllers\Tenants\ContractController;
use App\Http\Controllers\Tenants\ProviderController;
use App\Http\Controllers\Tenants\UserController;
use App\Http\Controllers\Tenants\UserNotificationPreferenceController;

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
    Route::get('dashboard', [DashboardController::class, 'show'])->name('tenant.dashboard');

    Route::resource('sites', TenantSiteController::class)->parameters(['sites' => 'site'])->only('index', 'show', 'create', 'edit')->names('tenant.sites');
    Route::resource('buildings', TenantBuildingController::class)->parameters(['buildings' => 'building'])->only('index', 'show', 'create', 'edit')->names('tenant.buildings');
    Route::resource('floors', TenantFloorController::class)->parameters(['floors' => 'floor'])->only('index', 'show', 'create', 'edit')->names('tenant.floors');
    Route::resource('rooms', TenantRoomController::class)->parameters(['rooms' => 'room'])->only('index', 'show', 'create', 'edit')->names('tenant.rooms');
    Route::resource('assets', TenantAssetController::class)->parameters(['assets' => 'asset'])->only('index', 'show', 'create', 'edit')->names('tenant.assets');

    Route::get('/assets/{id}/deleted', [TenantAssetController::class, 'showDeleted'])->name('tenant.assets.deleted');

    Route::resource('contracts', ContractController::class)->parameters(['contracts' => 'contract'])->only('index', 'show', 'create', 'edit')->names('tenant.contracts');



    // PROVIDERS
    Route::prefix('providers')->group(function () {
        Route::get('/', [ProviderController::class, 'index'])->name('tenant.providers.index');
        Route::get('/create', [ProviderController::class, 'create'])->name('tenant.providers.create');
        Route::get('/{provider}', [ProviderController::class, 'show'])->name('tenant.providers.show');
        Route::get('/{provider}/edit', [ProviderController::class, 'edit'])->name('tenant.providers.edit');
    });

    // USERS
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('tenant.users.index');
        Route::get('/create', [UserController::class, 'create'])->name('tenant.users.create');
        Route::get('/{user}', [UserController::class, 'show'])->name('tenant.users.show');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('tenant.users.edit');
    });

    // TICKETS
    Route::prefix('tickets')->group(function () {
        Route::get('/', [TicketController::class, 'index'])->name('tenant.tickets.index');
        Route::get('/{ticket}', [TicketController::class, 'show'])->name('tenant.tickets.show');
    });

    // INTERVENTIONS
    Route::get('interventions/', [InterventionController::class, 'index'])->name('tenant.interventions.index');
    Route::get('interventions/create/{ticket}', [InterventionController::class, 'create'])->name('tenant.interventions.create');
    Route::get('interventions/{intervention}', [InterventionController::class, 'show'])->name('tenant.interventions.show');
    Route::get('interventions/{intervention}/actions/create', [InterventionActionController::class, 'create'])->name('tenant.interventions.actions.create');
    Route::get('actions/{action}/edit', [InterventionActionController::class, 'edit'])->name('tenant.interventions.actions.edit');
});


Route::middleware([
    'web',
    InitializeTenancyBySubdomain::class,
    ScopeSessions::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {

    Route::get('/assets/{assetCode}/tickets/create', [AssetTicketController::class, 'createFromAsset'])->name('tenant.assets.tickets.create');

    Route::get('/sites/{site}/tickets/create', [AssetTicketController::class, 'createFromSite'])->name('tenant.sites.tickets.create');

    Route::get('/buildings/{building}/tickets/create', [AssetTicketController::class, 'createFromBuilding'])->name('tenant.buildings.tickets.create');

    Route::get('/floors/{floor}/tickets/create', [AssetTicketController::class, 'createFromFloor'])->name('tenant.floors.tickets.create');

    Route::get('/rooms/{Room}/tickets/create', [AssetTicketController::class, 'createFromRoom'])->name('tenant.rooms.tickets.create');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/tenant_auth.php';
require __DIR__ . '/tenant_api.php';
