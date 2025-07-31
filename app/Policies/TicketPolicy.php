<?php

namespace App\Policies;

use App\Models\Central\CentralUser;
use App\Models\Ticket;
use Illuminate\Auth\Access\Response;

class TicketPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(CentralUser $centralUser): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(CentralUser $centralUser, Ticket $ticket): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(CentralUser $centralUser): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(CentralUser $centralUser, Ticket $ticket): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(CentralUser $centralUser, Ticket $ticket): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(CentralUser $centralUser, Ticket $ticket): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(CentralUser $centralUser, Ticket $ticket): bool
    {
        return false;
    }
}
