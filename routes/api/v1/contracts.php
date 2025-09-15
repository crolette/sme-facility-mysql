<?php

use App\Helpers\ApiResponse;
use App\Http\Controllers\API\V1\APIContractController;
use App\Models\Tenants\Contract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;

Route::middleware([
    'web',
    InitializeTenancyBySubdomain::class,
    \Stancl\Tenancy\Middleware\ScopeSessions::class,
    \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
    'auth:tenant'
])->prefix('/v1/contracts')->group(function () {
    Route::get('/', function () {
        $contracts = Contract::select('id', 'name', 'type', 'provider_id', 'status', 'renewal_type', 'end_date')->with('provider:id,name,category_type_id')->get();
        return ApiResponse::success($contracts);
    })->name('api.contracts.index');

    Route::get('/search', function(Request $request) {
        $contracts = Contract::where('name', 'like', '%'.$request->query('q').'%')->get();
        
        return ApiResponse::success($contracts);
    })->name('api.contracts.search');

    Route::post('/', [APIContractController::class, 'store'])->name('api.contracts.store');

    Route::prefix('/{contract}')->group(function () {
        Route::patch('/', [APIContractController::class, 'update'])->name('api.contracts.update');
        Route::delete('/', [APIContractController::class, 'destroy'])->name('api.contracts.destroy');
    });
});
