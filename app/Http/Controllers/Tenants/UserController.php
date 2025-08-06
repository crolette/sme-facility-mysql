<?php

namespace App\Http\Controllers\Tenants;

use Inertia\Inertia;
use App\Models\Tenants\User;
use Illuminate\Http\Request;
use App\Models\Tenants\Ticket;
use App\Models\Tenants\Provider;
use App\Http\Controllers\Controller;
use App\Models\Tenants\Intervention;
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
        $users = User::all()->load('provider:id,name');
        return Inertia::render('tenants/users/index', ['users' => $users]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (Auth::user()->cannot('create', User::class)) {
            abort(403);
        }
        return Inertia::render('tenants/users/create');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function edit(User $user)
    {
        if (Auth::user()->cannot('update', $user)) {
            abort(403);
        }

        return Inertia::render('tenants/users/create', ['user' => $user->load('provider:id,name')]);
    }


    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        if (Auth::user()->cannot('view', $user)) {
            abort(403);
        }
        return Inertia::render('tenants/users/show', ['item' => $user->load('provider:id,name')]);
    }
}
