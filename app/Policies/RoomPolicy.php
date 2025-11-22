<?php

namespace App\Policies;

use App\Models\Tenants\Room;
use App\Models\Tenants\User;

class RoomPolicy
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
    public function view(User $user, Room $room): bool
    {
        if ($user->hasRole('Admin'))
            return true;

        if ($user->hasRole('Maintenance Manager'))
            return $user->can('view locations') && $room->maintainable->manager?->id == $user->id;

        if ($user->hasRole('Provider') && $room->providers) {
            return $user->can('view locations') && array_search($user->provider?->id, $room->maintainable->providers?->pluck('id'));
        } elseif ($user->hasRole('Provider') && $room->users) {
            return $user->can('view locations') && array_search($user->id, $room->maintainable->users?->pluck('id'));
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
    public function update(User $user, Room $room): bool
    {
        if ($user->hasRole('Admin'))
            return true;

        if ($user->hasRole('Maintenance Manager'))
            return $user->can('view locations') && $room->maintainable->manager?->id == $user->id;

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Room $room): bool
    {
        return $user->can('delete locations');
    }
}
