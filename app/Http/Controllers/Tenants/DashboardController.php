<?php

namespace App\Http\Controllers\Tenants;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Ticket;
use App\Http\Controllers\Controller;
use App\Models\Tenants\Maintainable;

class DashboardController extends Controller
{
    public function show()
    {
        $assets = Asset::count();
        $tickets = Ticket::where('status', '!=', 'closed')->count();
        $maintainables = Maintainable::select('id', 'name', 'next_maintenance_date', 'maintenance_frequency', 'maintainable_id', 'maintainable_type')->where('need_maintenance', true)->whereNotNull('next_maintenance_date')->orderBy('next_maintenance_date')->limit(10)->get()->load('maintainable');
        // dd($maintainables);

        // dd($assets, $tickets);

        return Inertia::render('tenants/dashboard', ['assets' => $assets, 'tickets' => $tickets, 'maintainables' => $maintainables]);
    }
}
