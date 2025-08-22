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
use App\Models\Tenants\Maintainable;

class DashboardController extends Controller
{
    public function show()
    {
        $assets = Asset::count();
        $tickets = Ticket::where('status', '!=', 'closed')->count();
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
            ->with('maintainable') // on eager-load correctement
            ->get();
        // dd($maintainables);

        // dd($assets, $tickets);

        return Inertia::render('tenants/dashboard', ['assets' => $assets, 'tickets' => $tickets, 'maintainables' => $maintainables]);
    }
}
