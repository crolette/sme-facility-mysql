<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CacheTenantLimits;
use Stancl\Tenancy\Middleware\ScopeSessions;
use App\Http\Middleware\TenantLocaleMiddleware;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\CompanyProfileController;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use App\Http\Controllers\Tenants\UserNotificationPreferenceController;

Route::middleware([
    'web',
    InitializeTenancyBySubdomain::class,
    ScopeSessions::class,
    PreventAccessFromCentralDomains::class,
    TenantLocaleMiddleware::class,
    'auth:tenant'
])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('tenant.profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('tenant.profile.update');
    // Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('tenant.profile.destroy');

    Route::get('settings/password', [PasswordController::class, 'edit'])->name('tenant.password.edit');
    Route::put('settings/password', [PasswordController::class, 'update'])->name('tenant.password.update');

    Route::get('settings/company', [CompanyProfileController::class, 'show'])->name('tenant.company.show');

    Route::resource('settings/notification-preferences', UserNotificationPreferenceController::class)->parameters(['notification-preferences' => 'preference'])->only('index', 'show', 'create', 'edit')->names('tenant.notification-preferences');


    Route::get('settings/appearance', function () {
        return Inertia::render('settings/appearance');
    })->name('appearance');
});
