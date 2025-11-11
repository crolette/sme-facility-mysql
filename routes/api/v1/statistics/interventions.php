<?php

use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Models\Central\CategoryType;
use App\Models\Tenants\Intervention;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;

Route::middleware([
    'web',
    InitializeTenancyBySubdomain::class,
    \Stancl\Tenancy\Middleware\ScopeSessions::class,
    \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
    'auth:tenant'
])->prefix('/v1/statistics/interventions')->group(
    function () {

        Route::get('/by-type', function (Request $request) {



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

            return ApiResponse::success($interventionsByType);
        })->name('api.statistics.interventions.by-type');


        Route::get('/by-assignee', function (Request $request) {

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

            return ApiResponse::success($interventionsByAssignee);
        })->name('api.statistics.interventions.by-assignee');


        Route::get('/by-status', function (Request $request) {

            $interventionsByStatus =  Intervention::query()
                ->withoutGlobalScope('ancient')
                ->where('status', '<>', 'completed')
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status');



            return ApiResponse::success($interventionsByStatus);
        })->name('api.statistics.interventions.by-status');
    }
);
