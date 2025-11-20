<?php

use App\Helpers\ApiResponse;
use App\Models\Tenants\User;
use Illuminate\Http\Request;
use App\Models\Tenants\Ticket;
use App\Models\Tenants\Provider;
use App\Services\PictureService;
use App\Enums\InterventionStatus;
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

    Route::post('/create', [APIInterventionController::class, 'store'])->name('api.interventions.store');

    Route::prefix('/{intervention}')->group(function () {
        Route::get('/', [APIInterventionController::class, 'show'])->name('api.interventions.show');
        Route::patch('/', [APIInterventionController::class, 'update'])
            ->name('api.interventions.update');
        Route::delete('/', [APIInterventionController::class, 'destroy'])
            ->name('api.interventions.destroy');

        Route::get('/pictures/', function (Intervention $intervention) {
            $intervention = Intervention::find($intervention->id)->with('pictures')->first();
            return ApiResponse::success($intervention->pictures);
        })->name('api.interventions.pictures');

        Route::post('/pictures/', function (PictureUploadRequest $pictureUploadRequest, PictureService $pictureService, Intervention $intervention) {

            $files = $pictureUploadRequest->validated('pictures');
            if ($files) {
                $pictureService->uploadAndAttachPictures($intervention, $files);
            }

            return ApiResponse::success(null, 'Pictures added');
        })->name('api.interventions.pictures.post');

        Route::patch('/status/', function (Intervention $intervention, Request $request) {

            Debugbar::info($request->status);

            if (in_array($request->status, array_column(InterventionStatus::cases(), 'value'))) {
                $intervention->update(['status' => $request->status]);
                return ApiResponse::success(null, 'Status updated');
            } else {
                return ApiResponse::error('Unknow status');
            }
        })->name('api.interventions.status');

        Route::get('/actions', [APIInterventionActionController::class, 'index'])->name('api.interventions.actions.index');
        Route::post('/actions', [APIInterventionActionController::class, 'store'])->name('api.interventions.actions.store');

        Route::post('/send-provider', [SendInterventionController::class, 'store'])->name('api.interventions.send-provider');

        Route::get('providers', function (Intervention $intervention) {

            if ($intervention->interventionable_type === Provider::class) {
                return ApiResponse::success([$intervention->interventionable->load('users')]);
            }

            $providers = $intervention->interventionable->maintainable?->providers?->load('users');

            return ApiResponse::success($providers ?? [], 'Providers');
        })->name('api.interventions.providers');
    });

    Route::patch('/actions/{action}', [APIInterventionActionController::class, 'update'])->name('api.interventions.actions.update');
    Route::delete('/actions/{action}', [APIInterventionActionController::class, 'destroy'])->name('api.interventions.actions.destroy');
});
