<?php

use App\Helpers\ApiResponse;
use App\Http\Controllers\API\V1\APIRoomController;
use App\Models\Tenants\Room;
use App\Models\Tenants\Site;
use App\Services\PictureService;
use App\Services\DocumentService;
use Illuminate\Support\Facades\Route;
use App\Http\Requests\Tenant\PictureUploadRequest;
use App\Http\Requests\Tenant\DocumentUploadRequest;
use App\Http\Controllers\API\V1\RelocateRoomController;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;

Route::middleware([
    'web',
    InitializeTenancyBySubdomain::class,
    \Stancl\Tenancy\Middleware\ScopeSessions::class,
    \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
    'auth:tenant'
])->prefix('/v1/rooms')->group(
    function () {

        Route::post('/', [APIRoomController::class, 'store'])->name('api.rooms.store');

        Route::prefix('{room}')->group(function () {
            Route::get('/', function (Room $room) {
                return ApiResponse::success($room->load(['locationType', 'documents', 'maintainable.manager', 'maintainable.providers']));
            })->name('api.rooms.show');

            Route::patch('/', [APIRoomController::class, 'update'])->name('api.rooms.update');
            Route::delete('/', [APIRoomController::class, 'destroy'])->name('api.rooms.destroy');

            Route::patch('/relocate', [RelocateRoomController::class, 'relocateRoom'])->name('api.rooms.relocate');

            // Get all documents from a room
            Route::get('/assets/', function (Room $room) {
                return ApiResponse::success($room->load('assets')->assets);
            })->name('api.rooms.assets');


            // Get all documents from a room
            Route::get('/documents/', function (Room $room) {
                return ApiResponse::success($room->load('documents')->documents);
            })->name('api.rooms.documents');

            // Post a new document to a floor
            Route::post('/documents/', function (DocumentUploadRequest $documentUploadRequest, DocumentService $documentService, Room $room) {
                $files = $documentUploadRequest->validated('files');
                if ($files) {
                    $documentService->uploadAndAttachDocuments($room, $files);
                    return ApiResponse::success(null, 'Document added');
                } else {
                    return ApiResponse::error('Error posting new documents');
                }
            })->name('api.rooms.documents.post');

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
