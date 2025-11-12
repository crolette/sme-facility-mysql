<?php

namespace App\Http\Controllers\Tenants;

use Inertia\Inertia;
use App\Enums\TicketStatus;
use App\Models\Tenants\Ticket;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Central\CategoryType;
use App\Models\Tenants\Intervention;
use App\Http\Requests\Tenant\StatisticsRequest;
use App\Services\Statistics\StatisticTicketsService;
use App\Services\Statistics\StatisticInterventionsService;

class StatisticsController extends Controller
{

    public function index(StatisticsRequest $request)
    {
        $interventionsByStatus = app(StatisticInterventionsService::class)->getByStatus($request->validated());
        $interventionsByType = app(StatisticInterventionsService::class)->getByType($request->validated());
        $interventionsByAssignee = app(StatisticInterventionsService::class)->getByAssignee($request->validated());

        $ticketsByPeriod = app(StatisticTicketsService::class)->getByPeriod($request->validated());
        $ticketsByAssetOrLocations = app(StatisticTicketsService::class)->getByAssetOrLocations($request->validated());
        $ticketsAvgDuration = app(StatisticTicketsService::class)->getByAvgDuration($request->validated());
        $ticketsByAvgHandlingDuration = app(StatisticTicketsService::class)->getByAvgHandlingDuration($request->validated());

        return Inertia::render('tenants/statistics/IndexStatistics', ['interventionsByStatus' => $interventionsByStatus, 'interventionsByType' => $interventionsByType, 'interventionsByAssignee' => $interventionsByAssignee, 'ticketsByPeriod' => $ticketsByPeriod, 'ticketsByAssetOrLocations' => $ticketsByAssetOrLocations, 'ticketsAvgDuration' => $ticketsAvgDuration, 'ticketsByAvgHandlingDuration' => $ticketsByAvgHandlingDuration]);
    }
}
