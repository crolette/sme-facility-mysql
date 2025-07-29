<?php

use App\Helpers\ApiResponse;
use App\Models\Tenants\Building;
use App\Services\PictureService;
use App\Services\DocumentService;
use Illuminate\Support\Facades\Route;
use App\Http\Requests\Tenant\PictureUploadRequest;
use App\Http\Requests\Tenant\DocumentUploadRequest;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;

Route::middleware([
    'web',
    InitializeTenancyBySubdomain::class,
    \Stancl\Tenancy\Middleware\ScopeSessions::class,
    \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
    'auth:tenant'
])->prefix('/v1/buildings')->group(function () {

    // Route::middleware(['auth'])->group(function () {

    Route::prefix('/{building}')->group(function () {

        // Get all assets from a building
        Route::get('/assets/', function (Building $building) {
            return ApiResponse::success($building->load('assets')->assets);
        })->name('api.buildings.assets');

        // Get all documents from a building
        Route::get('/documents/', function (Building $building) {
            return ApiResponse::success($building->load('documents')->documents);
        })->name('api.buildings.documents');

        // Post a new document to a building
        Route::post('/documents/', function (DocumentUploadRequest $documentUploadRequest, DocumentService $documentService, Building $building) {

            $files = $documentUploadRequest->validated('files');
            if ($files) {
                $documentService->uploadAndAttachDocuments($building, $files);
            }

            return ApiResponse::success(null, 'Document added');
        })->name('api.buildings.documents.post');


        // Get all tickets from a building
        Route::get('/tickets/', function (Building $building) {
            return ApiResponse::success($building->load('tickets.pictures')->tickets, 'Document added');
        })->name('api.buildings.tickets');

        // Get all interventions from a building
        Route::get('/interventions/', function (Building $building) {
            return ApiResponse::success($building->load('interventions')->interventions);
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
