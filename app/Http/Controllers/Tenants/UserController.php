<?php

namespace App\Http\Controllers\Tenants;

use Inertia\Inertia;
use App\Enums\RoleTypes;
use App\Models\Tenants\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (Auth::user()->cannot('viewAny', User::class)) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'q' => 'string|max:255|nullable',
            'sortBy' => 'in:asc,desc',
            'orderBy' => 'string|nullable',
            'canLogin' => 'nullable|in:yes,no',
            'provider' => 'nullable|string',
            'role' => 'nullable|in:admin,manager'
        ]);


        $validatedFields = $validator->validated();

        $users = User::withoutRole('Super Admin')->with('roles:id,name', 'provider:id,name');

        if (isset($validatedFields['q'])) {
            $users->where(function (Builder $query) use ($validatedFields) {
                $query->where('first_name', 'like', '%' . $validatedFields['q'] . '%')
                    ->orWhere('last_name', 'like', '%' . $validatedFields['q'] . '%');
            });
        }

        if (isset($validatedFields['provider'])) {
            $users->whereHas('provider', function (Builder $query) use ($validatedFields) {
                $query->where('name', 'like', '%' . $validatedFields['provider'] . '%');
            });
        }

        if (isset($validatedFields['role'])) {
            $validatedFields['role'] === 'admin' ? $users->role('Admin') : $users->role('Maintenance Manager');
        }

        if (isset($validatedFields['canLogin'])) {
            $validatedFields['canLogin'] === 'yes' ? $users->where('can_login', true) : $users->where('can_login', false);
        }


        return Inertia::render('tenants/users/IndexUsers', ['items' => $users->paginate()->withQueryString(), 'filters' => $validator->safe()->only(['q', 'sortBy', 'canLogin', 'orderBy', 'role', 'provider'])]);
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
