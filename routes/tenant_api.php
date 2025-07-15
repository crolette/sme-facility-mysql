<?php

use App\Helpers\ApiResponse;
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
use App\Http\Controllers\API\V1\DestroyDocumentController;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;
use App\Http\Controllers\API\V1\ApiSearchLocationController;
use App\Http\Controllers\Tenants\ForceDeleteAssetController;
use App\Http\Controllers\API\V1\ApiSearchTrashedAssetController;
use App\Http\Controllers\Tenants\RestoreSoftDeletedAssetController;

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

                // Post a new document to the assets
                Route::post('/assets/{asset}/documents/', function (DocumentUploadRequest $documentUploadRequest, DocumentService $documentService, Asset $asset) {

                    Debugbar::info($documentUploadRequest, $documentUploadRequest->validated());
                    $files = $documentUploadRequest->validated('files');
                    if ($files) {
                        $documentService->uploadAndAttachDocuments($asset, $files);
                    }

                    return response()->json($asset->load('documents')->documents);
                })->name('api.assets.documents.post');

                // Get all pictures from an asset
                Route::get('/assets/{asset}/pictures/', function (Asset $asset) {
                    return response()->json($asset->load('pictures')->pictures);
                })->name('api.assets.pictures');

                // Get all tickets from an asset
                Route::get('/assets/{asset}/tickets/', function (Asset $asset) {
                    return response()->json($asset->load('tickets.pictures')->tickets);
                })->name('api.assets.tickets');

                // Post a new picture to an asset
                Route::post('/assets/{asset}/pictures/', function (PictureUploadRequest $pictureUploadRequest, PictureService $pictureService, Asset $asset) {

                    $files = $pictureUploadRequest->validated('pictures');
                    if ($files) {
                        $pictureService->uploadAndAttachPictures($asset, $files);
                    }

                    return ApiResponse::success(null, 'Pictures added');
                })->name('api.assets.pictures.post');

                Route::post('assets/{assetId}/restore', [RestoreSoftDeletedAssetController::class, 'restore'])->name('api.tenant.assets.restore');
                Route::delete('assets/{assetId}/force', [ForceDeleteAssetController::class, 'forceDelete'])->name('api.tenant.assets.force');


                // Get all documents from a site
                Route::get('/sites/{site}/documents/', function (Site $site) {
                    return response()->json($site->load('documents')->documents);
                })->name('api.sites.documents');

                // Post a new document to a site
                Route::post('/sites/{site}/documents/', function (DocumentUploadRequest $documentUploadRequest, DocumentService $documentService, Site $site) {

                    $files = $documentUploadRequest->validated('files');
                    if ($files) {
                        $documentService->uploadAndAttachDocuments($site, $files);
                    }

                    return ApiResponse::success(null, 'Document added');
                })->name('api.sites.documents.post');

                // Get all tickets from a site
                Route::get('/sites/{site}/tickets/', function (Site $site) {
                    return ApiResponse::success($site->load('tickets.pictures')->tickets, 'Document added');
                })->name('api.sites.tickets');


                // Get all documents from a building
                Route::get('/buildings/{building}/documents/', function (Building $building) {
                    return response()->json($building->load('documents')->documents);
                })->name('api.buildings.documents');

                // Post a new document to a building
                Route::post('/buildings/{building}/documents/', function (DocumentUploadRequest $documentUploadRequest, DocumentService $documentService, Building $building) {

                    $files = $documentUploadRequest->validated('files');
                    if ($files) {
                        $documentService->uploadAndAttachDocuments($building, $files);
                    }

                    return ApiResponse::success(null, 'Document added');
                })->name('api.buildings.documents.post');


                // Get all tickets from a building
                Route::get('/buildings/{building}/tickets/', function (Building $building) {
                    return ApiResponse::success($building->load('tickets.pictures')->tickets, 'Document added');
                })->name('api.buildings.tickets');


                // Get all documents from a floor
                Route::get('/floors/{floor}/documents/', function (Floor $floor) {
                    return response()->json($floor->load('documents')->documents);
                })->name('api.floors.documents');

                // Post a new document to a floor
                Route::post('/floors/{floor}/documents/', function (DocumentUploadRequest $documentUploadRequest, DocumentService $documentService, Floor $floor) {

                    $files = $documentUploadRequest->validated('files');
                    if ($files) {
                        $documentService->uploadAndAttachDocuments($floor, $files);
                    }

                    return ApiResponse::success(null, 'Document added');
                })->name('api.floors.documents.post');


                // Get all tickets from a floor
                Route::get('/floors/{floor}/tickets/', function (Floor $floor) {
                    return ApiResponse::success($floor->load('tickets.pictures')->tickets, 'Document added');
                })->name('api.floors.tickets');


                // Get all documents from a room
                Route::get('/rooms/{room}/documents/', function (Room $room) {
                    return response()->json($room->load('documents')->documents);
                })->name('api.rooms.documents');

                // Post a new document to a floor
                Route::post('/rooms/{room}/documents/', function (DocumentUploadRequest $documentUploadRequest, DocumentService $documentService, Room $room) {

                    $files = $documentUploadRequest->validated('files');
                    if ($files) {
                        $documentService->uploadAndAttachDocuments($room, $files);
                    }

                    return ApiResponse::success(null, 'Document added');
                })->name('api.rooms.documents.post');

                // Get all tickets from a room
                Route::get('/rooms/{room}/tickets/', function (Room $room) {
                    return ApiResponse::success($room->load('tickets.pictures')->tickets, 'Document added');
                })->name('api.rooms.tickets');

                // Return the category type searched
                Route::get('category-types/', function (Request $request) {
                    $documentTypes = CategoryType::where('category', $request->query('type'))->get();
                    Debugbar::info($request->query('type'), $documentTypes);

                    return response()->json($documentTypes);
                });

                // Route to get the documents from a tenant - to display on show page
                Route::patch('/documents/{document}', [UpdateDocumentController::class, 'update'])->name('api.documents.update');

                // Delete a specific document
                Route::delete('/documents/{document}', [DestroyDocumentController::class, 'destroy'])->name('api.documents.delete');

                // Get the path to a specific document through the guard tenant
                Route::get('/documents/{document}', function (Document $document) {

                    $path = $document->path;

                    if (! Storage::disk('tenants')->exists($path)) {
                        abort(404);
                    }

                    return response()->file(Storage::disk('tenants')->path($path));
                })->name('documents.show');

                // Get the path to a specific picture through the guard tenant
                Route::get('/pictures/{picture}', function (Picture $picture) {

                    $path = $picture->path;

                    if (! Storage::disk('tenants')->exists($path)) {
                        abort(404);
                    }

                    return response()->file(Storage::disk('tenants')->path($path));
                })->name('pictures.show');

                // Delete a specific picture
                Route::delete('/pictures/{picture}', [DestroyPictureController::class, 'destroy'])->name('api.picture.delete');

                // Post a new ticket
                Route::post('tickets', [APITicketController::class, 'store'])->name('api.tickets.store');

                // Get all tickets
                Route::get('/tickets/', function () {
                    return ApiResponse::success(Ticket::all()->load('pictures'));
                })->name('api.tickets.all');

                // Get a specific ticket
                Route::get('/tickets/{ticket}', function (Ticket $ticket) {
                    Debugbar::info($ticket);
                    return ApiResponse::success($ticket->load('pictures'), 'Ticket');
                })->name('api.tickets.get');

                // Update a specific ticket
                Route::patch('tickets/{ticket}', [APITicketController::class, 'update'])->name('api.tickets.update');

                // Close a specific ticket
                Route::patch('tickets/{ticket}/close', [APITicketController::class, 'close'])->name('api.tickets.close');
            });
        });
    }
);
