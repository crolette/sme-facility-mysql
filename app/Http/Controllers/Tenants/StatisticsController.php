<?php

namespace App\Http\Controllers\Tenants;

use Inertia\Inertia;
use App\Http\Controllers\Controller;
use App\Models\Central\CategoryType;
use App\Models\Tenants\Intervention;
use Illuminate\Support\Facades\DB;

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


        $interventionsByAssignee = Intervention::query()
            ->withoutGlobalScope('ancient')
            // ->whereNotNull('assignable_type')
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



        $interventionsByType = $interventionsTypeCount->mapWithKeys(function ($count, $key) use ($interventionTypes) {
            $type = $interventionTypes->firstWhere('id', $key);
            return [$type->label => $count];
        });

        return Inertia::render('tenants/statistics/IndexStatistics', ['interventionsByStatus' => $interventionsByStatus, 'interventionsByType' => $interventionsByType, 'interventionsByAssignee' => $interventionsByAssignee]);
    }
}
