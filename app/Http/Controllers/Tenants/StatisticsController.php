<?php

namespace App\Http\Controllers\Tenants;

use Inertia\Inertia;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Cache;
use Barryvdh\Debugbar\Facades\Debugbar;
use App\Http\Requests\Tenant\StatisticsRequest;
use App\Services\Statistics\StatisticTicketsService;
use App\Services\Statistics\StatisticInterventionsService;

class StatisticsController extends Controller
{

    public function index(StatisticsRequest $request)
    {
        $tenant = tenant();
        $limits = Cache::get("tenant:{$tenant->id}:limits");

        if (!Gate::allows('view statistics')) {
            ApiResponse::notAuthorized();
            return redirect()->route('tenant.dashboard');
        }

        if ($limits['has_statistics'] === false) {
            ApiResponse::notAuthorized();
            return redirect()->route('tenant.dashboard');
        }

        $interventionsByStatus = app(StatisticInterventionsService::class)->getByStatus($request->validated());
        $interventionsByType = app(StatisticInterventionsService::class)->getByType($request->validated());
        $interventionsByAssignee = app(StatisticInterventionsService::class)->getByAssignee($request->validated());
        $interventionsMissed = app(StatisticInterventionsService::class)->getMissed($request->validated());


        $ticketsByPeriod = app(StatisticTicketsService::class)->getByPeriod($request->validated());
        $ticketsByAssetOrLocations = app(StatisticTicketsService::class)->getByAssetOrLocations($request->validated());
        $ticketsAvgDuration = app(StatisticTicketsService::class)->getByAvgDuration($request->validated());
        $ticketsByAvgHandlingDuration = app(StatisticTicketsService::class)->getByAvgHandlingDuration($request->validated());

        return Inertia::render('tenants/statistics/IndexStatistics', ['interventionsByStatus' => $interventionsByStatus, 'interventionsByType' => $interventionsByType, 'interventionsMissed' => $interventionsMissed, 'interventionsByAssignee' => $interventionsByAssignee, 'ticketsByPeriod' => $ticketsByPeriod, 'ticketsByAssetOrLocations' => $ticketsByAssetOrLocations, 'ticketsAvgDuration' => $ticketsAvgDuration, 'ticketsByAvgHandlingDuration' => $ticketsByAvgHandlingDuration]);
    }
}
