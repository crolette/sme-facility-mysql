<?php

namespace App\Policies;

use App\Models\Tenants\User;
use App\Models\Tenants\Floor;

class FloorPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view any locations');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Floor $floor): bool
    {
        if ($user->hasRole('Admin'))
            return true;

        if ($user->hasRole('Maintenance Manager'))
            return $user->can('view locations') && $floor->maintainable->manager?->id == $user->id;

        if ($user->hasRole('Provider') && $floor->providers) {
            return $user->can('view locations') && array_search($user->provider?->id, $floor->maintainable->providers?->pluck('id'));
        } elseif ($user->hasRole('Provider') && $floor->users) {
            return $user->can('view locations') && array_search($user->id, $floor->maintainable->users?->pluck('id'));
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create locations');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Floor $floor): bool
    {
        if ($user->hasRole('Admin'))
            return true;

        if ($user->hasRole('Maintenance Manager'))
            return $user->can('view locations') && $floor->maintainable->manager?->id == $user->id;

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Floor $floor): bool
    {
        return $user->can('delete locations');
    }
}
