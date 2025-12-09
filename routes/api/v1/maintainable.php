<?php

use Carbon\Carbon;
use App\Helpers\ApiResponse;
use App\Enums\MaintenanceFrequency;
use App\Models\Tenants\Maintainable;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CustomInitializeTenancyBySubdomain;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;

Route::middleware([
    'web',
    CustomInitializeTenancyBySubdomain::class,
    \Stancl\Tenancy\Middleware\ScopeSessions::class,
    \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
    'auth:tenant'
])->prefix('/v1/maintenance')->group(
    function () {

        Route::prefix('{maintainable}')->group(function () {

            // Mark maintenance as done
            Route::patch('/done/', function (Maintainable $maintainable) {

                $maintainable->last_maintenance_date = Carbon::now()->toDateString();

                if ($maintainable->maintenance_frequency !== MaintenanceFrequency::ONDEMAND->value)
                    $maintainable->next_maintenance_date = calculateNextMaintenanceDate($maintainable->maintenance_frequency);
                else {
                    $maintainable->next_maintenance_date = null;
                }


                $maintainable->save();

                return ApiResponse::success([], 'Maintenance done');
            })->name('api.maintenance.done');
        });
    }
);
