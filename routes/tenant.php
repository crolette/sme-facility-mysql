<?php

declare(strict_types=1);

use App\Models\Tenant;
use App\Mail\PasswordReset;
use App\Models\Tenants\Room;
use App\Models\Tenants\Site;
use App\Models\Tenants\User;
use Illuminate\Http\Request;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Floor;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Tenants\Building;
use App\Mail\NewTenantCreatedMail;
use Illuminate\Support\Facades\URL;
use App\Models\Tenants\Intervention;
use Illuminate\Support\Facades\Route;
use App\Mail\NewTenantPasswordCreation;
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
use App\Mail\ScheduledNotificationMail;
use App\Models\Tenants\ScheduledNotification;

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
        // $param1 = User::first();

        // $url = url(route('password.reset', [
        //     'token' => 1111,
        //     'email' => $param1->getEmailForPasswordReset(),
        // ], false));

        // $tenant = Tenant::first();
        // return (new PasswordReset($param1,$url))->render();
        
        $param1 = ScheduledNotification::where('notification_type', 'planned_at')->first();
        // dd($param1->data);
        // dd($param1->interventionable->location_route);
        return (new ScheduledNotificationMail($param1))->render();
    });

    Route::get('/pdf-qr-codes', function(Request $request) {

     

        $collection = collect([]);

        $sites = Site::select('id','code', 'reference_code','qr_code', 'location_type_id')->where('qr_code', '!=', null)->get();

        $buildings = Building::select('id','code', 'reference_code','qr_code', 'location_type_id')->where('qr_code', '!=', null)->get();
        
        $floors = Floor::select('id','code', 'reference_code','qr_code', 'location_type_id')->where('qr_code', '!=', null)->get();
        
        $rooms = Room::select('id','code', 'reference_code','qr_code', 'location_type_id')->where('qr_code', '!=', null)->get();

        $assets = Asset::select('id', 'code', 'reference_code', 'qr_code', 'category_type_id')->where('qr_code', '!=', null)->get();

        $codes = match($request->query('type')) {
            'sites' => $sites,
            'buildings' => $buildings,
            'floors' => $floors,
            'rooms' => $rooms,
            'assets' => $assets,
            default => $collection->merge($sites)->merge($buildings)->merge($floors)->merge($rooms)->merge($assets)

        };

        $pdf = Pdf::loadView('pdf.qr-codes', ['codes' => $codes])->setPaper('a4', 'portrait');
        return $pdf->stream('qrcode.pdf');

    })->name('tenant.pdf.qr-codes');

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
