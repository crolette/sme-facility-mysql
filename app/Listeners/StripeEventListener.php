<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Log;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Laravel\Cashier\Events\WebhookReceived;

class StripeEventListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(WebhookReceived $event): void
    {
        //
        Log::info('webhookstripe');
        if ($event->payload['type'] === 'invoice.payment_succeeded') {
            Log::info('invoice.payment_succeeded');
            Log::info($event->payload);
        }

        if ($event->payload['type'] === 'customer.subscription.updated') {
            Log::info('customer.subscription.updated');
            Log::info($event->payload);
        }
        if ($event->payload['type'] === 'customer.subscription.deleted') {
            Log::info('customer.subscription.deleted');
            Log::info($event->payload);
        }
        if ($event->payload['type'] === 'customer.subscription.created') {
            Log::info('customer.subscription.created');
            Log::info($event->payload);
        }

        if ($event->payload['type'] === 'customer.created') {
            Log::info('customer.created');
            Log::info($event->payload);
        }

        if ($event->payload['type'] === 'customer.deleted') {
            Log::info('STRIPE customer.deleted');
            Log::info($event->payload);
        }

        if ($event->payload['type'] === 'customer.updated') {
            Log::info("Webhook Stripe: Customer Updated");
            Log::info($event->payload);
        }

        if ($event->payload['type'] === 'customer.tax_id.updated') {
            Log::info('STRIPE customer.tax_id.updated');
            Log::info($event->payload);
        }
        if ($event->payload['type'] === 'customer.tax_id.created') {
            Log::info('STRIPE customer.tax_id.created');
            Log::info($event->payload);
        }
    }
}
