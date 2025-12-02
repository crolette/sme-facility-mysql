<?php

namespace App\Listeners;

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
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

        if ($event->payload['type'] === 'customer.created') {
            Log::info('customer.created');
            Log::info($event->payload);
            $data = $event->payload['data']['object'];

            $tenant = Tenant::where('email', '=', $data['email'])->first();
            if (isset($data['id']) && $tenant) {
                $tenant->update(['stripe_id' => $data['id']]);
            } else {
                Log::info('no tenant found : ' . $data['id'] . ' - ' . $data['email']);
            }
        }

        if ($event->payload['type'] === 'customer.deleted') {
            Log::info('STRIPE customer.deleted');
            Log::info($event->payload);

            $data = $event->payload['data']['object'];

            $tenant = Tenant::where('stripe_id', '=', $data['id'])->first();
            if (isset($data['id']) && $tenant) {
                $tenant->update(['stripe_id' => null]);
            } else {
                Log::info('no tenant found : ' . $data['id'] . ' - ' . $data['email']);
            }
        }

        if ($event->payload['type'] === 'customer.updated') {
            Log::info("Webhook Stripe: Customer Updated");
            Log::info($event->payload);
        }


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


        if ($event->payload['type'] === 'customer.tax_id.updated') {
            Log::info('STRIPE customer.tax_id.updated');
            Log::info($event->payload);
            $data = $event->payload['data']['object'];

            Log::info('VAT Number', [$data['value']]);

            $tenant = Tenant::where('vat_number', '=',  $data['value'])->first();

            if (isset($data['verification']) && $tenant) {
                $tenant->update([
                    'verified_vat_status' => $data['verification']['status'],
                    'stripe_id' => $data['customer']
                ]);
            } else {
                Log::info('No tenant found', ['stripe_id' => $data['customer'], 'vat_number' => $data['value']]);
            }
        }
        if ($event->payload['type'] === 'customer.tax_id.created') {
            Log::info('STRIPE customer.tax_id.created');
            Log::info($event->payload);

            $data = $event->payload['data']['object'];
            Log::info('VAT Number', [$data['value']]);

            $tenant = Tenant::where('vat_number', '=',  $data['value'])->first();

            if (isset($data['verification']) && $tenant) {
                $tenant->update([
                    'verified_vat_status' => $data['verification']['status'],
                    'stripe_id' => $data['customer']
                ]);
            } else {
                Log::info('No tenant found', ['stripe_id' => $data['customer'], 'vat_number' => $data['value']]);
            }
        }
    }
}
