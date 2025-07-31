<?php

namespace App\Http\Controllers\Tenants;

use App\Models\Tenants\Intervention;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Tenants\Provider;
use App\Models\Tenants\Ticket;
use Inertia\Inertia;

class ProviderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // $interventions = Provider::all();
        // return Inertia::render('tenants/tickets/interventions/index', ['interventions' => $interventions]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Provider $provider)
    {
        // return Inertia::render('tenants/tickets/interventions/create', ['ticket' => $ticket]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function edit(Provider $provider)
    {
        // return Inertia::render('tenants/tickets/interventions/create', ['ticket' => $ticket]);
    }


    /**
     * Display the specified resource.
     */
    public function show(Provider $provider)
    {
        // return Inertia::render('tenants/tickets/interventions/show', ['intervention' => $intervention->load('ticket')]);
    }
}
