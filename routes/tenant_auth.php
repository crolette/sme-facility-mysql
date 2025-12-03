<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CacheTenantLimits;
use App\Http\Middleware\TenantLocaleMiddleware;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Middleware\CustomInitializeTenancyBySubdomain;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;
use App\Http\Controllers\Tenants\Auth\TenantAuthenticatedSessionController;

Route::middleware([
    'web',
    // InitializeTenancyBySubdomain::class,
    CustomInitializeTenancyBySubdomain::class,
    \Stancl\Tenancy\Middleware\ScopeSessions::class,
    \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
    CacheTenantLimits::class,
    TenantLocaleMiddleware::class,
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


    Route::get('/', function () {

        if (Auth::guard('tenant')->check()) {

            return redirect()->route('tenant.dashboard');
        }

        return redirect()->route('tenant.login');
    })->name('login');


    Route::get('login', [TenantAuthenticatedSessionController::class, 'create'])
        ->name('tenant.login');

    Route::post('login', [TenantAuthenticatedSessionController::class, 'store'])->name('tenant.login.post');

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

Route::middleware(
    'web',
    InitializeTenancyBySubdomain::class,
    \Stancl\Tenancy\Middleware\ScopeSessions::class,
    \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
)->group(function () {
    // Route::get('verify-email', EmailVerificationPromptController::class)
    //     ->name('verification.notice');

    //     Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
    //         ->middleware(['signed', 'throttle:6,1'])
    //         ->name('verification.verify');

    //     Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
    //         ->middleware('throttle:6,1')
    //         ->name('verification.send');

    //     Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
    //         ->name('password.confirm');

    //     Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);



    Route::post('logout', [TenantAuthenticatedSessionController::class, 'destroy'])
        ->name('tenant.logout');
});
