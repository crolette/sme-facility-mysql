<?php

namespace App\Http\Controllers\Tenants;

use Carbon\Carbon;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Tenants\Ticket;
use App\Models\Tenants\Provider;
use App\Http\Controllers\Controller;
use App\Models\Central\CategoryType;
use App\Models\Tenants\Intervention;

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
        $categories = CategoryType::where('category', 'provider')->get();
        return Inertia::render('tenants/providers/create', ['providerCategories' => $categories]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function edit(Provider $provider)
    {
        $categories = CategoryType::where('category', 'provider')->get();
        return Inertia::render('tenants/providers/create', ['provider' => $provider, 'providerCategories' => $categories]);
    }


    /**
     * Display the specified resource.
     */
    public function show(Provider $provider)
    {
        return Inertia::render('tenants/providers/show', ['item' => $provider->load('users')]);
    }
}
