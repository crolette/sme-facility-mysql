<?php

use Carbon\Carbon;
use App\Helpers\ApiResponse;
use App\Enums\MaintenanceFrequency;
use App\Http\Controllers\API\V1\APIUserNotificationPreferencesController;
use App\Models\Tenants\Maintainable;
use App\Models\Tenants\UserNotificationPreference;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;

Route::middleware([
    'web',
    InitializeTenancyBySubdomain::class,
    \Stancl\Tenancy\Middleware\ScopeSessions::class,
    \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
    'auth:tenant'
])->prefix('/v1/notification-preferences')->group(
    function () {
        Route::post('/', [APIUserNotificationPreferencesController::class, 'store'])->name('api.notification-preferences.store');

        Route::prefix('{preference}')->group(function () {

            Route::patch('/', [APIUserNotificationPreferencesController::class, 'update'])->name('api.notification-preferences.update');
        });
    }
);
