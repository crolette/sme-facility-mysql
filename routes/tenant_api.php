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
use App\Http\Controllers\API\V1\APISearchAssetsLocationController;
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
                Route::get('/all', [APISearchAssetsLocationController::class, 'index'])->name('api.search.all');

                Route::get('/file', function (Request $request) {

                    $path = $request->path;

                    if (!$path || !Storage::disk('tenants')->exists($path)) {
                        return null;
                    }

                    return Storage::disk('tenants')->download($path);
                })->name('api.file.download');

                // Get image
                Route::get('/image', function (Request $request) {

                    $path = $request->path;

                    if (!$path || !Storage::disk('tenants')->exists($path)) {
                        return null;
                    }

                    return response()->file(Storage::disk('tenants')->path($path));
                })->name('api.image.show');

                Route::get('/search', function (Request $request) {
                    $documents = Document::where('name', 'like', '%' . $request->query('q') . '%')->orWhere('description', 'like', '%' . $request->query('q') . '%')->limit(5)->get();

                    return ApiResponse::success($documents);
                })->name('api.documents.search');


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

                    if (!$path || !Storage::disk('tenants')->exists($path)) {
                        return null;
                    }

                    return response()->file(Storage::disk('tenants')->path($path));
                })->name('api.documents.show');

                // Get the path to a specific picture through the guard tenant
                Route::get('/pictures/{picture}', function (Picture $picture) {

                    $path = $picture->path;
                    if (!$path || !Storage::disk('tenants')->exists($path)) {
                        return null;
                    }

                    return response()->file(Storage::disk('tenants')->path($path));
                })->name('api.pictures.show');


                // Delete a specific picture
                Route::delete('/pictures/{picture}', [DestroyPictureController::class, 'destroy'])->name('api.pictures.delete');
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
