<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Cashier\Subscription as CashierSubscription;

class Subscription extends CashierSubscription
{

    protected $fillable = [
        'type',
        'stripe_id',
        'stripe_status',
        'stripe_price',
        'quantity',
        'trial_ends_at',
        'ends_at',
    ];


    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function scopeActiveUserSubscription(Builder $query, Tenant $tenant): Builder
    {
        return
            $query->where('stripe_status', "=", 'active')->where('tenant_id', $tenant->id);
    }
}
