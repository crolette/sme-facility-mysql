<?php

namespace App\Http\Controllers\Tenants;

use App\Enums\RoleTypes;
use Inertia\Inertia;
use App\Models\Tenants\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (Auth::user()->cannot('viewAny', User::class)) {
            abort(403);
        }
        $users = User::withoutRole('Super Admin')->with('roles:id,name', 'provider:id,name')->paginate();
        return Inertia::render('tenants/users/IndexUsers', ['items' => $users]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (Auth::user()->cannot('create', User::class)) {
            abort(403);
        }

        return Inertia::render('tenants/users/CreateUpdateUser', ['roles' => array_column(RoleTypes::cases(), 'value')]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function edit(User $user)
    {
        if (Auth::user()->cannot('update', $user)) {
            abort(403);
        }

        return Inertia::render('tenants/users/CreateUpdateUser', ['user' => $user->load('roles:id,name', 'provider:id,name'), 'roles' => array_column(RoleTypes::cases(), 'value')]);
    }


    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        if (Auth::user()->cannot('view', $user)) {
            abort(403);
        }
        return Inertia::render('tenants/users/ShowUser', ['item' => $user->load('assets', 'roles:id,name', 'provider:id,name')]);
    }
}
