<?php

declare(strict_types=1);

use Illuminate\Support\Facades\URL;
use App\Models\Tenants\Intervention;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\ScopeSessions;
use App\Mail\SendInterventionToProviderEmail;
use App\Http\Controllers\Tenants\UserController;
use App\Http\Controllers\Tenants\TicketController;
use App\Http\Controllers\API\V1\APITicketController;
use App\Http\Controllers\Tenants\ContractController;
use App\Http\Controllers\Tenants\ProviderController;
use App\Http\Controllers\Tenants\DashboardController;
use App\Http\Controllers\Tenants\TenantRoomController;
use App\Http\Controllers\Tenants\TenantSiteController;
use App\Http\Controllers\Tenants\TenantAssetController;
use App\Http\Controllers\Tenants\TenantFloorController;
use App\Http\Controllers\Tenants\InterventionController;
use App\Http\Controllers\API\V1\APICompanyLogoController;
use App\Http\Controllers\Tenants\TenantBuildingController;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;
use App\Http\Controllers\Tenants\InterventionActionController;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use App\Http\Controllers\Tenants\InterventionProviderController;
use App\Http\Controllers\Tenants\CreateTicketFromQRCodeController;

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
    Route::get('/mail', function () {
        $param1 = Intervention::first();

        $url = URL::temporarySignedRoute(
            'tenant.intervention.provider',
            now()->addDays(7),
            ['intervention' => $param1->id, 'email' => 'crolweb@gmail.com']
        );

        return (new SendInterventionToProviderEmail($param1, $url))->render();
    });

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

    Route::prefix('company')->group(function() {
        Route::post('/logo', [APICompanyLogoController::class, 'store'])->name('api.company.logo.store');
        Route::delete('/logo', [APICompanyLogoController::class, 'destroy'])->name('api.company.logo.destroy');
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
        // Route::get('/{ticket}/edit', [TicketController::class, 'update'])->name('tenant.tickets.update');
    });
    
    // INTERVENTIONS

    Route::prefix('interventions')->group(function() {
        Route::get('', [InterventionController::class, 'index'])->name('tenant.interventions.index');
        Route::get('/create/{ticket}', [InterventionController::class, 'create'])->name('tenant.interventions.create');
        Route::get('/{intervention}', [InterventionController::class, 'show'])->name('tenant.interventions.show');
        Route::get('/{intervention}/actions/create', [InterventionActionController::class, 'create'])->name('tenant.interventions.actions.create');
        Route::get('/{intervention}/actions/{action}/edit', [InterventionActionController::class, 'edit'])->name('tenant.interventions.actions.edit');


    });
});


Route::middleware(array_merge([
    'web',
    InitializeTenancyBySubdomain::class,
    ScopeSessions::class,
    PreventAccessFromCentralDomains::class,
], app()->environment('local') ? [] : ['throttle:6,60']))->group(function () {

    Route::get('/assets/{qr_hash}/tickets/create', [CreateTicketFromQRCodeController::class, 'createFromAsset'])->name('tenant.assets.tickets.create');

    Route::get('/sites/{qr_hash}/tickets/create', [CreateTicketFromQRCodeController::class, 'createFromSite'])->name('tenant.sites.tickets.create');

    Route::get('/buildings/{qr_hash}/tickets/create', [CreateTicketFromQRCodeController::class, 'createFromBuilding'])->name('tenant.buildings.tickets.create');

    Route::get('/floors/{qr_hash}/tickets/create', [CreateTicketFromQRCodeController::class, 'createFromFloor'])->name('tenant.floors.tickets.create');

    Route::get('/rooms/{qr_hash}/tickets/create', [CreateTicketFromQRCodeController::class, 'createFromRoom'])->name('tenant.rooms.tickets.create');

    // Post a new ticket
    Route::post('/', [APITicketController::class, 'store'])->name('api.tickets.store');


    // Route::get('/{intervention}/external', [InterventionProviderController::class, 'create'])->name('tenant.intervention.provider');
    Route::get('/interventions/{intervention}/external', [InterventionProviderController::class, 'create'])->name('tenant.intervention.provider')->middleware('signed');
    // Route::post('/{intervention}/external', [InterventionProviderController::class, 'store'])->name('tenant.intervention.provider.store');
    Route::post('/interventions/{intervention}/external', [InterventionProviderController::class, 'store'])->name('tenant.intervention.provider.store')->middleware('signed');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/tenant_auth.php';
require __DIR__ . '/tenant_api.php';
