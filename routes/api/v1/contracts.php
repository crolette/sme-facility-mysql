<?php

use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Models\Tenants\Contract;
use App\Services\DocumentService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Requests\Tenant\DocumentUploadRequest;
use App\Http\Controllers\API\V1\APIContractController;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;

Route::middleware([
    'web',
    InitializeTenancyBySubdomain::class,
    \Stancl\Tenancy\Middleware\ScopeSessions::class,
    \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
    'auth:tenant'
])->prefix('/v1/contracts')->group(function () {
    Route::get('/', function () {
        $contracts = Contract::select('id', 'name', 'type', 'provider_id', 'status', 'renewal_type', 'end_date')->with('provider:id,name,category_type_id')->paginated();
        return ApiResponse::success($contracts);
    })->name('api.contracts.index');

    Route::get('/search', function (Request $request) {
        $contracts = Contract::where('name', 'like', '%' . $request->query('q') . '%')->get();

        return ApiResponse::success($contracts);
    })->name('api.contracts.search');



    Route::post('/', [APIContractController::class, 'store'])->name('api.contracts.store');

    Route::prefix('/{contract}')->group(function () {
        Route::patch('/', [APIContractController::class, 'update'])->name('api.contracts.update');
        Route::delete('/', [APIContractController::class, 'destroy'])->name('api.contracts.destroy');

        Route::prefix('/documents')->group(function () {

            Route::get('', function (Contract $contract) {

                return ApiResponse::success($contract->load('documents')->documents);
            })->name('api.contracts.documents');

            Route::post('', function (Contract $contract, DocumentUploadRequest $request) {
                if (Auth::user()->cannot('update', $contract))
                    return ApiResponse::notAuthorized();

                app(DocumentService::class)->uploadAndAttachDocumentsForContract($contract, $request['files']);

                return ApiResponse::success([], 'Document added');
            })->name('api.contracts.documents.post');

            Route::patch('', function (Contract $contract, Request $request) {
                if (Auth::user()->cannot('update', $contract))
                    return ApiResponse::notAuthorized();

                $validated = $request->validateWithBag('errors', [
                    'document_id' => 'required|exists:documents,id'
                ]);

                app(DocumentService::class)->detachDocumentFromModel($contract, $validated['document_id']);
                return ApiResponse::success([], 'Document removed');
            })->name('api.contracts.documents.detach');
        });
    });
});
