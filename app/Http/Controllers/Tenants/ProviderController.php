<?php

namespace App\Http\Controllers\Tenants;

use Inertia\Inertia;
use App\Models\Tenants\Provider;
use App\Http\Controllers\Controller;
use App\Models\Central\CategoryType;
use Illuminate\Support\Facades\Auth;

class ProviderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (Auth::user()->cannot('viewAny', Provider::class))
            abort(403);

        $providers = Provider::all();
        return Inertia::render('tenants/providers/index', ['providers' => $providers]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (Auth::user()->cannot('create', Provider::class))
            abort(403);

        $categories = CategoryType::where('category', 'provider')->get();
        return Inertia::render('tenants/providers/create', ['providerCategories' => $categories]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function edit(Provider $provider)
    {
        if (Auth::user()->cannot('update', $provider))
            abort(403);

        $categories = CategoryType::where('category', 'provider')->get();
        return Inertia::render('tenants/providers/create', ['provider' => $provider, 'providerCategories' => $categories]);
    }


    /**
     * Display the specified resource.
     */
    public function show(Provider $provider)
    {
        if (Auth::user()->cannot('view', $provider))
            abort(403);

        return Inertia::render('tenants/providers/show', ['item' => $provider->load('users', 'contracts', 'contracts.provider')]);
    }
}
