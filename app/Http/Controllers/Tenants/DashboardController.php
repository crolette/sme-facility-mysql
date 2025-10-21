<?php

namespace App\Http\Controllers\Tenants;

use Inertia\Inertia;
use App\Enums\CategoryTypes;
use App\Models\Tenants\Room;
use App\Models\Tenants\Site;
use Illuminate\Http\Request;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Floor;
use App\Models\Tenants\Ticket;
use App\Models\Tenants\Building;
use App\Enums\MaintenanceFrequency;
use App\Http\Controllers\Controller;
use App\Models\Tenants\Company;
use App\Models\Tenants\Intervention;
use App\Models\Tenants\Maintainable;
use DirectoryIterator;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    public function show()
    {
        $assetsCount = Asset::count();
        $ticketsCount = Ticket::where('status', '!=', 'closed')->count();
        $interventionsCount = Intervention::where('status', '!=', 'completed')->where('status', '!=', 'cancelled')->count();

        $company = Company::first();

        $diskSizes = [
            'mb' => $company->disk_size_mb,
            'gb' => $company->disk_size_gb,
            'percent' => round($company->disk_size_gb / 20, 2)
        ];

        $counts = [
            'assetsCount' => $assetsCount,
            'ticketsCount' => $ticketsCount,
            'interventionsCount' => $interventionsCount,
        ];

        $maintainables = Maintainable::select(
            'id',
            'name',
            'next_maintenance_date',
            'maintenance_frequency',
            'maintainable_id',
            'maintainable_type'
        )
            ->where('need_maintenance', true)
            ->whereNotNull('next_maintenance_date')
            ->where('next_maintenance_date', '>=', today())
            ->whereHasMorph(
                'maintainable',
                [
                    Asset::class,
                    Site::class,
                    Building::class,
                    Floor::class,
                    Room::class
                ],
                function ($query, $type) {
                    if ($type === Asset::class) {
                        $query->whereNull('deleted_at'); // exclut les soft deleted
                    }
                }
            )
            ->orderBy('next_maintenance_date')
            ->limit(10)
            ->with('maintainable')
            ->get();


        $overdueMaintenances =
            Maintainable::select(
                'id',
                'name',
                'next_maintenance_date',
                'maintenance_frequency',
                'maintainable_id',
                'maintainable_type'
            )
            ->where('need_maintenance', true)
            ->whereNotNull('next_maintenance_date')
            ->where('next_maintenance_date', '<', today())
            ->whereHasMorph(
                'maintainable',
                [
                    Asset::class,
                    Site::class,
                    Building::class,
                    Floor::class,
                    Room::class
                ],
                function ($query, $type) {
                    if ($type === Asset::class) {
                        $query->whereNull('deleted_at'); // exclut les soft deleted
                    }
                }
            )
            ->orderBy('next_maintenance_date')
            ->limit(10)
            ->with('maintainable')
            ->get();

        $interventions = Intervention::select('id', 'intervention_type_id', 'priority', 'status', 'maintainable_id', 'interventionable_type', 'interventionable_id', 'ticket_id', 'planned_at')->where('planned_at', '>=', today())->orderBy('planned_at')->limit(10)->with('maintainable:id,name,maintainable_type', 'ticket:id,description', 'interventionable')->get();
        $overdueInterventions = Intervention::select('id', 'intervention_type_id', 'priority', 'status', 'maintainable_id', 'interventionable_type', 'interventionable_id', 'ticket_id', 'planned_at')->where('planned_at', '<', today())->whereNotIn('status', ['completed', 'cancelled'])->orderBy('planned_at')->limit(10)->with('maintainable:id,name,maintainable_type', 'ticket:id,description', 'interventionable')->get();

        return Inertia::render('tenants/dashboard', ['counts' => $counts, 'overdueMaintenances' => $overdueMaintenances, 'overdueInterventions' => $overdueInterventions,  'maintainables' => $maintainables, 'interventions' => $interventions, 'diskSizes' => $diskSizes]);
    }
}
