<?php

use App\Helpers\ApiResponse;
use App\Models\Tenants\User;
use Illuminate\Http\Request;
use App\Models\Tenants\Ticket;
use App\Services\PictureService;
use App\Services\DocumentService;
use App\Models\Tenants\Intervention;
use Barryvdh\Debugbar\Facades\Debugbar;
use Stancl\Tenancy\Middleware\ScopeSessions;
use App\Http\Controllers\API\V1\APIUserController;
use App\Http\Requests\Tenant\PictureUploadRequest;
use App\Http\Requests\Tenant\DocumentUploadRequest;
use App\Http\Controllers\API\V1\APITicketController;
use App\Http\Controllers\API\V1\APIProviderController;
use App\Http\Controllers\API\V1\APIInterventionController;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;
use App\Http\Controllers\Tenants\SendInterventionController;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use App\Http\Controllers\API\V1\APIInterventionActionController;
use App\Http\Controllers\API\V1\APIUploadProfilePictureController;

Route::middleware([
    'web',
    InitializeTenancyBySubdomain::class,
    ScopeSessions::class,
    PreventAccessFromCentralDomains::class,
    'auth:tenant'
])->prefix('/v1/interventions')->group(function () {
    // Get all tickets


    Route::post('/create', [APIInterventionController::class, 'store'])->name('api.interventions.store');

    Route::prefix('/{intervention}')->group(function() {

        Route::patch('/', [APIInterventionController::class, 'update'])
        ->name('api.interventions.update');
        Route::delete('/', [APIInterventionController::class, 'destroy'])
        ->name('api.interventions.destroy');
        
        Route::get('/actions', [APIInterventionActionController::class, 'index'])->name('api.interventions.actions.index');
        Route::post('/actions', [APIInterventionActionController::class, 'store'])->name('api.interventions.actions.store');

        Route::post('/send-provider', [SendInterventionController::class, 'store'])->name('api.interventions.send-provider');

        Route::get('providers', function(Intervention $intervention) {

            $providers = $intervention->interventionable->maintainable->providers;

            return ApiResponse::success($providers, 'Providers');
        })->name('api.interventions.providers');
    });
    
    Route::patch('/actions/{action}', [APIInterventionActionController::class, 'update'])->name('api.interventions.actions.update');
    Route::delete('/actions/{action}', [APIInterventionActionController::class, 'destroy'])->name('api.interventions.actions.destroy');
    

   

});


