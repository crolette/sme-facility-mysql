<?php

use Carbon\Carbon;
use App\Enums\TicketStatus;
use App\Helpers\ApiResponse;
use App\Http\Requests\Tenant\StatisticsRequest;
use Illuminate\Http\Request;
use App\Models\Tenants\Ticket;
use App\Models\Central\CategoryType;
use App\Models\Tenants\Intervention;
use App\Services\Statistics\StatisticTicketsService;
use Illuminate\Support\Facades\Route;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Support\Facades\Validator;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;

Route::middleware([
    'web',
    InitializeTenancyBySubdomain::class,
    \Stancl\Tenancy\Middleware\ScopeSessions::class,
    \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
    'auth:tenant'
])->prefix('/v1/statistics/tickets')->group(
    function () {

        Route::get('/by-period', function (StatisticsRequest $request) {


            $ticketsByPeriod = app(StatisticTicketsService::class)->getByPeriod($request->validated());

            return ApiResponse::success($ticketsByPeriod);
        })->name('api.statistics.tickets.by-period');


        Route::get('/by-items', function (StatisticsRequest $request) {


            $ticketsByAssetOrLocations = app(StatisticTicketsService::class)->getByAssetOrLocations($request->validated());



            return ApiResponse::success($ticketsByAssetOrLocations);
        })->name('api.statistics.tickets.by-items');
    }
);
