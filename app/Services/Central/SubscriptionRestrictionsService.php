<?php

namespace App\Services\Central;

use Illuminate\Support\Facades\Log;
use App\Models\Central\Subscription;
use Illuminate\Support\Facades\Cache;

class SubscriptionRestrictionsService
{
    public function updateSubscriptionRestrictions($subscriptionId)
    {
        $subscription = Subscription::where('stripe_id', $subscriptionId)->first();
        if ($subscription) {
            $tenant = $subscription->tenant;

            if ($subscription->stripe_status === 'trialing' || $subscription->stripe_status === 'active') {
                $stripe = new \Stripe\StripeClient(config('cashier.secret'));
                $price = $stripe->prices->retrieve($subscription->stripe_price, ['expand' => ['product']]);
                Log::info('update restrictions', ['metadata' => $price->product->metadata]);
                $tenant->update(
                    [
                        'max_sites' => intval($price->product->metadata->sites) ?? 0,
                        'max_users' => intval($price->product->metadata->users) ?? 0,
                        'max_storage_gb' => intval($price->product->metadata->storage) ?? 0,
                        'has_statistics' => $price->product->metadata->statistics === 'true' ? true : false,
                    ]
                );
                // Cache::forget("tenant:{$tenant->id}:limits");
            } else {
                $this->removeSubscriptionRestrictions($subscriptionId);
            }
        }
    }

    public function removeSubscriptionRestrictions($subscriptionId)
    {
        $subscription = Subscription::where('stripe_id', $subscriptionId)->first();
        if ($subscription) {

            $tenant = $subscription->tenant;
            $tenant->update(
                [
                    'max_sites' => 0,
                    'max_users' => 0,
                    'max_storage_gb' => 0,
                    'has_statistics' => false,
                ]
            );
            // Cache::forget("tenant:{$tenant->id}:limits");
        }
    }
}
