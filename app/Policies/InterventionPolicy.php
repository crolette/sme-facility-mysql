<?php

namespace App\Policies;

use App\Models\Tenants\User;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Intervention;
use Illuminate\Auth\Access\Response;

class InterventionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view any interventions');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Intervention $intervention): bool
    {
        if ($user->hasRole('Admin'))
            return true;

        if ($user->hasRole('Maintenance Manager'))
            return $user->can('view interventions') && $intervention->maintainable->manager?->id == $user->id;

        if ($user->hasRole('Provider') && $intervention->providers) {
            return $user->can('view interventions') && array_search($user->provider?->id, $intervention->maintainable->providers?->pluck('id'));
        } elseif ($user->hasRole('Provider') && $intervention->users) {
            return $user->can('view interventions') && array_search($user->id, $intervention->maintainable->users?->pluck('id'));
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create interventions');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Intervention $intervention): bool
    {
        if ($user->hasRole('Admin'))
            return true;

        if ($user->hasRole('Maintenance Manager'))
            return $user->can('update interventions') && $intervention->maintainable->manager?->id == $user->id;

        if ($user->hasRole('Provider'))
            return $user->can('update interventions') && array_search($user->id, $intervention->providers?->pluck('id'));

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Intervention $intervention): bool
    {
        if ($user->hasRole('Admin'))
            return true;

        if ($user->hasRole('Maintenance Manager'))
            return $user->can('delete interventions') && $intervention->maintainable->manager?->id == $user->id;

        return false;
    }
}
