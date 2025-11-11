<?php

namespace App\Services\Statistics;

use App\Enums\TicketStatus;
use App\Models\Tenants\Ticket;
use App\Models\Central\CategoryType;
use App\Models\Tenants\Intervention;
use Barryvdh\Debugbar\Facades\Debugbar;


class StatisticTicketsService
{
    public function getByPeriod($filters = [])
    {
        if ($filters['period'] === 'week') {
            $ticketsByPeriod = Ticket::query()
                ->where('status', '<>', TicketStatus::CLOSED)
                ->where('created_at', '>', $filters['date_from'])->where('created_at', '<', $filters['date_to'])
                ->selectRaw('WEEK(created_at, 1) AS week, COUNT(*) as count_week')
                ->groupBy('week')
                ->orderBy('week')
                ->pluck('count_week', 'week');
        } else {
            $ticketsByPeriod = Ticket::query()
                ->where('status', '<>', TicketStatus::CLOSED)
                ->where('created_at', '>', $filters['date_from'])->where('created_at', '<', $filters['date_to'])
                ->selectRaw('DATE_FORMAT(created_at, \'%m-%Y\') AS month, COUNT(*) as count_month')
                ->groupBy('month')
                ->orderBy('month')
                ->pluck('count_month', 'month');
        }

        return $ticketsByPeriod;
    }

    public function getByAssetOrLocations($filters = [])
    {
        $ticketsByAssetOrLocations = Ticket::query()
            ->where('status', '<>', TicketStatus::CLOSED)
            ->where('created_at', '>', $filters['date_from'])->where('created_at', '<', $filters['date_to'])
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

        return $ticketsByAssetOrLocations;
    }

    public function getByStatus($filters = [])
    {

        $interventionsByStatus =  Intervention::query()
            ->withoutGlobalScope('ancient')
            ->where('created_at', '>', $filters['date_from'])->where('created_at', '<', $filters['date_to'])
            ->where('status', '<>', 'completed')
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');



        return $interventionsByStatus;
    }
}
