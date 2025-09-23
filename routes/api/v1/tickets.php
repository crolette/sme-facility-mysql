<?php

use App\Helpers\ApiResponse;
use App\Models\Tenants\User;
use Illuminate\Http\Request;
use App\Models\Tenants\Ticket;
use App\Services\PictureService;
use App\Services\DocumentService;
use Barryvdh\Debugbar\Facades\Debugbar;
use Stancl\Tenancy\Middleware\ScopeSessions;
use App\Http\Controllers\API\V1\APIUserController;
use App\Http\Requests\Tenant\PictureUploadRequest;
use App\Http\Requests\Tenant\DocumentUploadRequest;
use App\Http\Controllers\API\V1\APITicketController;
use App\Http\Controllers\API\V1\APIProviderController;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use App\Http\Controllers\API\V1\APIUploadProfilePictureController;


Route::middleware([
    'web',
    InitializeTenancyBySubdomain::class,
    ScopeSessions::class,
    PreventAccessFromCentralDomains::class,
    'auth:tenant'
])->prefix('/v1/tickets')->group(function () {
    // Get all tickets
    Route::get('/', [APITicketController::class, 'index'])->name('api.tickets.index');

    Route::prefix('{ticket}')->group(function() {
        // Get a specific ticket
        Route::get('/', [APITicketController::class, 'show'])->name('api.tickets.get');
        Route::delete('/', [APITicketController::class, 'destroy'])->name('api.tickets.destroy');

        // Get all pictures from a ticket
        Route::get('/pictures/', function (Ticket $ticket) {
            return ApiResponse::success($ticket->load('pictures')->pictures);
        })->name('api.tickets.pictures');

        // Post a new picture to a ticket
        Route::post('/pictures/', function (PictureUploadRequest $pictureUploadRequest, PictureService $pictureService, Ticket $ticket) {

            $files = $pictureUploadRequest->validated('pictures');
            if ($files) {
                $pictureService->uploadAndAttachPictures($ticket, $files);
            }

            return ApiResponse::success(null, 'Pictures added');
        })->name('api.tickets.pictures.post');

        // Update a specific ticket
        Route::patch('/', [APITicketController::class, 'update'])->name('api.tickets.update');

        // Change the status of a specific ticket
        Route::patch('/status', [APITicketController::class, 'changeStatus'])->name('api.tickets.status');


        // Get all ticket related interventions
        Route::get('/interventions', function (Ticket $ticket) {
            return ApiResponse::success($ticket->load('interventions')->interventions);
        })->name('api.tickets.interventions');

    });
    

    

    // TODO are documents needed for a ticket ?
    // Get all the documents from a ticket
    // Route::get('/{ticket}/documents/', function (Ticket $ticket) {
    //     if (Auth::user()->cannot('update', $ticket))
    //         return ApiResponse::notAuthorized();

    //     return ApiResponse::success($ticket->load('documents')->documents);
    // })->name('api.tickets.documents');

    // // Post a new document to the ticket
    // Route::post('/{ticket}/documents/', function (DocumentUploadRequest $documentUploadRequest, DocumentService $documentService, Ticket $ticket) {

    //     if (Auth::user()->cannot('update', $ticket))
    //         return ApiResponse::notAuthorized();

    //     $files = $documentUploadRequest->validated('files');
    //     if ($files) {
    //         $documentService->uploadAndAttachDocuments($ticket, $files);
    //     }

    //     return ApiResponse::success([], 'Document added');
    // })->name('api.tickets.documents.post');

   

});


