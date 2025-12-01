<?php

use App\Helpers\ApiResponse;
use Illuminate\Support\Facades\Route;
use Barryvdh\Debugbar\Facades\Debugbar;
use App\Http\Requests\Tenant\StatisticsRequest;
use App\Services\Statistics\StatisticInterventionsService;
use App\Http\Middleware\CustomInitializeTenancyBySubdomain;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;

Route::middleware([
    'web',
    CustomInitializeTenancyBySubdomain::class,
    \Stancl\Tenancy\Middleware\ScopeSessions::class,
    \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
    'auth:tenant'
])->prefix('/v1/statistics/interventions')->group(
    function () {

        Route::get('/by-type', function (StatisticsRequest $request) {

            $interventionsByType = app(StatisticInterventionsService::class)->getByType($request->validated());


            return ApiResponse::success($interventionsByType);
        })->name('api.statistics.interventions.by-type');


        Route::get('/by-assignee', function (StatisticsRequest $request) {

            $interventionsByAssignee = app(StatisticInterventionsService::class)->getByAssignee($request->validated());

            return ApiResponse::success($interventionsByAssignee);
        })->name('api.statistics.interventions.by-assignee');


        Route::get('/by-status', function (StatisticsRequest $request) {


            $interventionsByStatus = app(StatisticInterventionsService::class)->getByStatus($request->validated());



            return ApiResponse::success($interventionsByStatus);
        })->name('api.statistics.interventions.by-status');
    }
);
