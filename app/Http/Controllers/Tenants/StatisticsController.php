<?php

namespace App\Http\Controllers\Tenants;

use App\Enums\TicketStatus;
use Inertia\Inertia;
use App\Models\Tenants\Ticket;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Central\CategoryType;
use App\Models\Tenants\Intervention;

class StatisticsController extends Controller
{

    public function index()
    {
        // $interventionsType = Intervention::withoutGlobalScope('ancient')->select(['id', 'status', 'updated_at'])->groupBy('status')->get();
        $interventionsByStatus =  Intervention::query()
            ->withoutGlobalScope('ancient')
            ->where('status', '<>', 'completed')
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');


        $interventionsTypeCount = Intervention::query()
            ->withoutGlobalScope('ancient')
            ->where('status', '<>', 'completed')
            ->selectRaw('intervention_type_id, COUNT(*) as count')
            ->groupBy('intervention_type_id')
            ->orderBy('count')
            ->pluck('count', 'intervention_type_id');

        // TODO Cacher ça dans Redis 
        $interventionTypes = CategoryType::where('category', 'intervention')->get();

        $interventionsByType = $interventionsTypeCount->mapWithKeys(function ($count, $key) use ($interventionTypes) {
            $type = $interventionTypes->firstWhere('id', $key);
            return [$type->label => $count];
        });




        $interventionsByAssignee = Intervention::query()
            ->withoutGlobalScope('ancient')
            ->selectRaw('assignable_type, assignable_id, COUNT(*) as count')
            ->groupBy('assignable_type', 'assignable_id')
            ->get();

        $interventionsByAssignee = $interventionsByAssignee->map(function ($item) {
            // dd($item);
            if ($item->assignable_type) {
                $assignable = $item->assignable_type::find($item->assignable_id);
                // dd($assignable);
                return [
                    'id' => $assignable->id,
                    'name' => $assignable?->first_name ? $assignable?->full_name : $assignable->name ?? 'Unknown', // Adapte selon ton attribut
                    'type' => class_basename($item->assignable_type),
                    'picture' => class_basename($item->assignable_type) === 'User' ? $assignable->avatar : $assignable->logo,
                    'count' => $item->count
                ];
            } else {
                return [
                    'name' => 'Not assigned',
                    'type' => 'Not assigned',
                    'count' => $item->count
                ];
            }
        });

        $begin = '2025-01-01';
        $end = '2025-12-31';
        $period = 'week';


        if ($period === 'month') {
            // ticketsByMonth
            $ticketsByPeriod = Ticket::query()
                ->where('status', '<>', TicketStatus::CLOSED)
                ->where('created_at', '>', $begin)->where('created_at', '<', $end)
                ->selectRaw('DATE_FORMAT(created_at, \'%m-%Y\') AS month, COUNT(*) as count_month')
                ->groupBy('month')
                ->orderBy('month')
                ->pluck('count_month', 'month');
        }

        if ($period === 'week') {
            //ticketsByWeek
            $ticketsByPeriod = Ticket::query()
                ->where('status', '<>', TicketStatus::CLOSED)
                ->where('created_at', '>', $begin)->where('created_at', '<', $end)
                ->selectRaw('WEEK(created_at) AS week, COUNT(*) as count_week')
                ->groupBy('week')
                ->orderBy('week')
                ->pluck('count_week', 'week');
        }


        // assets with most problems
        $ticketsByAssetOrLocations = Ticket::query()
            ->where('status', '<>', TicketStatus::CLOSED)
            ->selectRaw('ticketable_type, ticketable_id, COUNT(*) as count')
            ->groupBy('ticketable_type', 'ticketable_id')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        $ticketsByAssetOrLocations = $ticketsByAssetOrLocations->map(function ($item) {
            if (class_basename($item->ticketable_type) === class_basename(Asset::class)) {
                $ticketable = $item->ticketable_type::withTrashed()->find($item->ticketable_id);
            } else {
                $ticketable = $item->ticketable_type::find($item->ticketable_id);
            }

            return [
                'id' => $ticketable->id,
                'reference_code' => $ticketable?->reference_code  ?? 'Unknown', // Adapte selon ton attribut
                'name' => $ticketable?->name  ?? 'Unknown', // Adapte selon ton attribut
                'type' => class_basename($item->ticketable_type),
                'count' => $item->count
            ];
        });



        return Inertia::render('tenants/statistics/IndexStatistics', ['interventionsByStatus' => $interventionsByStatus, 'interventionsByType' => $interventionsByType, 'interventionsByAssignee' => $interventionsByAssignee, 'ticketsByPeriod' => $ticketsByPeriod, 'ticketsByAssetOrLocations' => $ticketsByAssetOrLocations]);
    }
}
