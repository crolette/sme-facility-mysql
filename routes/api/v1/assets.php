<?php

use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Models\Tenants\Asset;
use App\Services\QRCodeService;
use App\Models\Tenants\Contract;
use App\Services\PictureService;
use App\Services\ContractService;
use App\Services\DocumentService;
use Illuminate\Support\Facades\Route;
use Barryvdh\Debugbar\Facades\Debugbar;
use App\Http\Requests\Tenant\ContractStoreRequest;
use App\Http\Requests\Tenant\PictureUploadRequest;
use App\Http\Controllers\API\V1\APIAssetController;
use App\Http\Requests\Tenant\DocumentUploadRequest;
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

                $files = $documentUploadRequest->validated('files');
                if ($files) {
                    $documentService->uploadAndAttachDocuments($asset, $files);
                }

                return ApiResponse::success([], 'Document added');
            })->name('api.assets.documents.post');

            
            Route::prefix('/contracts')->group(function () {
                Route::post('', function (Asset $asset, ContractWithModelStoreRequest $contractWithModelRequest) {

                    if ($contractWithModelRequest->validated('existing_contracts'))
                        app(ContractService::class)->attachExistingContractsToModel($asset, $contractWithModelRequest->validated('existing_contracts'));

                    return ApiResponse::success([], 'Contract(s) added');
                })->name('api.assets.contracts.post');

                // Remove/Detach a contract from an asset
                Route::delete('', function (Asset $asset, Request $request) {
                    Debugbar::info($request);                    $validated = $request->validateWithBag('errors',[
                        'contract_id' => 'required|exists:contracts,id'
                    ]);
                        app(ContractService::class)->detachExistingContractFromModel($asset, $validated['contract_id']);
                        return ApiResponse::success([], 'Contract removed');
                })->name('api.assets.contracts.delete');
            });

               

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
