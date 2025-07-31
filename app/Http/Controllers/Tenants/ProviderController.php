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
        $providers = Provider::all();
        return Inertia::render('tenants/providers/index', ['providers' => $providers]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('tenants/providers/create');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function edit(Provider $provider)
    {
        return Inertia::render('tenants/providers/create', ['provider' => $provider]);
    }


    /**
     * Display the specified resource.
     */
    public function show(Provider $provider)
    {
        return Inertia::render('tenants/providers/show', ['provider' => $provider]);
    }
}
