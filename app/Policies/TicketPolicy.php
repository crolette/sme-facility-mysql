<?php

namespace App\Policies;

use App\Models\Tenants\User;
use App\Models\Tenants\Ticket;
use Illuminate\Auth\Access\Response;

class TicketPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view any tickets');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Ticket $ticket): bool
    {
        if($user->hasRole('Maintenance Manager')) {
            return $user->can('view tickets') && $user->id === $ticket->ticketable->manager->id ? true : false;
        }

        return $user->can('view tickets');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // return $user->can('create tickets');
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Ticket $ticket): bool
    {
        if ($user->hasRole('Maintenance Manager')) {
            return $user->can('update tickets') && $user->id === $ticket->ticketable->manager->id ? true : false;
        }
    
        // dump('--- after Maintenacne Manager --- ');
        return $user->can('update tickets');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Ticket $ticket): bool
    {
        return $user->can('delete tickets');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Ticket $ticket): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Ticket $ticket): bool
    {
        return false;
    }
}
