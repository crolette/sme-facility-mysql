<?php

namespace App\Policies;

use App\Models\Tenants\Site;
use App\Models\Tenants\User;
use App\Services\TenantLimits;
use Illuminate\Auth\Access\Response;
use Barryvdh\Debugbar\Facades\Debugbar;

class SitePolicy
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
    public function view(User $user, Site $site): bool
    {
        if ($user->hasRole('Admin'))
            return true;

        if ($user->hasRole('Maintenance Manager'))
            return $user->can('view locations') && $site->maintainable->manager?->id == $user->id;

        if ($user->hasRole('Provider') && $site->providers) {
            return $user->can('view locations') && array_search($user->provider?->id, $site->maintainable->providers?->pluck('id'));
        } elseif ($user->hasRole('Provider') && $site->users) {
            return $user->can('view locations') && array_search($user->id, $site->maintainable->users?->pluck('id'));
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create locations') && TenantLimits::canCreateSite();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Site $site): bool
    {
        if ($user->hasRole('Admin'))
            return true;

        if ($user->hasRole('Maintenance Manager'))
            return $user->can('view locations') && $site->maintainable->manager?->id == $user->id;

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Site $site): bool
    {
        return $user->can('delete locations');
    }
}
