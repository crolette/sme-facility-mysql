<?php

use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Models\Tenants\Floor;
use App\Services\QRCodeService;
use App\Services\PictureService;
use App\Services\ContractService;
use App\Services\DocumentService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Requests\Tenant\PictureUploadRequest;
use App\Http\Controllers\API\V1\APIFloorController;
use App\Http\Requests\Tenant\DocumentUploadRequest;
use App\Http\Requests\Tenant\ContractWithModelStoreRequest;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;

Route::middleware([
    'web',
    InitializeTenancyBySubdomain::class,
    \Stancl\Tenancy\Middleware\ScopeSessions::class,
    \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
    'auth:tenant'
])->prefix('/v1/floors')->group(function () {
    Route::get('/', function (Request $request) {

        if ($request->building) {
            $floors = Floor::where('level_id', $request->building)->get();
            return ApiResponse::success($floors);
        }


        return ApiResponse::success(Floor::all());
    })->name('api.floors.index');

    Route::post('/', [APIFloorController::class, 'store'])->name('api.floors.store');

    Route::prefix('/{floor}')->group(function () {

        Route::get('/', function (Floor $floor) {
            return ApiResponse::success($floor->load(['locationType', 'documents', 'maintainable.manager', 'maintainable.providers']));
        })->name('api.floors.show');

        Route::patch('/', [APIFloorController::class, 'update'])->name('api.floors.update');
        Route::delete('/', [APIFloorController::class, 'destroy'])->name('api.floors.destroy');

        Route::post('/qr/regen', function (Floor $floor, QRCodeService $qRCodeService) {
            if (Auth::user()->cannot('update', $floor))
                return ApiResponse::notAuthorized();

            $qRCodeService->createAndAttachQR($floor);
            return ApiResponse::success([], 'QR Code created');
        })->name('api.floors.qr.regen');

        // Get all assets from a floor
        Route::get('/assets/', function (Floor $floor) {
            return ApiResponse::success($floor->load('assets')->assets);
        })->name('api.floors.assets');

        Route::prefix('/documents')->group(function () {
            // Get all documents from a floor
            Route::get('', function (Floor $floor) {
                return ApiResponse::success($floor->load('documents')->documents);
            })->name('api.floors.documents');

            // Post a new document to a floor
            Route::post('', function (DocumentUploadRequest $documentUploadRequest, DocumentService $documentService, Floor $floor) {

                if (Auth::user()->cannot('update', $floor))
                    return ApiResponse::notAuthorized();

                if ($documentUploadRequest->validated('files')) {
                    $documentService->uploadAndAttachDocuments($floor, $documentUploadRequest->validated('files'));
                }

                if ($documentUploadRequest->validated('existing_documents'))
                    $documentService->attachExistingDocumentsToModel($floor, $documentUploadRequest->validated('existing_documents'));

                return ApiResponse::success(null, 'Document added');
            })->name('api.floors.documents.post');

            // Detach a document from a location
            Route::patch('', function (Floor $floor, Request $request) {
                $validated = $request->validateWithBag('errors', [
                    'document_id' => 'required|exists:documents,id'
                ]);

                app(DocumentService::class)->detachDocumentFromModel($floor, $validated['document_id']);
                return ApiResponse::success([], 'Document removed');
            })->name('api.floors.documents.detach');
        });


        Route::prefix('contracts')->group(function () {

            Route::get('', function (Floor $floor) {

                $contracts = Floor::where('reference_code', $floor->reference_code)->with(['contracts', 'contracts.provider'])->first()->contracts()->with('provider')->paginate();

                return ApiResponse::success($contracts ?? [], 'Contract');
            })->name('api.floors.contracts');

            Route::post('', function (Floor $floor, ContractWithModelStoreRequest $contractWithModelRequest) {

                if ($contractWithModelRequest->validated('existing_contracts'))
                    app(ContractService::class)->attachExistingContractsToModel($floor, $contractWithModelRequest->validated('existing_contracts'));

                return ApiResponse::success([], 'Contract(s) added');
            })->name('api.floors.contracts.post');

            // Remove/Detach a contract 
            Route::delete('', function (Floor $floor, Request $request) {
                $validated = $request->validateWithBag('errors', [
                    'contract_id' => 'required|exists:contracts,id'
                ]);
                app(ContractService::class)->detachExistingContractFromModel($floor, $validated['contract_id']);
                return ApiResponse::success([], 'Contract removed');
            })->name('api.floors.contracts.delete');
        });

        // Get all tickets from a floor
        Route::get('/tickets/', function (Floor $floor) {
            return ApiResponse::success($floor->load('tickets.pictures')->tickets);
        })->name('api.floors.tickets');

        // Get all interventions from a floor
        Route::get('/interventions/', function (Floor $floor) {
            return ApiResponse::success($floor->interventions()->where('ticket_id', null)->get());
        })->name('api.floors.interventions');

        // Get all pictures from a floor
        Route::get('/pictures/', function (Floor $floor) {
            return ApiResponse::success($floor->load('pictures')->pictures, 'Pictures added');
        })->name('api.floors.pictures');

        // Post a new picture to a floor
        Route::post('/pictures/', function (PictureUploadRequest $pictureUploadRequest, PictureService $pictureService, Floor $floor) {

            $files = $pictureUploadRequest->validated('pictures');
            if ($files) {
                $pictureService->uploadAndAttachPictures($floor, $files);
            }

            return ApiResponse::success(null, 'Pictures added');
        })->name('api.floors.pictures.post');
    });


    // });
});
