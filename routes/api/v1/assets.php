<?php

use App\Helpers\ApiResponse;
use App\Models\Tenants\Asset;
use App\Services\QRCodeService;
use App\Services\PictureService;
use App\Services\DocumentService;
use Illuminate\Support\Facades\Route;
use Barryvdh\Debugbar\Facades\Debugbar;
use App\Http\Requests\Tenant\PictureUploadRequest;
use App\Http\Controllers\API\V1\APIAssetController;
use App\Http\Requests\Tenant\DocumentUploadRequest;
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
])->prefix('/v1/assets')->group(
    function () {

        // Route::middleware(['auth'])->group(function () {

        Route::get('/', function () {
            return ApiResponse::success(Asset::withoutTrashed()->get());
        })->name('api.assets.index');

        Route::post('/', [APIAssetController::class, 'store'])->name('api.assets.store');

        Route::get('/trashed', [ApiSearchTrashedAssetController::class, 'index'])->name('api.assets.trashed');



        Route::prefix('{asset}')->group(function () {
            // Get one asset and his maintainable
            Route::get('/', function (Asset $asset) {
                return ApiResponse::success($asset->load('maintainable.manager:id,first_name,last_name', 'maintainable.providers:id,name'));
            })->name('api.assets.show');

            Route::patch('/', [APIAssetController::class, 'update'])->name('api.assets.update');
            Route::delete('/', [APIAssetController::class, 'destroy'])->name('api.assets.destroy');

            // Restore a soft deleted asset
            Route::post('/restore', [RestoreSoftDeletedAssetController::class, 'restore'])->withTrashed()->name('api.assets.restore');

            // Force delete a soft deleted asset
            Route::delete('/force', [ForceDeleteAssetController::class, 'forceDelete'])->withTrashed()->name('api.assets.force');


            Route::post('/qr/regen', function (Asset $asset, QRCodeService $qRCodeService) {
                $qRCodeService->createAndAttachQR($asset);
                return ApiResponse::success();
            })->name('api.assets.qr.regen');

            // Get all the documents from an asset
            Route::get('/documents/', function ($asset) {
                $asset = Asset::withTrashed()->with('documents')->where('reference_code', $asset)->first();
                return ApiResponse::success($asset->documents);
            })->name('api.assets.documents');

            // Post a new document to the assets
            Route::post('/documents/', function (DocumentUploadRequest $documentUploadRequest, DocumentService $documentService, Asset $asset) {

                Debugbar::info($documentUploadRequest, $documentUploadRequest->validated());
                $files = $documentUploadRequest->validated('files');
                if ($files) {
                    $documentService->uploadAndAttachDocuments($asset, $files);
                }

                return ApiResponse::success([], 'Document added');
            })->name('api.assets.documents.post');

            // Get all pictures from an asset
            Route::get('/pictures/', function ($asset) {
                $asset = Asset::withTrashed()->with('pictures')->where('reference_code', $asset)->first();
                return ApiResponse::success($asset->pictures);
            })->name('api.assets.pictures');

            // Post a new picture to an asset
            Route::post('/pictures/', function (PictureUploadRequest $pictureUploadRequest, PictureService $pictureService, Asset $asset) {

                $files = $pictureUploadRequest->validated('pictures');
                if ($files) {
                    $pictureService->uploadAndAttachPictures($asset, $files);
                }

                return ApiResponse::success(null, 'Pictures added');
            })->name('api.assets.pictures.post');

            // Get all tickets from an asset
            Route::get('/tickets/', function (Asset $asset) {
                // $asset = Asset::withTrashed()->with('tickets')->where('reference_code', $asset)->first();
                return ApiResponse::success($asset->tickets);
            })->name('api.assets.tickets');

            // Get all interventions from an asset
            Route::get('/interventions/', function (Asset $asset) {
                return ApiResponse::success($asset->interventions()->where('ticket_id', null)->get());
            })->name('api.assets.interventions');
        });


        // });
    }
);
