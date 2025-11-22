<?php

namespace App\Traits;

use App\Models\Tenants\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

trait HasMaintenanceManager
{
    public function scopeForMaintenanceManager(Builder $query, ?User $user = null)
    {
        $user = $user ?? Auth::user();

        if ($user?->hasRole('Maintenance Manager')) {
            return $query->whereHas(
                'maintainable',
                fn($q) =>
                $q->where('maintenance_manager_id', $user->id)
            );
        }

        return $query;
    }
}
