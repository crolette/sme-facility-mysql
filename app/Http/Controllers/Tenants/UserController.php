<?php

namespace App\Http\Controllers\Tenants;

use App\Models\Tenants\Intervention;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Tenants\Provider;
use App\Models\Tenants\Ticket;
use App\Models\Tenants\User;
use Inertia\Inertia;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::all()->load('provider:id,name');
        return Inertia::render('tenants/users/index', ['users' => $users]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('tenants/users/create');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function edit(User $user)
    {
        return Inertia::render('tenants/users/create', ['user' => $user]);
    }


    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return Inertia::render('tenants/users/show', ['user' => $user]);
    }
}
