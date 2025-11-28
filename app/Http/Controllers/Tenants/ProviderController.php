<?php

namespace App\Http\Controllers\Tenants;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Tenants\Country;
use App\Models\Tenants\Provider;
use App\Http\Controllers\Controller;
use App\Models\Central\CategoryType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProviderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (Auth::user()->cannot('viewAny', Provider::class))
            abort(403);

        $providers = Provider::query();

        $validator = Validator::make($request->all(), [
            'q' => 'string|max:255|nullable',
            'category' => 'integer|nullable|gt:0',
            'sortBy' => 'in:asc,desc'
        ]);

        $validator = $validator->validated();


        if (isset($validator['category'])) {
            $providers->where('category_type_id', $request->query('category'));
        }

        if (isset($validator['q'])) {
            $providers->where('name', 'like', '%' . $validator['q'] . '%');
        }

        $categories = CategoryType::getByCategoryCache('provider');

        return Inertia::render('tenants/providers/IndexProviders', ['items' => $providers->paginate()->withQueryString(), 'categories' => $categories, 'filters' => $request->only(['q', 'category'])]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (Auth::user()->cannot('create', Provider::class))
            abort(403);

        $categories = CategoryType::getByCategoryCache('provider');
        $countries = Country::all();
        return Inertia::render('tenants/providers/CreateUpdateProvider', ['providerCategories' => $categories, 'countries' => $countries]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function edit(Provider $provider)
    {
        if (Auth::user()->cannot('update', $provider))
            abort(403);

        $categories = CategoryType::getByCategoryCache('provider');
        $countries = Country::all();
        return Inertia::render('tenants/providers/CreateUpdateProvider', ['provider' => $provider->load('country'), 'providerCategories' => $categories, 'countries' => $countries]);
    }


    /**
     * Display the specified resource.
     */
    public function show(Provider $provider)
    {
        if (Auth::user()->cannot('view', $provider))
            abort(403);

        // dd(basename(Storage::disk('tenants')->path($provider->logo)));


        return Inertia::render('tenants/providers/ShowProvider', ['item' => $provider->load('users', 'contracts', 'contracts.provider', 'assignedInterventions')]);
    }
}
