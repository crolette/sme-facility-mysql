<?php

declare(strict_types=1);

use Inertia\Inertia;
use App\Exports\UsersExport;
use App\Helpers\ApiResponse;
use App\Models\Tenants\Room;
use App\Models\Tenants\Site;
use Illuminate\Http\Request;
use App\Exports\AssetsExport;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Floor;
use App\Mail\TicketClosedMail;
use App\Models\Tenants\Ticket;
use App\Mail\TicketCreatedMail;
use App\Models\Tenants\Company;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Tenants\Building;
use App\Models\Tenants\Contract;
use App\Mail\ContractExpiredMail;
use App\Mail\ContractExtendedMail;
use Illuminate\Support\Facades\App;
use App\Models\Tenants\Intervention;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Stancl\Tenancy\Middleware\ScopeSessions;
use App\Mail\SendInterventionToProviderEmail;
use App\Http\Middleware\TenantLocaleMiddleware;
use App\Http\Controllers\Tenants\UserController;
use App\Http\Controllers\Tenants\TicketController;
use App\Http\Controllers\API\V1\APITicketController;
use App\Http\Controllers\Tenants\ContractController;
use App\Http\Controllers\Tenants\ProviderController;
use App\Http\Controllers\Tenants\DashboardController;
use App\Http\Controllers\Tenants\DocumentsController;
use App\Http\Controllers\Tenants\StatisticsController;
use App\Http\Controllers\Tenants\TenantRoomController;
use App\Http\Controllers\Tenants\TenantSiteController;
use App\Http\Controllers\Tenants\TenantAssetController;
use App\Http\Controllers\Tenants\TenantFloorController;
use App\Http\Controllers\Tenants\UsersExportController;
use App\Http\Controllers\Tenants\AssetsExportController;
use App\Http\Controllers\Tenants\ImportExportController;
use App\Http\Controllers\Tenants\InterventionController;
use App\Http\Controllers\API\V1\APICompanyLogoController;
use App\Http\Controllers\Tenants\ContractExportController;
use App\Http\Controllers\Tenants\TenantBuildingController;
use App\Http\Controllers\Tenants\ProvidersExportController;
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
    TenantLocaleMiddleware::class,
    // 'localizationRedirect',
    'auth:tenant'
])->group(function () {


    Route::get('/robots.txt', function () {
        if (tenancy()->initialized) {
            // Tous les tenants : bloquer tout
            $content = "User-agent: *\nDisallow: /";
        } else {
            // Central : bloquer seulement l'app, permettre le site vitrine
            $content = "User-agent: *\n";
            $content .= "Disallow: /login\n";
            $content .= "Disallow: /register\n";
            $content .= "Disallow: /admin\n";
            $content .= "Allow: /\n";
        }

        return response($content)->header('Content-Type', 'text/plain');
    });

    // Route::get('mail', function () {
    //     // $data = ScheduledNotification::where('notification_type', 'end_warranty_date')->first();
    //     $data = Intervention::first();
    //     $model = $data->ticketable;
    //     $url = 'hello';

    //     return (new SendInterventionToProviderEmail($data, 'google.com'))->render();
    // });

    Route::get('locale/{locale}', function (Request $request, $locale) {

        if (in_array($locale, array_keys(config('laravellocalization.supportedLocales')))) {
            Auth::user()->setLocale($locale);
            Session::put('locale', $locale);
            App::setLocale($locale);
        }

        return Inertia::location(url()->previous());
    })->name('tenant.locale');

    Route::get('dashboard', [DashboardController::class, 'show'])->name('tenant.dashboard');

    Route::get('/pdf-qr-codes', function (Request $request) {

        if ($request->query('type') !== 'all') {

            $validation = Validator::make(
                $request->all(),
                [
                    'ids' => 'nullable|array|max:12',
                    'ids' => 'exists:' . $request->query('type') . ',id'
                ]
            );

            $codes = match ($request->query('type')) {
                'sites' => Site::select('id', 'code', 'reference_code', 'qr_code', 'location_type_id')->whereNotNull('qr_code')->get(),
                'buildings' => Building::select('id', 'code', 'reference_code', 'qr_code', 'location_type_id')->whereNotNull('qr_code')->get(),
                'floors' => Floor::select('id', 'code', 'reference_code', 'qr_code', 'location_type_id')->whereNotNull('qr_code')->get(),
                'rooms' => Room::select('id', 'code', 'reference_code', 'qr_code', 'location_type_id')->whereNotNull('qr_code')->get(),
                'assets' => Asset::select('id', 'code', 'reference_code', 'qr_code', 'category_type_id')->whereNotNull('qr_code'),
            };

            if ($validation->validated('ids')) {
                $codes->whereIn('id',  [...collect($validation->validated('ids'))->flatten()]);
            }
        } else {
            $collection = collect([]);

            $sites = Site::select('id', 'code', 'reference_code', 'qr_code', 'location_type_id')->whereNotNull('qr_code')->get();
            $buildings = Building::select('id', 'code', 'reference_code', 'qr_code', 'location_type_id')->whereNotNull('qr_code')->get();
            $floors = Floor::select('id', 'code', 'reference_code', 'qr_code', 'location_type_id')->whereNotNull('qr_code')->get();
            $rooms = Room::select('id', 'code', 'reference_code', 'qr_code', 'location_type_id')->whereNotNull('qr_code')->get();
            $assets = Asset::select('id', 'code', 'reference_code', 'qr_code', 'category_type_id')->whereNotNull('qr_code')->get();
            $codes = $collection->merge($sites)->merge($buildings)->merge($floors)->merge($rooms)->merge($assets);
        }

        $pdf = Pdf::loadView('pdf.qr-codes', ['codes' => $codes->get()])->setPaper('a4', 'portrait');
        return $pdf->stream('qrcode.pdf');
    })->name('tenant.pdf.qr-codes');

    Route::prefix('/settings/import-export/')->group(function () {
        Route::get('/', [ImportExportController::class, 'show'])->name('tenant.import-export');
        Route::post('/assets/export', [AssetsExportController::class, 'index'])->name('tenant.assets.export');
        Route::post('/providers/export', [ProvidersExportController::class, 'index'])->name('tenant.providers.export');
        Route::post('/users/export', [UsersExportController::class, 'index'])->name('tenant.users.export');
        Route::post('/contracts/export', [ContractExportController::class, 'index'])->name('tenant.contracts.export');
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


    Route::prefix('/documents')->group(function () {
        Route::get('/', [DocumentsController::class, 'index'])->name('tenant.documents.index');
    });

    Route::prefix('/statistics')->group(function () {
        Route::get('/', [StatisticsController::class, 'index'])->name('tenant.statistics');
    });


    Route::prefix('company')->group(function () {
        Route::get('/', function () {
            $company = Company::first();

            return ApiResponse::success($company);
        })->name('api.company.logo.show');
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

    Route::prefix('interventions')->group(function () {
        Route::get('', [InterventionController::class, 'index'])->name('tenant.interventions.index');
        // Route::get('/create/{ticket}', [InterventionController::class, 'create'])->name('tenant.interventions.create');
        Route::get('/{intervention}', [InterventionController::class, 'show'])->name('tenant.interventions.show');
        // Route::get('/{intervention}/actions/create', [InterventionActionController::class, 'create'])->name('tenant.interventions.actions.create');
        // Route::get('/{intervention}/actions/{action}/edit', [InterventionActionController::class, 'edit'])->name('tenant.interventions.actions.edit');
    });
});


Route::middleware(array_merge([
    'web',
    InitializeTenancyBySubdomain::class,
    ScopeSessions::class,
    PreventAccessFromCentralDomains::class,
], app()->environment('local') ? [] : ['throttle:6,60']))->group(function () {

    Route::get('/robots.txt', function () {
        $content = "User-agent: *\nDisallow: /";
        return response($content)->header('Content-Type', 'text/plain');
    });

    Route::get('/assets/{qr_hash}/tickets/create', [CreateTicketFromQRCodeController::class, 'createFromAsset'])->name('tenant.assets.tickets.create');

    Route::get('/sites/{qr_hash}/tickets/create', [CreateTicketFromQRCodeController::class, 'createFromSite'])->name('tenant.sites.tickets.create');

    Route::get('/buildings/{qr_hash}/tickets/create', [CreateTicketFromQRCodeController::class, 'createFromBuilding'])->name('tenant.buildings.tickets.create');

    Route::get('/floors/{qr_hash}/tickets/create', [CreateTicketFromQRCodeController::class, 'createFromFloor'])->name('tenant.floors.tickets.create');

    Route::get('/rooms/{qr_hash}/tickets/create', [CreateTicketFromQRCodeController::class, 'createFromRoom'])->name('tenant.rooms.tickets.create');

    // Post a new ticket
    Route::post('/', [APITicketController::class, 'store'])->name('api.tickets.store');


    Route::middleware(array_merge(app()->environment('testing') ? [] : ['signed']))->group(function () {
        Route::get('/interventions/{intervention}/external', [InterventionProviderController::class, 'create'])->name('tenant.intervention.provider');
        Route::post('/interventions/{intervention}/external', [InterventionProviderController::class, 'store'])->name('tenant.intervention.provider.store');
    });
});

require __DIR__ . '/tenant_settings.php';
require __DIR__ . '/tenant_auth.php';
require __DIR__ . '/tenant_api.php';
