<?php

namespace App\Policies;

use App\Models\Tenants\Site;
use App\Models\Tenants\User;
use App\Models\Tenants\Building;
use Illuminate\Auth\Access\Response;

class BuildingPolicy
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
    public function view(User $user, Building $building): bool
    {
        if ($user->hasRole('Admin'))
            return true;

        if ($user->hasRole('Maintenance Manager'))
            return $user->can('view locations') && $building->maintainable->manager?->id == $user->id;

        if ($user->hasRole('Provider') && $building->providers) {
            return $user->can('view locations') && array_search($user->provider?->id, $building->maintainable->providers?->pluck('id'));
        } elseif ($user->hasRole('Provider') && $building->users) {
            return $user->can('view locations') && array_search($user->id, $building->maintainable->users?->pluck('id'));
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
    public function update(User $user, Building $building): bool
    {
        if ($user->hasRole('Admin'))
            return true;

        if ($user->hasRole('Maintenance Manager'))
            return $user->can('view locations') && $building->maintainable->manager?->id == $user->id;

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Building $building): bool
    {
        return $user->can('delete locations');
    }
}
