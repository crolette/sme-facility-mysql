<?php

use App\Helpers\ApiResponse;
use App\Models\Tenants\Site;
use Illuminate\Http\Request;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Floor;
use App\Models\Tenants\Document;
use App\Models\Central\CategoryType;
use Illuminate\Support\Facades\Route;
use Barryvdh\Debugbar\Facades\Debugbar;
use App\Http\Controllers\API\V1\UpdateDocumentController;
use App\Http\Controllers\API\V1\DestroyDocumentController;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubDomain;
use App\Http\Controllers\API\V1\ApiSearchLocationController;
use App\Http\Controllers\API\V1\ApiSearchTrashedAssetController;
use App\Http\Requests\Tenant\DocumentUploadRequest;
use App\Models\Tenants\Building;
use App\Services\DocumentService;

Route::prefix('api/v1')->group(
    function () {
        Route::middleware([
            'web',

            InitializeTenancyBySubDomain::class,
            \Stancl\Tenancy\Middleware\ScopeSessions::class,
            \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
            'auth:tenant'
        ])->group(function () {

            Route::middleware(['auth'])->group(function () {
                Route::get('/locations', [ApiSearchLocationController::class, 'index'])->name('api.locations');
                Route::get('/assets/trashed', [ApiSearchTrashedAssetController::class, 'index'])->name('api.assets.trashed');


                // Get all the documents from an asset
                Route::get('/assets/{asset}/documents/', function (Asset $asset) {
                    return ApiResponse::success($asset->load('documents')->documents);
                })->name('api.assets.documents');

                Route::post('/assets/{asset}/documents/', function (DocumentUploadRequest $documentUploadRequest, DocumentService $documentService, Asset $asset) {

                    Debugbar::info($documentUploadRequest, $documentUploadRequest->validated());
                    $files = $documentUploadRequest->validated('files');
                    if ($files) {
                        $documentService->uploadAndAttachDocuments($asset, $files);
                    }

                    return ApiResponse::success([], 'Document added');
                })->name('api.assets.documents.post');


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

                    return ApiResponse::success($documentTypes, 'Success');
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
                })->name('api.documents.show');

                // Get the path to a specific picture through the guard tenant
                Route::get('/pictures/{picture}', function (Picture $picture) {

                    $path = $picture->path;

                    if (! Storage::disk('tenants')->exists($path)) {
                        abort(404);
                    }

                    return response()->file(Storage::disk('tenants')->path($path));
                })->name('api.pictures.show');

                // Delete a specific picture
                Route::delete('/pictures/{picture}', [DestroyPictureController::class, 'destroy'])->name('api.pictures.delete');

                Route::prefix('tickets')->group(function () {
                    // Get all tickets
                    Route::get('/', [APITicketController::class, 'index'])->name('api.tickets.index');

                    // Post a new ticket
                    Route::post('/', [APITicketController::class, 'store'])->name('api.tickets.store');

                    // Get a specific ticket
                    Route::get('{ticket}', [APITicketController::class, 'show'])->name('api.tickets.get');

                    // Update a specific ticket
                    Route::patch('{ticket}', [APITicketController::class, 'update'])->name('api.tickets.update');

                    // Close a specific ticket
                    Route::patch('{ticket}/close', [APITicketController::class, 'close'])->name('api.tickets.close');
                });
            });
        });
    }
);
