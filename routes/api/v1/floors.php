<?php

use App\Helpers\ApiResponse;
use App\Models\Tenants\Floor;
use App\Services\QRCodeService;
use App\Services\PictureService;
use App\Services\DocumentService;
use Illuminate\Support\Facades\Route;
use App\Http\Requests\Tenant\PictureUploadRequest;
use App\Http\Controllers\API\V1\APIFloorController;
use App\Http\Requests\Tenant\DocumentUploadRequest;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;

Route::middleware([
    'web',
    InitializeTenancyBySubdomain::class,
    \Stancl\Tenancy\Middleware\ScopeSessions::class,
    \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
    'auth:tenant'
])->prefix('/v1/floors')->group(function () {

    Route::post('/', [APIFloorController::class, 'store'])->name('api.floors.store');


    Route::prefix('/{floor}')->group(function () {

        Route::get('/', function (Floor $floor) {
            return ApiResponse::success($floor->load(['locationType', 'documents', 'maintainable.manager', 'maintainable.providers']));
        })->name('api.floors.show');

        Route::patch('/', [APIFloorController::class, 'update'])->name('api.floors.update');
        Route::delete('/', [APIFloorController::class, 'destroy'])->name('api.floors.destroy');

        Route::post('/qr/regen', function (Floor $floor, QRCodeService $qRCodeService) {
            $qRCodeService->createAndAttachQR($floor);
            return ApiResponse::success();
        })->name('api.floors.qr.regen');

        // Get all assets from a floor
        Route::get('/assets/', function (Floor $floor) {
            return ApiResponse::success($floor->load('assets')->assets);
        })->name('api.floors.assets');

        // Get all documents from a floor
        Route::get('/documents/', function (Floor $floor) {
            return ApiResponse::success($floor->load('documents')->documents);
        })->name('api.floors.documents');

        // Post a new document to a floor
        Route::post('/documents/', function (DocumentUploadRequest $documentUploadRequest, DocumentService $documentService, Floor $floor) {

            $files = $documentUploadRequest->validated('files');
            if ($files) {
                $documentService->uploadAndAttachDocuments($floor, $files);
            }

            return ApiResponse::success(null, 'Document added');
        })->name('api.floors.documents.post');


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
