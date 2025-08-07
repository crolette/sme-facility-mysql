<?php

namespace App\Policies;

use App\Models\Tenants\User;
use App\Models\Tenants\Asset;
use Illuminate\Auth\Access\Response;

class AssetPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {

        return $user->can('view any assets');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Asset $asset): bool
    {
        if ($user->hasRole('Admin'))
            return true;

        if ($user->hasRole('Maintenance Manager'))
            return $user->can('view assets') && $asset->maintainable->manager->id == $user->id;

        if ($user->hasRole('Provider') && $asset->providers) {
            return $user->can('view assets') && array_search($user->provider?->id, $asset->maintainable->providers?->pluck('id'));
        } elseif ($user->hasRole('Provider') && $asset->users) {
            return $user->can('view assets') && array_search($user->id, $asset->maintainable->users?->pluck('id'));
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create assets');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Asset $asset): bool
    {
        if ($user->hasRole('Admin'))
            return true;

        if ($user->hasRole('Maintenance Manager'))
            return $user->can('update assets') && $asset->maintainable->manager->id == $user->id;

        if ($user->hasRole('Provider'))
            return $user->can('update assets') && array_search($user->id, $asset->providers?->pluck('id'));

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Asset $asset): bool
    {
        if ($user->hasRole('Admin'))
            return true;

        if ($user->hasRole('Maintenance Manager'))
            return $user->can('delete assets') && $asset->maintainable->manager->id == $user->id;

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Asset $asset): bool
    {
        return $user->can('restore assets');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Asset $asset): bool
    {
        return $user->can('force delete assets');
    }
}
