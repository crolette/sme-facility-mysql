<?php

use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Services\QRCodeService;
use App\Models\Tenants\Building;
use App\Services\PictureService;
use App\Services\ContractService;
use App\Services\DocumentService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Barryvdh\Debugbar\Facades\Debugbar;
use App\Http\Requests\Tenant\PictureUploadRequest;
use App\Http\Requests\Tenant\DocumentUploadRequest;
use App\Http\Controllers\API\V1\APIBuildingController;
use App\Http\Requests\Tenant\ContractWithModelStoreRequest;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;

Route::middleware([
    'web',
    InitializeTenancyBySubdomain::class,
    \Stancl\Tenancy\Middleware\ScopeSessions::class,
    \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
    'auth:tenant'
])->prefix('/v1/buildings')->group(function () {
    Route::get('/', function (Request $request) {
        if ($request->site) {
            $buildings = Building::where('level_id', $request->site)->get();

            return ApiResponse::success($buildings);
        }

        return ApiResponse::success(Building::all());
    })->name('api.buildings.index');

    Route::post('/', [APIBuildingController::class, 'store'])->name('api.buildings.store');

    Route::prefix('/{building}')->group(function () {

        Route::patch('/', [APIBuildingController::class, 'update'])->name('api.buildings.update');
        Route::delete('/', [APIBuildingController::class, 'destroy'])->name('api.buildings.destroy');

        Route::post('/qr/regen', function (Building $building, QRCodeService $qRCodeService) {
            if (Auth::user()->cannot('update', $building))
                return ApiResponse::notAuthorized();

            $qRCodeService->createAndAttachQR($building);
            return ApiResponse::success([], 'QR Code created');
        })->name('api.buildings.qr.regen');

        Route::get('/', function (Building $building) {
            return ApiResponse::success($building->load(['locationType', 'documents', 'maintainable.manager', 'maintainable.providers']));
        })->name('api.buildings.show');

        // Get all assets from a building
        Route::get('/assets/', function (Building $building) {
            return ApiResponse::success($building->load('assets')->assets);
        })->name('api.buildings.assets');

        Route::prefix('/documents')->group(function () {

            // Get all documents from a building
            Route::get('', function (Building $building) {
                return ApiResponse::success($building->load('documents')->documents);
            })->name('api.buildings.documents');

            // Post a new document to a building
            Route::post('', function (DocumentUploadRequest $documentUploadRequest, DocumentService $documentService, Building $building) {

                if (Auth::user()->cannot('update', $building))
                    return ApiResponse::notAuthorized();


                if ($documentUploadRequest->validated('files')) {
                    $documentService->uploadAndAttachDocuments($building, $documentUploadRequest->validated('files'));
                }

                if ($documentUploadRequest->validated('existing_documents'))
                    $documentService->attachExistingDocumentsToModel($building, $documentUploadRequest->validated('existing_documents'));

                return ApiResponse::success(null, 'Document added');
            })->name('api.buildings.documents.post');

            // Detach a document from a location
            Route::patch('', function (Building $building, Request $request) {
                $validated = $request->validateWithBag('errors', [
                    'document_id' => 'required|exists:documents,id'
                ]);

                app(DocumentService::class)->detachDocumentFromModel($building, $validated['document_id']);
                return ApiResponse::success([], 'Document removed');
            })->name('api.buildings.documents.detach');
        });




        Route::prefix('contracts')->group(function () {

            Route::get('', function (Building $building) {

                $contracts = Building::where('reference_code', $building->reference_code)->with(['contracts', 'contracts.provider'])->first()->contracts()->with('provider')->paginate();

                return ApiResponse::success($contracts ?? [], 'Contract');
            })->name('api.buildings.contracts');

            Route::post('', function (Building $building, ContractWithModelStoreRequest $contractWithModelRequest) {

                if ($contractWithModelRequest->validated('existing_contracts'))
                    app(ContractService::class)->attachExistingContractsToModel($building, $contractWithModelRequest->validated('existing_contracts'));

                return ApiResponse::success([], 'Contract(s) added');
            })->name('api.buildings.contracts.post');

            // Remove/Detach a contract 
            Route::delete('', function (Building $building, Request $request) {

                $validated = $request->validateWithBag('errors', [
                    'contract_id' => 'required|exists:contracts,id'
                ]);
                app(ContractService::class)->detachExistingContractFromModel($building, $validated['contract_id']);
                return ApiResponse::success([], 'Contract removed');
            })->name('api.buildings.contracts.delete');
        });

        // Get all tickets from a building
        Route::get('/tickets/', function (Building $building) {
            return ApiResponse::success($building->load('tickets.pictures')->tickets, 'Document added');
        })->name('api.buildings.tickets');

        // Get all interventions from a building
        Route::get('/interventions/', function (Building $building) {
            return ApiResponse::success($building->interventions()->where('ticket_id', null)->get());
        })->name('api.buildings.interventions');

        // Get all pictures from a building
        Route::get('/pictures/', function (Building $building) {
            return ApiResponse::success($building->load('pictures')->pictures, 'Pictures added');
        })->name('api.buildings.pictures');

        // Post a new picture to a building
        Route::post('/pictures/', function (PictureUploadRequest $pictureUploadRequest, PictureService $pictureService, Building $building) {

            $files = $pictureUploadRequest->validated('pictures');
            if ($files) {
                $pictureService->uploadAndAttachPictures($building, $files);
            }

            return ApiResponse::success(null, 'Pictures added');
        })->name('api.buildings.pictures.post');
    });


    // });
});
