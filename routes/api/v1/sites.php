<?php

use App\Helpers\ApiResponse;
use App\Models\Tenants\Site;
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
])->prefix('/v1/sites')->group(
    function () {

        Route::get('/{site}', function (Site $site) {
            return ApiResponse::success($site->load(['locationType', 'documents', 'maintainable.manager', 'maintainable.providers']));
        })->name('api.site.show');

        // Get all assets from a site
        Route::get('/{site}/assets/', function (Site $site) {
            return ApiResponse::success($site->load('assets')->assets);
        })->name('api.sites.assets');

        // Get all documents from a site
        Route::get('/{site}/documents/', function (Site $site) {
            return ApiResponse::success($site->load('documents')->documents);
        })->name('api.sites.documents');

        // Post a new document to a site
        Route::post('/{site}/documents/', function (DocumentUploadRequest $documentUploadRequest, DocumentService $documentService, Site $site) {

            $files = $documentUploadRequest->validated('files');
            if ($files) {
                $documentService->uploadAndAttachDocuments($site, $files);
            }

            return ApiResponse::success(null, 'Document added');
        })->name('api.sites.documents.post');

        // Get all tickets from a site
        Route::get('/{site}/tickets/', function (Site $site) {
            return ApiResponse::success($site->load('tickets.pictures')->tickets);
        })->name('api.sites.tickets');

        // Get all interventions from a site
        Route::get('/{site}/interventions/', function (Site $site) {
            return ApiResponse::success($site->load('interventions')->interventions);
        })->name('api.sites.interventions');

        // Get all pictures from a site
        Route::get('/{site}/pictures/', function (Site $site) {
            return ApiResponse::success($site->load('pictures')->pictures);
        })->name('api.sites.pictures');

        // Post a new picture to a site
        Route::post('/{site}/pictures/', function (PictureUploadRequest $pictureUploadRequest, PictureService $pictureService, Site $site) {

            $files = $pictureUploadRequest->validated('pictures');
            if ($files) {
                $pictureService->uploadAndAttachPictures($site, $files);
            }

            return ApiResponse::success(null, 'Pictures added');
        })->name('api.sites.pictures.post');
        // });
    }
);
