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
            ->withoutGlobalScope('ancient')
            ->where('status', '<>', 'completed')
            ->where('created_at', '>', $filters['date_from'])->where('created_at', '<', $filters['date_to'])
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
            ->withoutGlobalScope('ancient')
            ->where('created_at', '>', $filters['date_from'])->where('created_at', '<', $filters['date_to'])
            ->selectRaw('assignable_type, assignable_id, COUNT(*) as count')
            ->groupBy('assignable_type', 'assignable_id')
            ->get();

        $interventionsByAssignee = $interventionsByAssignee->map(function ($item) {
            if ($item->assignable_type) {
                $assignable = $item->assignable_type::find($item->assignable_id);
                return [
                    'id' => $assignable->id,
                    'name' => $assignable?->first_name ? $assignable?->full_name : $assignable->name ?? 'Unknown',
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

        return $interventionsByAssignee;
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
