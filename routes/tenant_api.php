<?php

use App\Http\Controllers\API\V1\ApiSearchLocationController;
use App\Http\Controllers\API\V1\ApiSearchTrashedAssetController;
use App\Models\Tenants\Document;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubDomain;



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
                Route::get('/documents/{document}', function (Document $document) {

                    $path = $document->path;

                    if (! Storage::disk('tenants')->exists($path)) {
                        abort(404);
                    }

                    return response()->file(Storage::disk('tenants')->path($path));
                })->name('documents.show');
            });
        });
    }
);
