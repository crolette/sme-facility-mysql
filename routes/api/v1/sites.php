<?php

use App\Helpers\ApiResponse;
use App\Models\Tenants\Site;
use Illuminate\Http\Request;
use App\Services\QRCodeService;
use App\Services\PictureService;
use App\Services\ContractService;
use App\Services\DocumentService;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\V1\APISiteController;
use App\Http\Requests\Tenant\PictureUploadRequest;
use App\Http\Requests\Tenant\DocumentUploadRequest;
use App\Http\Requests\Tenant\ContractWithModelStoreRequest;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;

Route::middleware([
    'web',
    InitializeTenancyBySubdomain::class,
    \Stancl\Tenancy\Middleware\ScopeSessions::class,
    \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
    'auth:tenant'
])->prefix('/v1/sites')->group(
    function () {
        Route::get('/', function () {
            return ApiResponse::success(Site::all());
        })->name('api.sites.index');

        Route::post('/', [APISiteController::class, 'store'])->name('api.sites.store');

        Route::prefix('{site}')->group(function () {
            Route::patch('/', [APISiteController::class, 'update'])->name('api.sites.update');
            Route::delete('/', [APISiteController::class, 'destroy'])->name('api.sites.destroy');

            Route::post('/qr/regen', function (Site $site, QRCodeService $qRCodeService) {
                $qRCodeService->createAndAttachQR($site);
                return ApiResponse::success();
            })->name('api.sites.qr.regen');

            Route::get('/', function (Site $site) {
                if (Auth::user()->cannot('view', $site))
                    return ApiResponse::notAuthorized();

                return ApiResponse::success($site->load(['locationType', 'documents', 'maintainable.manager', 'maintainable.providers']));
            })->name('api.sites.show');

            // Get all assets from a site
            Route::get('/assets/', function (Site $site) {
                if (Auth::user()->cannot('view', $site))
                    return ApiResponse::notAuthorized();
                return ApiResponse::success($site->load('assets')->assets);
            })->name('api.sites.assets');

            // Get all documents from a site
            Route::get('/documents/', function (Site $site) {
                if (Auth::user()->cannot('view', $site))
                    return ApiResponse::notAuthorized();

                return ApiResponse::success($site->load('documents')->documents);
            })->name('api.sites.documents');

            // Post a new document to a site
            Route::post('/documents/', function (DocumentUploadRequest $documentUploadRequest, DocumentService $documentService, Site $site) {
                if (Auth::user()->cannot('view', $site))
                    return ApiResponse::notAuthorized();

                $files = $documentUploadRequest->validated('files');
                if ($files) {
                    $documentService->uploadAndAttachDocuments($site, $files);
                }

                return ApiResponse::success(null, 'Document added');
            })->name('api.sites.documents.post');

            Route::prefix('contracts')->group(function () {

                Route::get('', function (Site $site) {

                    $contracts = Site::where('reference_code', $site->reference_code)->with(['contracts', 'contracts.provider'])->first()->contracts;

                    return ApiResponse::success($contracts ?? [], 'Contract');
                })->name('api.sites.contracts');

                Route::post('', function (Site $site, ContractWithModelStoreRequest $contractWithModelRequest) {

                    if ($contractWithModelRequest->validated('existing_contracts'))
                        app(ContractService::class)->attachExistingContractsToModel($site, $contractWithModelRequest->validated('existing_contracts'));

                    return ApiResponse::success([], 'Contract(s) added');
                })->name('api.sites.contracts.post');

                // Remove/Detach a contract 
                Route::delete('', function (Site $site, Request $request) {
                    $validated = $request->validateWithBag('errors', [
                        'contract_id' => 'required|exists:contracts,id'
                    ]);
                    app(ContractService::class)->detachExistingContractFromModel($site, $validated['contract_id']);
                    return ApiResponse::success([], 'Contract removed');
                })->name('api.sites.contracts.delete');
            });


            // Get all tickets from a site
            Route::get('/tickets/', function (Site $site) {
                if (Auth::user()->cannot('view', $site))
                    return ApiResponse::notAuthorized();

                return ApiResponse::success($site->load('tickets.pictures')->tickets);
            })->name('api.sites.tickets');

            // Get all interventions from a site
            Route::get('/interventions/', function (Site $site) {
                if (Auth::user()->cannot('view', $site))
                    return ApiResponse::notAuthorized();

                return ApiResponse::success($site->interventions()->where('ticket_id', null)->get());
            })->name('api.sites.interventions');

            // Get all pictures from a site
            Route::get('/pictures/', function (Site $site) {
                if (Auth::user()->cannot('view', $site))
                    return ApiResponse::notAuthorized();

                return ApiResponse::success($site->load('pictures')->pictures);
            })->name('api.sites.pictures');

            // Post a new picture to a site
            Route::post('/pictures/', function (PictureUploadRequest $pictureUploadRequest, PictureService $pictureService, Site $site) {
                if (Auth::user()->cannot('view', $site))
                    return ApiResponse::notAuthorized();

                $files = $pictureUploadRequest->validated('pictures');
                if ($files) {
                    $pictureService->uploadAndAttachPictures($site, $files);
                }

                return ApiResponse::success(null, 'Pictures added');
            })->name('api.sites.pictures.post');
        });


        // });
    }
);
