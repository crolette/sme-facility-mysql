<?php

namespace App\Policies;

use App\Models\Tenants\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    // public function before(User $user, string $ability): bool|null
    // {
    //     if ($user->hasRole('Maintenance Manager')) {
    //         return true;
    //     }

    //     return null;
    // }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view any users');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        if ($model->hasRole('Super Admin', 'tenant'))
            return false;

        if ($user->hasPermissionTo('view any users')) {
            return true;
        } else {
            return $user->can('view users');
            // return $user->can('view users') && $user->id == $model->id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create users');
    }


    /**
     * Determine whether the user can update the model.
     */
    public function updateOwn(User $user, User $model): bool
    {
        if ($model->hasRole('Super Admin', 'tenant'))
            return false;


        return $user->can('update users') && $user->id === $model->id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        if ($model->hasRole('Super Admin', 'tenant'))
            return false;


        return $user->can('update users');
        // return $user->can('update users') && $user->id !== $model->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        if ($model->hasRole('Super Admin', 'tenant'))
            return false;


        return $user->can('delete users') && $user->id !== $model->id;
    }
}
