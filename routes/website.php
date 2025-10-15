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
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use App\Http\Controllers\Central\AdminLocationTypeController;
use App\Http\Controllers\Central\CentralCategoryTypeController;
use App\Http\Controllers\Central\CentralAssetCategoryController;
use App\Http\Controllers\Central\RegisterCentralTenantController;
use App\Notifications\TenantAdminCreatedPasswordResetNotification;

foreach (config('tenancy.central_domains') as $domain) {
    Route::domain($domain)->group(function () {

        Route::group(
            [
                'prefix' => LaravelLocalization::setLocale(),
                'middleware' => ['localeSessionRedirect', 'localizationRedirect']
            ],
            function () {

                Route::get('/', function () {
                    return Inertia::render('welcome');
                })->name('home');


                Route::prefix('features')->group(function () {
                    Route::get('/qr-code', function () {
                        return Inertia::render('website/features/qr-code');
                    })->name('website.features.qrcode');

                    Route::get('/maintenance', function () {
                        return Inertia::render('website/features/maintenance');
                    })->name('website.features.maintenance');

                    Route::get('/contracts', function () {
                        return Inertia::render('website/features/contracts');
                    })->name('website.features.contracts');

                    Route::get('/assets', function () {
                        return Inertia::render('website/features/assets');
                    })->name('website.features.assets');

                    Route::get('/documents', function () {
                        return Inertia::render('website/features/documents');
                    })->name('website.features.documents');

                    Route::get('/statistics', function () {
                        return Inertia::render('website/features/statistics');
                    })->name('website.features.statistics');

                    Route::get('/roles', function () {
                        return Inertia::render('website/features/qr-code');
                    })->name('website.features.roles');
                });

                Route::prefix('who')->group(function () {
                    Route::get('/facility-manager', function () {
                        return Inertia::render('website/who/facility-manager');
                    })->name('website.who.facility-manager');
                    Route::get('/maintenance-manager', function () {
                        return Inertia::render('website/who/maintenance-manager');
                    })->name('website.who.maintenance-manager');
                    Route::get('/sme', function () {
                        return Inertia::render('website/who/sme');
                    })->name('website.who.sme');
                });

                Route::prefix('why')->group(function () {
                    Route::get('/sme-facility', function () {
                        return Inertia::render('website/why-sme/why-sme');
                    })->name('website.why');
                });

                Route::get('pricing', function () {
                    return Inertia::render('website/pricing');
                })->name('website.pricing');
            }
        );
    });
}
