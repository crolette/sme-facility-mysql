<?php

use App\Helpers\ApiResponse;
use App\Http\Controllers\API\V1\APIInterventionActionController;
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
use App\Http\Controllers\API\V1\ApiSearchTrashedAssetController;
use App\Http\Controllers\API\V1\Tickets\InterventionForLocationController;
use App\Http\Controllers\Tenants\RestoreSoftDeletedAssetController;

Route::middleware([
    'web',
    InitializeTenancyBySubdomain::class,
    \Stancl\Tenancy\Middleware\ScopeSessions::class,
    \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
    'auth:tenant'
])->prefix('/v1/floors')->group(function () {

    // Route::middleware(['auth'])->group(function () {

    Route::prefix('/{floor}')->group(function () {

        Route::get('/', function (Floor $floor) {
            return ApiResponse::success($floor->load(['locationType', 'documents', 'maintainable.manager', 'maintainable.providers']));
        })->name('api.floors.show');

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
            return ApiResponse::success($floor->load('interventions')->interventions);
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
