<?php

namespace App\Http\Controllers\Tenants;

use App\Models\Tenants\Intervention;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Tenants\InterventionAction;
use App\Models\Tenants\Ticket;
use Inertia\Inertia;

class InterventionActionController extends Controller
{

    /**
     * Show the form for creating a new resource.
     */
    public function create(Intervention $intervention)
    {
        return Inertia::render('tenants/tickets/interventions/actions/create', ['intervention' => $intervention]);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function edit(InterventionAction $action)
    {
        return Inertia::render('tenants/tickets/interventions/actions/create', ['action' => $action]);
    }
}
