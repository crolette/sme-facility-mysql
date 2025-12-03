<?php

namespace App\Http\Controllers\Tenants;

use Carbon\Carbon;
use Inertia\Inertia;
use DirectoryIterator;
use App\Enums\CategoryTypes;
use App\Models\LocationType;
use App\Models\Tenants\Room;
use App\Models\Tenants\Site;
use Illuminate\Http\Request;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Floor;
use App\Models\Tenants\Ticket;
use App\Models\Tenants\Company;
use App\Models\Tenants\Building;
use App\Models\Tenants\Provider;
use App\Enums\MaintenanceFrequency;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use App\Models\Tenants\Intervention;
use App\Models\Tenants\Maintainable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    public function show()
    {
        $locale = App::getLocale();
        $company = Company::first();

        $diskSizes = [
            'mb' => $company->disk_size_mb,
            'gb' => $company->disk_size_gb,
            'percent' => round($company->disk_size_gb / 0.5, 2)
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
            ->where('next_maintenance_date', '<=', Carbon::now()->addWeek())
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
            ->with('maintainable');


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
            ->with('maintainable');


        $interventions = Intervention::select('id', 'intervention_type_id', 'priority', 'status', 'maintainable_id', 'interventionable_type', 'interventionable_id', 'ticket_id', 'planned_at')
            ->where('planned_at', '>=', today())
            ->where('planned_at', '<=', Carbon::now()->addWeek())
            ->withoutTrashed()
            ->orderBy('planned_at')
            ->limit(10)
            ->with('ticket:id,description', 'interventionable');

        $overdueInterventions = Intervention::select('id', 'intervention_type_id', 'priority', 'status', 'maintainable_id', 'interventionable_type', 'interventionable_id', 'ticket_id', 'planned_at')
            ->where('planned_at', '<', today())
            ->withoutTrashed()
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->orderBy('planned_at')
            ->limit(10)
            ->with('ticket:id,description', 'interventionable');

        if (Auth::user()->hasRole('Maintenance Manager')) {
            $assetsCount = Asset::forMaintenanceManager()->count();
            $ticketsCount = Ticket::forMaintenanceManager()->where('status', '!=', 'closed')->count();
            $interventionsCount = Intervention::forMaintenanceManager()->where('status', '!=', 'completed')->where('status', '!=', 'cancelled')->count();
            $maintainables->forMaintenanceManager();
            $overdueMaintenances->forMaintenanceManager();
            $interventions->forMaintenanceManager();
            $overdueInterventions->forMaintenanceManager();
        } else {
            $assetsCount = Asset::count();
            $ticketsCount = Ticket::where('status', '!=', 'closed')->count();
            $interventionsCount = Intervention::where('status', '!=', 'completed')->where('status', '!=', 'cancelled')->count();
        }

        $counts = [
            'assetsCount' => $assetsCount,
            'ticketsCount' => $ticketsCount,
            'interventionsCount' => $interventionsCount,
        ];

        return Inertia::render('tenants/dashboard', [
            'counts' => $counts,
            'overdueMaintenances' => $overdueMaintenances->get(),
            'overdueInterventions' => $overdueInterventions->get(),
            'maintainables' => $maintainables->get(),
            'interventions' => $interventions->get(),
            'diskSizes' => $diskSizes
        ]);
    }
}
