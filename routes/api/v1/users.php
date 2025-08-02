<?php

use App\Helpers\ApiResponse;
use App\Models\Tenants\User;
use Illuminate\Http\Request;
use Stancl\Tenancy\Middleware\ScopeSessions;
use App\Http\Controllers\API\V1\APIUserController;
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
])->prefix('/v1/users')->group(function () {

    Route::get('/search', function (Request $request) {
        $query  = User::select('id', 'first_name', 'last_name');

        if ($request->query('q')) {
            $query->where(function ($subquery) use ($request) {
                $subquery->where('first_name', 'like', '%' . $request->query('q') . '%');
            });
        }

        Debugbar::info($query->get());

        return ApiResponse::success($query->get());
    })->name('api.users.search');

    Route::post('/', [APIUserController::class, 'store'])->name('api.users.store');
    Route::get('/{user}', [APIUserController::class, 'show'])->name('api.users.show');
    Route::patch('/{user}', [APIUserController::class, 'update'])->name('api.users.update');
    Route::delete('/{user}', [APIUserController::class, 'destroy'])->name('api.users.destroy');
    Route::post('/{user}/logo', [APIUploadProfilePictureController::class, 'store'])->name('api.users.picture.store');
});
