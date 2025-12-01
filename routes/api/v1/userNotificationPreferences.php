<?php

use Carbon\Carbon;
use App\Helpers\ApiResponse;
use App\Enums\MaintenanceFrequency;
use App\Models\Tenants\Maintainable;
use Illuminate\Support\Facades\Route;
use App\Models\Tenants\UserNotificationPreference;
use App\Http\Middleware\CustomInitializeTenancyBySubdomain;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;
use App\Http\Controllers\API\V1\APIUserNotificationPreferencesController;

Route::middleware([
    'web',
    CustomInitializeTenancyBySubdomain::class,
    \Stancl\Tenancy\Middleware\ScopeSessions::class,
    \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
    'auth:tenant'
])->prefix('/v1/notification-preferences')->group(
    function () {
        // Route::post('/', [APIUserNotificationPreferencesController::class, 'store'])->name('api.notifications.store');

        Route::prefix('{preference}')->group(function () {

            Route::patch('/', [APIUserNotificationPreferencesController::class, 'update'])->name('api.notifications.update');
        });
    }
);
