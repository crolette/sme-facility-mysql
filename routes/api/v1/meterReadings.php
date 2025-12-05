<?php

use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Models\Tenants\Asset;
use App\Services\QRCodeService;
use App\Models\Tenants\Contract;
use App\Models\Tenants\Document;
use App\Services\PictureService;
use App\Services\ContractService;
use App\Services\DocumentService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Barryvdh\Debugbar\Facades\Debugbar;
use App\Http\Requests\Tenant\ContractStoreRequest;
use App\Http\Requests\Tenant\PictureUploadRequest;
use App\Http\Controllers\API\V1\APIAssetController;
use App\Http\Requests\Tenant\DocumentUploadRequest;
use App\Http\Controllers\Tenants\MeterReadingsController;
use App\Http\Requests\Tenant\ContractWithModelStoreRequest;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;
use App\Http\Controllers\Tenants\ForceDeleteAssetController;
use App\Http\Controllers\API\V1\ApiSearchTrashedAssetController;
use App\Http\Controllers\Tenants\RestoreSoftDeletedAssetController;

Route::middleware([
    'web',
    InitializeTenancyBySubdomain::class,
    \Stancl\Tenancy\Middleware\ScopeSessions::class,
    \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
    'auth:tenant'
])->prefix('/v1/meters')->group(
    function () {


        Route::patch('/{meterReading}', [MeterReadingsController::class, 'update'])->name('api.meter-readings.patch');
        Route::delete('/{meterReading}', [MeterReadingsController::class, 'destroy'])->name('api.meter-readings.delete');

        Route::prefix('{asset}')->group(function () {
            Route::prefix('/meter-readings')->group(function () {
                Route::get('', [MeterReadingsController::class, 'index'])->name('api.meter-readings.index');
                Route::post('', [MeterReadingsController::class, 'store'])->name('api.meter-readings.store');
            });
        });
    }
);
