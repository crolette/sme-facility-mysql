<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

foreach (config('tenancy.central_domains') as $domain) {
    Route::domain($domain)->group(
        function () {
            Route::middleware('guest')->group(function () {
                Route::get('register', [RegisteredUserController::class, 'create'])
                    ->name('central.register');

                Route::post('register', [RegisteredUserController::class, 'store'])->name('central.register.post');

                Route::get('login', [AuthenticatedSessionController::class, 'create'])
                    ->name('central.login');

                Route::post('login', [AuthenticatedSessionController::class, 'store'])->name('central.login.post');

                Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
                    ->name('central.password.request');

                Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
                    ->name('central.password.email');

                Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
                    ->name('central.password.reset');

                Route::post('reset-password', [NewPasswordController::class, 'store'])
                    ->name('central.password.store');
            });



            Route::middleware('auth')->group(function () {
                Route::get('verify-email', EmailVerificationPromptController::class)
                    ->name('central.verification.notice');

                Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
                    ->middleware(['signed', 'throttle:6,1'])
                    ->name('central.verification.verify');

                Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
                    ->middleware('throttle:6,1')
                    ->name('central.verification.send');

                Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
                    ->name('central.password.confirm');

                Route::post('confirm-password', [ConfirmablePasswordController::class, 'store'])->name('central.password.confirm.post');

                Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
                    ->name('central.logout');
            });
        }
    );
}
