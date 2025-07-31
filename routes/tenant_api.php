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
use App\Models\LocationType;
use App\Services\QRCodeService;

Route::prefix('/v1/')->group(
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


                // Get the qr code
                Route::get('/qr/show', function (Request $request) {

                    $path = $request->path;

                    if (! Storage::disk('tenants')->exists($path)) {
                        abort(404);
                    }

                    return response()->file(Storage::disk('tenants')->path($path));
                })->name('api.qr.show');

                Route::get('/qr/download', function (Request $request) {

                    $path = $request->path;

                    if (! Storage::disk('tenants')->exists($path)) {
                        abort(404);
                    }

                    return Storage::disk('tenants')->download($path);
                })->name('api.qr.download');

                Route::post('/qr/regen/{asset}', function (Asset $asset, QRCodeService $qRCodeService) {
                    $qRCodeService->createAndAttachQR($asset);
                    return ApiResponse::success();
                })->name('api.qr.regen');

                // Return the category type searched
                Route::get('category-types/', function (Request $request) {
                    $documentTypes = CategoryType::where('category', $request->query('type'))->get();
                    Debugbar::info($request->query('type'), $documentTypes);

                    return ApiResponse::success($documentTypes, 'Success');
                })->name('api.category-types');

                // Return the category location
                Route::get('location-types/', function (Request $request) {
                    $locationTypes = LocationType::where('level', $request->query('level'))->get();
                    Debugbar::info($request->query('type'), $locationTypes);

                    return ApiResponse::success($locationTypes, 'Success');
                })->name('api.location-types');

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

                    // Get all pictures from a ticket
                    Route::get('/{ticket}/pictures/', function (Ticket $ticket) {
                        Debugbar::info('ticketpictures', $ticket->load('pictures')->pictures);
                        return ApiResponse::success($ticket->load('pictures')->pictures);
                    })->name('api.tickets.pictures');

                    // Post a new picture to a ticket
                    Route::post('/{ticket}/pictures/', function (PictureUploadRequest $pictureUploadRequest, PictureService $pictureService, Ticket $ticket) {

                        $files = $pictureUploadRequest->validated('pictures');
                        if ($files) {
                            $pictureService->uploadAndAttachPictures($ticket, $files);
                        }

                        return ApiResponse::success(null, 'Pictures added');
                    })->name('api.tickets.pictures.post');

                    // Get all the documents from a ticket
                    Route::get('/{ticket}/documents/', function (Ticket $ticket) {
                        return ApiResponse::success($ticket->load('documents')->documents);
                    })->name('api.tickets.documents');

                    // Post a new document to the ticket
                    Route::post('/{ticket}/documents/', function (DocumentUploadRequest $documentUploadRequest, DocumentService $documentService, Ticket $ticket) {

                        Debugbar::info($documentUploadRequest, $documentUploadRequest->validated());
                        $files = $documentUploadRequest->validated('files');
                        if ($files) {
                            $documentService->uploadAndAttachDocuments($ticket, $files);
                        }

                        return ApiResponse::success([], 'Document added');
                    })->name('api.tickets.documents.post');

                    // Update a specific ticket
                    Route::patch('{ticket}', [APITicketController::class, 'update'])->name('api.tickets.update');

                    // Change the status of a specific ticket
                    Route::patch('{ticket}/status', [APITicketController::class, 'changeStatus'])->name('api.tickets.status');


                    // Get all ticket related interventions
                    Route::get('{ticket}/interventions', function (Ticket $ticket) {
                        return ApiResponse::success($ticket->load('interventions')->interventions);
                    })->name('api.tickets.interventions');
                });

                Route::prefix('interventions')->group(function () {
                    Route::post('/create', [APIInterventionController::class, 'store'])->name('api.interventions.store');
                    Route::patch('/{intervention}', [APIInterventionController::class, 'update'])
                        ->name('api.interventions.update');
                    Route::delete('/{intervention}', [APIInterventionController::class, 'destroy'])
                        ->name('api.interventions.destroy');

                    Route::get('/{intervention}/actions', [APIInterventionActionController::class, 'index'])->name('api.interventions.actions.index');
                    Route::post('/{intervention}/actions', [APIInterventionActionController::class, 'store'])->name('api.interventions.actions.store');
                    Route::patch('/actions/{action}', [APIInterventionActionController::class, 'update'])->name('api.interventions.actions.update');
                    Route::delete('/actions/{action}', [APIInterventionActionController::class, 'destroy'])->name('api.interventions.actions.destroy');
                });
            });
        });
    }
);

$apiPath = __DIR__ . '/api';

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($apiPath)
);

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        require $file->getPathname();
    }
}
