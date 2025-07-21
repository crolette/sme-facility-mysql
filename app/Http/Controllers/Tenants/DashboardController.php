<?php

namespace App\Http\Controllers\Tenants;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Ticket;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function show()
    {
        $assets = Asset::count();
        $tickets = Ticket::where('status', '!=', 'closed')->count();

        // dd($assets, $tickets);

        return Inertia::render('tenants/dashboard', ['assets' => $assets, 'tickets' => $tickets]);
    }
}
