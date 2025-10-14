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
        $query  = User::select('id', 'first_name', 'last_name', 'email')->withoutRole('Super Admin');

        if ($request->query('q')) {
            $query->where(function ($subquery) use ($request) {
                $subquery->where('first_name', 'like', '%' . $request->query('q') . '%');
            });
        }

        if ($request->query('interns') === '1')
            $query->whereDoesntHave('provider');

        return ApiResponse::success($query->get());
    })->name('api.users.search');

    Route::get('/maintenance', function (Request $request) {
        $query  = User::role(['Admin', 'Maintenance Manager'])->select('id', 'first_name', 'last_name');

        $query->whereDoesntHave('provider');

        if ($request->query('q')) {
            $query->where(function ($subquery) use ($request) {
                $subquery->where('first_name', 'like', '%' . $request->query('q') . '%');
            });
        }

        return ApiResponse::success($query->get());
    })->name('api.users.maintenance');

    Route::get('/notifications', function () {

        $user = Auth::user();

        return ApiResponse::success(
            collect($user->notification_preferences)->groupBy('asset_type')->toArray()
        );
    })->name('api.users.notifications');
    Route::post('/', [APIUserController::class, 'store'])->name('api.users.store');
    Route::get('/{user}', [APIUserController::class, 'show'])->name('api.users.show');

    Route::patch('/{user}', [APIUserController::class, 'update'])->name('api.users.update');
    Route::delete('/{user}', [APIUserController::class, 'destroy'])->name('api.users.destroy');

    Route::post('/{user}/logo', [APIUploadProfilePictureController::class, 'store'])->name('api.users.picture.store');
    Route::delete('/{user}/logo', [APIUploadProfilePictureController::class, 'destroy'])->name('api.users.picture.destroy');
});
