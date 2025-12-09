<?php

namespace App\Services\Central;

use Illuminate\Support\Facades\Log;
use App\Models\Central\Subscription;
use Illuminate\Support\Facades\Cache;

class SubscriptionRestrictionsService
{
    public function updateSubscriptionRestrictions($subscriptionId, $subscriptionPlan)
    {
        $subscription = Subscription::where('stripe_id', $subscriptionId)->first();


        //FIXME Mettre à jour Cache TenantLimits 

        if ($subscription) {
            $tenant = $subscription->tenant;

            if ($subscription->stripe_status === 'trialing' || $subscription->stripe_status === 'active') {
                $stripe = new \Stripe\StripeClient(config('cashier.secret'));
                $price = $stripe->prices->retrieve($subscriptionPlan['id'], ['expand' => ['product']]);
                Log::info('product', ['product' => $price->product]);
                Log::info('update restrictions', ['metadata' => $price->product->metadata]);
                $tenant->update(
                    [
                        'max_sites' => intval($price->product->metadata->sites) ?? 0,
                        'max_users' => intval($price->product->metadata->users) ?? 0,
                        'max_storage_gb' => intval($price->product->metadata->storage) ?? 0,
                        'has_statistics' => $price->product->metadata->statistics === 'true' ? true : false,
                    ]
                );
                $this->updateSubscriptionInfoTenant($tenant, $subscriptionPlan, $price->product->name);
                // Cache::forget("tenant:{$tenant->id}:limits");
            } else {
                $this->removeSubscriptionRestrictions($subscriptionId);
            }

            $this->updateTenantLimitCache($tenant);
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
            $this->updateSubscriptionInfoTenant($tenant, null, null);
        }
    }

    public function updateSubscriptionInfoTenant($tenant, $plan, $productName)
    {
        $tenant->update(['subscription_plan' => $plan['interval'], 'subscription_name' => $productName]);
    }

    public function updateTenantLimitCache($tenant)
    {
        tenancy()->initialize($tenant);

        // TODO improve/refactor in service ?

        Cache::forget("tenant:{$tenant->id}:limits");
        Cache::remember(
            "tenant:{$tenant->id}:limits",
            now()->addDay(),
            fn() => [
                "max_sites" => $tenant->max_sites,
                "max_users" => $tenant->max_users,
                "max_storage_gb" => $tenant->max_storage_gb,
                "has_statistics" => $tenant->has_statistics,
            ]
        );

        tenancy()->end();
    }
}
