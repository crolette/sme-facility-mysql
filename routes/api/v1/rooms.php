<?php

use App\Helpers\ApiResponse;
use App\Models\Tenants\Room;
use App\Models\Tenants\Site;
use Illuminate\Http\Request;
use App\Services\QRCodeService;
use App\Services\PictureService;
use App\Services\ContractService;
use App\Services\DocumentService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\V1\APIRoomController;
use App\Http\Requests\Tenant\PictureUploadRequest;
use App\Http\Requests\Tenant\DocumentUploadRequest;
use App\Http\Controllers\API\V1\RelocateRoomController;
use App\Http\Requests\Tenant\ContractWithModelStoreRequest;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;

Route::middleware([
    'web',
    InitializeTenancyBySubdomain::class,
    \Stancl\Tenancy\Middleware\ScopeSessions::class,
    \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
    'auth:tenant'
])->prefix('/v1/rooms')->group(
    function () {
        Route::get('/', function () {
            return ApiResponse::success(Room::all());
        })->name('api.rooms.index');

        Route::post('/', [APIRoomController::class, 'store'])->name('api.rooms.store');

        Route::prefix('{room}')->group(function () {
            Route::get('/', function (Room $room) {
                return ApiResponse::success($room->load(['locationType', 'documents', 'maintainable.manager', 'maintainable.providers']));
            })->name('api.rooms.show');

            Route::patch('/', [APIRoomController::class, 'update'])->name('api.rooms.update');
            Route::delete('/', [APIRoomController::class, 'destroy'])->name('api.rooms.destroy');

            Route::patch('/relocate', [RelocateRoomController::class, 'relocateRoom'])->name('api.rooms.relocate');

            Route::post('/qr/regen', function (Room $room, QRCodeService $qRCodeService) {
                if (Auth::user()->cannot('update', $room))
                    return ApiResponse::notAuthorized();

                $qRCodeService->createAndAttachQR($room);
                return ApiResponse::success();
            })->name('api.rooms.qr.regen');

            // Get all documents from a room
            Route::get('/assets/', function (Room $room) {
                return ApiResponse::success($room->load('assets')->assets);
            })->name('api.rooms.assets');

            Route::prefix('/documents')->group(function() {
                // Get all documents from a room
                Route::get('', function (Room $room) {
                    return ApiResponse::success($room->load('documents')->documents);
                })->name('api.rooms.documents');

                // Post a new document to a floor
                Route::post('', function (DocumentUploadRequest $documentUploadRequest, DocumentService $documentService, Room $room) {

                    if (Auth::user()->cannot('update', $room))
                        return ApiResponse::notAuthorized();

                    if ($documentUploadRequest->validated('files')) {
                        $documentService->uploadAndAttachDocuments($room, $documentUploadRequest->validated('files'));
                    }

                    if ($documentUploadRequest->validated('existing_documents'))
                        $documentService->attachExistingDocumentsToModel($room, $documentUploadRequest->validated('existing_documents'));

                    return ApiResponse::success(null, 'Document removed');
                })->name('api.rooms.documents.post');

                // Detach a document from a location
                Route::patch('', function (Room $room, Request $request) {
                    $validated = $request->validateWithBag('errors', [
                        'document_id' => 'required|exists:documents,id'
                    ]);

                    app(DocumentService::class)->detachDocumentFromModel($room, $validated['document_id']);
                    return ApiResponse::success([], 'Document removed');
                })->name('api.rooms.documents.detach');
            });
           

            Route::prefix('contracts')->group(function() {

                Route::get('', function (Room $room) {

                    $contracts = Room::where('reference_code', $room->reference_code)->with(['contracts', 'contracts.provider'])->first()->contracts;

                    return ApiResponse::success($contracts ?? [], 'Contract');
                })->name('api.rooms.contracts');

                Route::post('', function (Room $room, ContractWithModelStoreRequest $contractWithModelRequest) {

                    if ($contractWithModelRequest->validated('existing_contracts'))
                        app(ContractService::class)->attachExistingContractsToModel($room, $contractWithModelRequest->validated('existing_contracts'));

                    return ApiResponse::success([], 'Contract(s) added');
                })->name('api.rooms.contracts.post');

                // Remove/Detach a contract 
                Route::delete('', function (Room $room, Request $request) {
                    $validated = $request->validateWithBag('errors', [
                        'contract_id' => 'required|exists:contracts,id'
                    ]);
                    app(ContractService::class)->detachExistingContractFromModel($room, $validated['contract_id']);
                    return ApiResponse::success([], 'Contract removed');
                })->name('api.rooms.contracts.delete');

            });

            // Get all tickets from a room
            Route::get('/tickets/', function (Room $room) {
                return ApiResponse::success($room->load('tickets.pictures')->tickets);
            })->name('api.rooms.tickets');

            // Get all interventions from a room
            Route::get('/interventions/', function (Room $room) {
                return ApiResponse::success($room->interventions()->where('ticket_id', null)->get());
            })->name('api.rooms.interventions');

            // Get all pictures from a room
            Route::get('/pictures/', function (Room $room) {
                return ApiResponse::success($room->load('pictures')->pictures);
            })->name('api.rooms.pictures');

            // Post a new picture to a room
            Route::post('/pictures/', function (PictureUploadRequest $pictureUploadRequest, PictureService $pictureService, Room $room) {
                Debugbar::info('post picture to room', $pictureUploadRequest->validated('pictures'), $room);
                $files = $pictureUploadRequest->validated('pictures');
                if ($files) {
                    $pictureService->uploadAndAttachPictures($room, $files);
                }

                return ApiResponse::success(null, 'Pictures added');
            })->name('api.rooms.pictures.post');
        });


        // });
    }
);
