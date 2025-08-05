<?php

use Carbon\Carbon;
use App\Helpers\ApiResponse;
use App\Models\Tenants\Room;
use App\Models\Tenants\Site;
use Illuminate\Http\Request;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Floor;
use App\Models\Tenants\Ticket;
use App\Models\Tenants\Picture;
use App\Models\Tenants\Building;
use App\Models\Tenants\Document;
use App\Services\PictureService;
use App\Services\DocumentService;
use App\Models\Central\CategoryType;
use App\Models\Tenants\Maintainable;
use Illuminate\Support\Facades\Route;
use Barryvdh\Debugbar\Facades\Debugbar;
use App\Http\Requests\Tenant\PictureUploadRequest;
use App\Http\Requests\Tenant\DocumentUploadRequest;
use App\Http\Controllers\API\V1\APITicketController;
use App\Http\Controllers\API\V1\DestroyPictureController;
use App\Http\Controllers\API\V1\UpdateDocumentController;
use App\Http\Controllers\API\V1\APIInterventionController;
use App\Http\Controllers\API\V1\DestroyDocumentController;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;
use App\Http\Controllers\API\V1\ApiSearchLocationController;
use App\Http\Controllers\Tenants\ForceDeleteAssetController;
use App\Http\Controllers\API\V1\APIInterventionActionController;
use App\Http\Controllers\API\V1\ApiSearchTrashedAssetController;
use App\Http\Controllers\Tenants\RestoreSoftDeletedAssetController;
use App\Http\Controllers\API\V1\Tickets\InterventionForLocationController;

Route::middleware([
    'web',
    InitializeTenancyBySubdomain::class,
    \Stancl\Tenancy\Middleware\ScopeSessions::class,
    \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
    'auth:tenant'
])->prefix('/v1/maintenance')->group(
    function () {

        Route::prefix('{maintainable}')->group(function () {

            // Mark maintenance as done
            Route::post('/done/', function (Maintainable $maintainable) {

                $maintainable->last_maintenance_date = Carbon::now()->toDateString();
                $maintainable->next_maintenance_date = calculateNextMaintenanceDate($maintainable->maintenance_frequency);

                $maintainable->save();

                return ApiResponse::success([], 'Maintenance done');
            })->name('api.maintenance.done');
        });
    }
);
