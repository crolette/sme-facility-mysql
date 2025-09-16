<?php

namespace App\Policies;

use App\Models\Tenants\Provider;
use App\Models\Tenants\User;
use Illuminate\Auth\Access\Response;

class ProviderPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view any providers');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Provider $provider): bool
    {
        return $user->can('view providers');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create providers');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Provider $provider): bool
    {
        return $user->can('update providers');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Provider $provider): bool
    {
        return $user->can('delete providers');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(CentralUser $centralUser, Provider $provider): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(CentralUser $centralUser, Provider $provider): bool
    {
        return false;
    }
}
