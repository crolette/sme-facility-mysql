<?php

use App\Helpers\ApiResponse;
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
use App\Http\Controllers\API\V1\DestroyDocumentController;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;
use App\Http\Controllers\API\V1\ApiSearchLocationController;
use App\Http\Controllers\API\V1\ApiSearchTrashedAssetController;

Route::prefix('api/v1')->group(
    function () {
        Route::middleware([
            'web',

            InitializeTenancyBySubdomain::class,
            \Stancl\Tenancy\Middleware\ScopeSessions::class,
            \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
            'auth:tenant'
        ])->group(function () {

            Route::middleware(['auth'])->group(function () {
                Route::get('/locations', [ApiSearchLocationController::class, 'index'])->name('api.locations');
                Route::get('/assets/trashed', [ApiSearchTrashedAssetController::class, 'index'])->name('api.assets.trashed');


                // Get all the documents from an asset
                Route::get('/assets/{asset}/documents/', function (Asset $asset) {
                    return response()->json($asset->load('documents')->documents);
                })->name('api.assets.documents');

                Route::post('/assets/{asset}/documents/', function (DocumentUploadRequest $documentUploadRequest, DocumentService $documentService, Asset $asset) {

                    Debugbar::info($documentUploadRequest, $documentUploadRequest->validated());
                    $files = $documentUploadRequest->validated('files');
                    if ($files) {
                        $documentService->uploadAndAttachDocuments($asset, $files);
                    }

                    return response()->json($asset->load('documents')->documents);
                })->name('api.assets.documents.post');

                Route::get('/assets/{asset}/pictures/', function (Asset $asset) {
                    return response()->json($asset->load('pictures')->pictures);
                })->name('api.assets.pictures');

                Route::get('/assets/{asset}/tickets/', function (Asset $asset) {
                    return response()->json($asset->load('tickets.pictures')->tickets);
                })->name('api.assets.tickets');

                Route::post('/assets/{asset}/pictures/', function (PictureUploadRequest $pictureUploadRequest, PictureService $pictureService, Asset $asset) {

                    $files = $pictureUploadRequest->validated('pictures');
                    if ($files) {
                        $pictureService->uploadAndAttachPictures($asset, $files);
                    }

                    return ApiResponse::success(null, 'Pictures added');
                })->name('api.assets.pictures.post');


                Route::get('/sites/{site}/documents/', function (Site $site) {
                    return response()->json($site->load('documents')->documents);
                })->name('api.sites.documents');

                Route::get('/buildings/{building}/documents/', function (Building $building) {
                    return response()->json($building->load('documents')->documents);
                })->name('api.buildings.documents');

                Route::get('/floors/{floor}/documents/', function (Floor $floor) {
                    return response()->json($floor->load('documents')->documents);
                })->name('api.floors.documents');

                Route::get('/rooms/{room}/documents/', function (Room $room) {
                    return response()->json($room->load('documents')->documents);
                })->name('api.rooms.documents');


                // Return the category type searched
                Route::get('category-types/', function (Request $request) {
                    $documentTypes = CategoryType::where('category', $request->query('type'))->get();
                    Debugbar::info($request->query('type'), $documentTypes);

                    return response()->json($documentTypes);
                });

                // Route to get the documents from a tenant - to display on show page
                Route::patch('/documents/{document}', [UpdateDocumentController::class, 'update'])->name('api.documents.update');

                Route::delete('/documents/{document}', [DestroyDocumentController::class, 'destroy'])->name('api.documents.delete');

                Route::get('/documents/{document}', function (Document $document) {

                    $path = $document->path;

                    if (! Storage::disk('tenants')->exists($path)) {
                        abort(404);
                    }

                    return response()->file(Storage::disk('tenants')->path($path));
                })->name('documents.show');

                Route::get('/pictures/{picture}', function (Picture $picture) {

                    $path = $picture->path;

                    if (! Storage::disk('tenants')->exists($path)) {
                        abort(404);
                    }

                    return response()->file(Storage::disk('tenants')->path($path));
                })->name('pictures.show');

                Route::delete('/pictures/{picture}', [DestroyPictureController::class, 'destroy'])->name('api.picture.delete');

                Route::post('tickets', [APITicketController::class, 'store'])->name('api.tickets.store');

                Route::get('/tickets/{ticket}', function (Ticket $ticket) {
                    Debugbar::info($ticket);
                    return ApiResponse::success($ticket->load('pictures'), 'Ticket');
                })->name('api.tickets.get');

                Route::patch('tickets/{ticket}', [APITicketController::class, 'update'])->name('api.tickets.update');

                Route::patch('tickets/{ticket}/close', [APITicketController::class, 'close'])->name('api.tickets.close');
            });
        });
    }
);
