<?php

use Inertia\Inertia;
use App\Models\Tenant;
use App\Helpers\ApiResponse;
use App\Models\Tenants\User;
use Illuminate\Http\Request;
use App\Http\Middleware\setLocale;
use App\Mail\NewTenantCreatedMail;
use Illuminate\Support\Facades\Log;
use App\Events\NewTenantCreatedEvent;
use Illuminate\Support\Facades\Route;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Support\Facades\Password;
use App\Http\Middleware\AuthenticateCentral;
use App\Http\Controllers\Central\CentralTenantController;
use App\Http\Controllers\Central\AdminLocationTypeController;
use App\Http\Controllers\Central\CentralCategoryTypeController;
use App\Http\Controllers\Central\CentralAssetCategoryController;
use App\Http\Controllers\Central\RegisterCentralTenantController;
use App\Notifications\TenantAdminCreatedPasswordResetNotification;

foreach (config('tenancy.central_domains') as $domain) {
    Route::domain($domain)->group(function () {


        Route::middleware(['web', AuthenticateCentral::class])->group(function () {
            Route::get('dashboard', function () {


                return Inertia::render('central/dashboard');
            })->name('central.dashboard');

            Route::get('tenants', [CentralTenantController::class, 'index'])->name('central.tenants.index');

            Route::get('api/tenants', function () {
                $tenants = Tenant::with('domain')->get();
                return ApiResponse::success($tenants);
            })->name('api.central.tenants.index');


            Route::get('tenants/create', [RegisterCentralTenantController::class, 'create'])->name('central.tenants.create');
            Route::post('tenants/create', [RegisterCentralTenantController::class, 'store'])->name('central.tenants.store');

            Route::get('tenants/{tenant}', [CentralTenantController::class, 'show'])->name('central.tenants.show');

            Route::get('tenants/{tenant}/update', [CentralTenantController::class, 'edit'])->name('central.tenants.edit');
            Route::patch('tenants/{tenant}/update', [CentralTenantController::class, 'update'])->name('central.tenants.update');

            Route::delete('tenants/{tenant}', [CentralTenantController::class, 'destroy'])->name('central.tenants.delete');

            // Route::resource('tenants', CentralTenantController::class)->parameters(['tenants' => 'tenant'])->names('central.tenants');


            Route::resource('location-types', AdminLocationTypeController::class)->parameters(['location-types' => 'locationType'])->names('central.locations');

            Route::resource('asset-categories', CentralAssetCategoryController::class)->parameters(['asset-categories' => 'assetCategory'])->names('central.assets');

            Route::resource('category-types', CentralCategoryTypeController::class)->parameters(['category-types' => 'categoryType'])->names('central.types');

            Route::post('/tenant-notif/', function (Request $request) {

                try {
                    $tenant = Tenant::findOrFail($request->tenant);
                    $email = $tenant->email;

                    $tenant->run(function () use ($email, $tenant) {
                        $admin = User::where('email', $email)->first();
                        
                        event(new NewTenantCreatedEvent($admin, $tenant));

                        $token = Password::createToken($admin);
                        $admin->notify(new TenantAdminCreatedPasswordResetNotification($token, $tenant));
                    });
                    return ApiResponse::success([], 'Notif sent');
                } catch (Exception $e) {
                    Log::info($e->getMessage());
                    return ApiResponse::error($e->getMessage());
                }
            })->name('send-notif-tenant-admin');
        });
    });
}


require __DIR__ . '/website.php';
require __DIR__ . '/central_auth.php';
