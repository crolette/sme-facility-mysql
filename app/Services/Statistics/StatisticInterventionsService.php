<?php

namespace App\Services\Statistics;

use App\Models\Central\CategoryType;
use App\Models\Tenants\Intervention;
use Barryvdh\Debugbar\Facades\Debugbar;


class StatisticInterventionsService
{
    public function getByType($filters = [])
    {
        $interventionsTypeCount = Intervention::query()
            ->forMaintenanceManager()
            ->withoutGlobalScope('ancient')
            ->where('created_at', '>', $filters['date_from'])->where('created_at', '<', $filters['date_to'])
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

        return $interventionsByType;
    }

    public function getByAssignee($filters = [])
    {
        $interventionsByAssignee = Intervention::query()
            ->whereHas('assignable')
            ->forMaintenanceManager()
            ->withoutGlobalScope('ancient')
            ->where('created_at', '>', $filters['date_from'])->where('created_at', '<', $filters['date_to'])
            ->selectRaw('assignable_type, assignable_id, COUNT(*) as count')
            ->groupBy('assignable_type', 'assignable_id')
            ->get();


        $interventionsByAssignee = $interventionsByAssignee->mapWithKeys(function ($item) {
            if ($item->assignable_type) {
                $assignable = $item->assignable_type::find($item->assignable_id);
                return [
                    $assignable?->first_name ? $assignable?->full_name : $assignable->name ?? 'Unknown' => $item->count
                ];
            } else {
                return [
                    __('interventions.assigned_not') => $item->count
                ];
            }
        });

        // $interventionsByAssignee = $interventionsByAssignee->map(function ($item) {
        //     if ($item->assignable_type) {
        //         $assignable = $item->assignable_type::find($item->assignable_id);
        //         return [
        //             // 'id' => $assignable?->id,
        //             'name' => $assignable?->first_name ? $assignable?->full_name : $assignable->name ?? 'Unknown',
        //             'type' => class_basename($item->assignable_type),
        //             'picture' => class_basename($item->assignable_type) === 'User' ? $assignable?->avatar : $assignable->logo,
        //             'count' => $item->count
        //         ];
        //     } else {
        //         return [
        //             'name' => __('interventions.assigned_not'),
        //             'type' => __('interventions.assigned_not'),
        //             'count' => $item->count
        //         ];
        //     }
        // });
        // dd($interventionsByAssignee);

        return $interventionsByAssignee;
    }

    public function getByStatus($filters = [])
    {

        $interventionsByStatus =  Intervention::query()
            ->forMaintenanceManager()
            ->withoutGlobalScope('ancient')
            ->where('created_at', '>', $filters['date_from'])->where('created_at', '<', $filters['date_to'])
            ->where('status', '<>', 'completed')
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $interventionsByStatus = $interventionsByStatus->mapWithKeys(function ($count, $key) use ($interventionsByStatus) {
            return [__('common.status.' . $key) => $count];
        });


        return $interventionsByStatus;
    }


    public function getMissed($filters = [])
    {
        $interventionsMissed = Intervention::query()
            ->forMaintenanceManager()
            ->withoutGlobalScope('ancient')
            ->where('created_at', '>', $filters['date_from'])->where('created_at', '<', $filters['date_to'])
            ->where('status', '=', 'completed');

        if ($filters['period'] === 'week') {

            $interventionsMissed = $interventionsMissed
                ->selectRaw('WEEK(completed_at, 1) AS week')
                ->selectRaw('SUM(DATE(completed_at) > planned_at) AS missed_count')
                ->groupBy('week')
                ->orderBy('week')
                ->pluck('missed_count', 'week');
        } else {
            $interventionsMissed =  $interventionsMissed
                ->selectRaw('DATE_FORMAT(completed_at, \'%m-%Y\') AS month')
                ->selectRaw('SUM(DATE(completed_at) > planned_at) AS missed_count')
                ->groupBy('month')
                ->orderBy('month')
                ->pluck('missed_count', 'month');
        }

        return $interventionsMissed;
    }
}
