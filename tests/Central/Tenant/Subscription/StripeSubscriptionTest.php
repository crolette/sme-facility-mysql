<?php

use App\Models\Tenant;
use App\Models\Address;
use App\Enums\AddressTypes;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\DB;
use App\Models\Central\CentralUser;
use App\Listeners\StripeEventListener;
use function PHPUnit\Framework\assertTrue;
use Tests\Concerns\ManagesTenantDatabases;

use function PHPUnit\Framework\assertFalse;
use Laravel\Cashier\Events\WebhookReceived;
use function Pest\Laravel\assertDatabaseHas;
use function PHPUnit\Framework\assertNotNull;
use function Pest\Laravel\assertDatabaseMissing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Http\Middleware\CustomInitializeTenancyBySubdomain;

uses(ManagesTenantDatabases::class);

beforeEach(function () {
    $user = CentralUser::factory()->create();
    $this->actingAs($user);

    $this->withoutMiddleware([
        \Stancl\Tenancy\Middleware\InitializeTenancyByDomain::class,
        CustomInitializeTenancyBySubdomain::class,
        \Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain::class,
        \Stancl\Tenancy\Middleware\InitializeTenancyByPath::class,
        \Stancl\Tenancy\Middleware\InitializeTenancyByRequestData::class,
    ]);

    $this->subscriptionCreated = [
        'type' => 'customer.subscription.created',
        'id' => 'evt_1SZwGIFHXryfbBkb0zgVdpJF',
        'object' => 'event',
        'api_version' => '2025-11-17.clover',
        'created' => 1764692229,
        'data' => [
            'object' => [
                'id' => 'sub_1SZwGHFHXryfbBkbrcZS0iyV',
                'object' => 'subscription',
                'application' => null,
                'application_fee_percent' => null,
                'automatic_tax' => [
                    'disabled_reason' => null,
                    'enabled' => true,
                    'liability' => [
                        'type' => 'self',
                    ],
                ],
                'billing_cycle_anchor' => 1764864952,
                'billing_cycle_anchor_config' => null,
                'billing_mode' => [
                    'flexible' => null,
                    'type' => 'classic',
                ],
                'billing_thresholds' => null,
                'cancel_at' => null,
                'cancel_at_period_end' => false,
                'canceled_at' => null,
                'cancellation_details' => [
                    'comment' => null,
                    'feedback' => null,
                    'reason' => null,
                ],
                'collection_method' => 'charge_automatically',
                'created' => 1764692228,
                'currency' => 'eur',
                'customer' => 'cus_test',
                'days_until_due' => null,
                'default_payment_method' => 'pm_1SZwGFFHXryfbBkbAcZtEDT3',
                'default_source' => null,
                'default_tax_rates' => [],
                'description' => null,
                'discounts' => [],
                'ended_at' => null,
                'invoice_settings' => [
                    'account_tax_ids' => null,
                    'issuer' => [
                        'type' => 'self',
                    ],
                ],
                'items' => [
                    'object' => 'list',
                    'data' => [
                        [
                            'id' => 'si_TX0GywQD07X4HV',
                            'object' => 'subscription_item',
                            'billing_thresholds' => null,
                            'created' => 1764692228,
                            'current_period_end' => 1764864952,
                            'current_period_start' => 1764692228,
                            'discounts' => [],
                            'metadata' => [],
                            'plan' => [
                                'id' => 'price_1SZXmvFHXryfbBkbFnFMYnTJ',
                                'object' => 'plan',
                                'active' => true,
                                'amount' => 29900,
                                'amount_decimal' => '29900',
                                'billing_scheme' => 'per_unit',
                                'created' => 1764598153,
                                'currency' => 'eur',
                                'interval' => 'month',
                                'interval_count' => 1,
                                'livemode' => false,
                                'metadata' => [],
                                'meter' => null,
                                'nickname' => null,
                                'product' => 'prod_TWaytwcuX4Mb03',
                                'tiers_mode' => null,
                                'transform_usage' => null,
                                'trial_period_days' => null,
                                'usage_type' => 'licensed',
                            ],
                            'price' => [
                                'id' => 'price_1SZXmvFHXryfbBkbFnFMYnTJ',
                                'object' => 'price',
                                'active' => true,
                                'billing_scheme' => 'per_unit',
                                'created' => 1764598153,
                                'currency' => 'eur',
                                'custom_unit_amount' => null,
                                'livemode' => false,
                                'lookup_key' => null,
                                'metadata' => [],
                                'nickname' => null,
                                'product' => 'prod_TWaytwcuX4Mb03',
                                'recurring' => [
                                    'interval' => 'month',
                                    'interval_count' => 1,
                                    'meter' => null,
                                    'trial_period_days' => null,
                                    'usage_type' => 'licensed',
                                ],
                                'tax_behavior' => 'exclusive',
                                'tiers_mode' => null,
                                'transform_quantity' => null,
                                'type' => 'recurring',
                                'unit_amount' => 29900,
                                'unit_amount_decimal' => '29900',
                            ],
                            'quantity' => 1,
                            'subscription' => 'sub_1SZwGHFHXryfbBkbrcZS0iyV',
                            'tax_rates' => [],
                        ],
                    ],
                    'has_more' => false,
                    'total_count' => 1,
                    'url' => '/v1/subscription_items?subscription=sub_1SZwGHFHXryfbBkbrcZS0iyV',
                ],
                'latest_invoice' => 'in_1SZwGGFHXryfbBkbv18KzNO2',
                'livemode' => false,
                'metadata' => [
                    'is_on_session_checkout' => 'true',
                    'type' => 'prod_TWaytwcuX4Mb03',
                    'name' => 'prod_TWaytwcuX4Mb03',
                ],
                'next_pending_invoice_item_invoice' => null,
                'on_behalf_of' => null,
                'pause_collection' => null,
                'payment_settings' => [
                    'payment_method_options' => [
                        'acss_debit' => null,
                        'bancontact' => null,
                        'card' => [
                            'network' => null,
                            'request_three_d_secure' => 'automatic',
                        ],
                        'customer_balance' => null,
                        'konbini' => null,
                        'sepa_debit' => null,
                        'us_bank_account' => null,
                    ],
                    'payment_method_types' => null,
                    'save_default_payment_method' => 'off',
                ],
                'pending_invoice_item_interval' => null,
                'pending_setup_intent' => null,
                'pending_update' => null,
                'plan' => [
                    'id' => 'price_1SZXmvFHXryfbBkbFnFMYnTJ',
                    'object' => 'plan',
                    'active' => true,
                    'amount' => 29900,
                    'amount_decimal' => '29900',
                    'billing_scheme' => 'per_unit',
                    'created' => 1764598153,
                    'currency' => 'eur',
                    'interval' => 'month',
                    'interval_count' => 1,
                    'livemode' => false,
                    'metadata' => [],
                    'meter' => null,
                    'nickname' => null,
                    'product' => 'prod_TWaytwcuX4Mb03',
                    'tiers_mode' => null,
                    'transform_usage' => null,
                    'trial_period_days' => null,
                    'usage_type' => 'licensed',
                ],
                'quantity' => 1,
                'schedule' => null,
                'start_date' => 1764692228,
                'status' => 'trialing',
                'test_clock' => null,
                'transfer_data' => null,
                'trial_end' => 1764864952,
                'trial_settings' => [
                    'end_behavior' => [
                        'missing_payment_method' => 'create_invoice',
                    ],
                ],
                'trial_start' => 1764692228,
            ],
        ],
        'livemode' => false,
        'pending_webhooks' => 3,
        'request' => [
            'id' => null,
            'idempotency_key' => '03043e19-8c6a-4e41-80fa-3d8130569a01',
        ],

    ];

    $this->subscriptionUpdated = [
        'type' => 'customer.subscription.updated',
        'id' => 'evt_1Sa2YXFHXryfbBkbIsD0hz4k',
        'object' => 'event',
        'api_version' => '2025-11-17.clover',
        'created' => 1764716425,
        'data' => [
            'object' => [
                'id' => 'sub_1SZwGHFHXryfbBkbrcZS0iyV',
                'object' => 'subscription',
                'application' => null,
                'application_fee_percent' => null,
                'automatic_tax' => [
                    'disabled_reason' => null,
                    'enabled' => true,
                    'liability' => [
                        'type' => 'self',
                    ],
                ],
                'billing_cycle_anchor' => 1764864952,
                'billing_cycle_anchor_config' => null,
                'billing_mode' => [
                    'flexible' => null,
                    'type' => 'classic',
                ],
                'billing_thresholds' => null,
                'cancel_at' => 1780417028,
                'cancel_at_period_end' => false,
                'canceled_at' => 1764716425,
                'cancellation_details' => [
                    'comment' => null,
                    'feedback' => null,
                    'reason' => 'cancellation_requested',
                ],
                'collection_method' => 'charge_automatically',
                'created' => 1764692228,
                'currency' => 'eur',
                'customer' => 'cus_test',
                'days_until_due' => null,
                'default_payment_method' => 'pm_1SZwGFFHXryfbBkbAcZtEDT3',
                'default_source' => null,
                'default_tax_rates' => [],
                'description' => null,
                'discounts' => [],
                'ended_at' => null,
                'invoice_settings' => [
                    'account_tax_ids' => null,
                    'issuer' => [
                        'type' => 'self',
                    ],
                ],
                'items' => [
                    'object' => 'list',
                    'data' => [
                        [
                            'id' => 'si_TX0GywQD07X4HV',
                            'object' => 'subscription_item',
                            'billing_thresholds' => null,
                            'created' => 1764692228,
                            'current_period_end' => 1764864952,
                            'current_period_start' => 1764692228,
                            'discounts' => [],
                            'metadata' => [],
                            'plan' => [
                                'id' => 'price_1SZXmvFHXryfbBkbFnFMYnTJ',
                                'object' => 'plan',
                                'active' => true,
                                'amount' => 29900,
                                'amount_decimal' => '29900',
                                'billing_scheme' => 'per_unit',
                                'created' => 1764598153,
                                'currency' => 'eur',
                                'interval' => 'month',
                                'interval_count' => 1,
                                'livemode' => false,
                                'metadata' => [],
                                'meter' => null,
                                'nickname' => null,
                                'product' => 'prod_TWaytwcuX4Mb03',
                                'tiers_mode' => null,
                                'transform_usage' => null,
                                'trial_period_days' => null,
                                'usage_type' => 'licensed',
                            ],
                            'price' => [
                                'id' => 'price_1SZXmvFHXryfbBkbFnFMYnTJ',
                                'object' => 'price',
                                'active' => true,
                                'billing_scheme' => 'per_unit',
                                'created' => 1764598153,
                                'currency' => 'eur',
                                'custom_unit_amount' => null,
                                'livemode' => false,
                                'lookup_key' => null,
                                'metadata' => [],
                                'nickname' => null,
                                'product' => 'prod_TWaytwcuX4Mb03',
                                'recurring' => [
                                    'interval' => 'month',
                                    'interval_count' => 1,
                                    'meter' => null,
                                    'trial_period_days' => null,
                                    'usage_type' => 'licensed',
                                ],
                                'tax_behavior' => 'exclusive',
                                'tiers_mode' => null,
                                'transform_quantity' => null,
                                'type' => 'recurring',
                                'unit_amount' => 29900,
                                'unit_amount_decimal' => '29900',
                            ],
                            'quantity' => 1,
                            'subscription' => 'sub_1SZwGHFHXryfbBkbrcZS0iyV',
                            'tax_rates' => [],
                        ],
                    ],
                    'has_more' => false,
                    'total_count' => 1,
                    'url' => '/v1/subscription_items?subscription=sub_1SZwGHFHXryfbBkbrcZS0iyV',
                ],
                'latest_invoice' => 'in_1SZwGGFHXryfbBkbv18KzNO2',
                'livemode' => false,
                'metadata' => [
                    'is_on_session_checkout' => 'true',
                    'type' => 'prod_TWaytwcuX4Mb03',
                    'name' => 'prod_TWaytwcuX4Mb03',
                ],
                'next_pending_invoice_item_invoice' => null,
                'on_behalf_of' => null,
                'pause_collection' => null,
                'payment_settings' => [
                    'payment_method_options' => null,
                    'payment_method_types' => null,
                    'save_default_payment_method' => null,
                ],
                'pending_invoice_item_interval' => null,
                'pending_setup_intent' => null,
                'pending_update' => null,
                'plan' => [
                    'id' => 'price_1SZXmvFHXryfbBkbFnFMYnTJ',
                    'object' => 'plan',
                    'active' => true,
                    'amount' => 29900,
                    'amount_decimal' => '29900',
                    'billing_scheme' => 'per_unit',
                    'created' => 1764598153,
                    'currency' => 'eur',
                    'interval' => 'month',
                    'interval_count' => 1,
                    'livemode' => false,
                    'metadata' => [],
                    'meter' => null,
                    'nickname' => null,
                    'product' => 'prod_TWaytwcuX4Mb03',
                    'tiers_mode' => null,
                    'transform_usage' => null,
                    'trial_period_days' => null,
                    'usage_type' => 'licensed',
                ],
                'quantity' => 1,
                'schedule' => 'sub_sched_1Sa2YMFHXryfbBkbzqWFKwK1',
                'start_date' => 1764692228,
                'status' => 'trialing',
                'test_clock' => null,
                'transfer_data' => null,
                'trial_end' => 1764864952,
                'trial_settings' => [
                    'end_behavior' => [
                        'missing_payment_method' => 'create_invoice',
                    ],
                ],
                'trial_start' => 1764692228,
            ],
            'previous_attributes' => [
                'cancel_at' => null,
                'canceled_at' => null,
                'cancellation_details' => [
                    'reason' => null,
                ],
                'payment_settings' => [
                    'payment_method_options' => [
                        'acss_debit' => null,
                        'bancontact' => null,
                        'card' => [
                            'network' => null,
                            'request_three_d_secure' => 'automatic',
                        ],
                        'customer_balance' => null,
                        'konbini' => null,
                        'sepa_debit' => null,
                        'us_bank_account' => null,
                    ],
                    'save_default_payment_method' => 'off',
                ],
            ],
        ],
        'livemode' => false,
        'pending_webhooks' => 3,
        'request' => [
            'id' => 'req_OlCEExHTIcMYtA',
            'idempotency_key' => 'f96aff95-914b-4d4c-a590-ab1a7bc10cbb',
        ],

    ];
});

it('creates subscription restrictions when subscription is created', function () {

    try {
        $tenant = Tenant::factory()->create(['stripe_id' => 'cus_test']);
        $tenant->subscriptions()->create(['type' => 'prod_TWaytwcuX4Mb03', 'stripe_status' => 'active', 'stripe_id' => 'sub_1SZwGHFHXryfbBkbrcZS0iyV', 'stripe_price' => 'price_1SZXmvFHXryfbBkbFnFMYnTJ', 'quantity' => 1]);

        $event = new WebhookReceived($this->subscriptionCreated);

        (new StripeEventListener)->handle($event);

        $tenant->refresh();

        expect($tenant->max_sites)->toBe(2);
        expect($tenant->max_users)->toBe(15);
        expect($tenant->max_storage_gb)->toBe(15);
        expect($tenant->has_statistics)->toBeTrue();
    } finally {
        $tenant = Tenant::where('stripe_id', 'cus_test')->first();
        if ($tenant)
            $tenant->delete();



        $subscription = $tenant->subscriptions()->where('type', 'prod_TWaytwcuX4Mb03')->first();
        if ($subscription)
            $subscription->delete();
    }
});

it('updates subscription restrictions when subscription is updated', function () {

    try {
        $tenant = Tenant::factory()->create(['stripe_id' => 'cus_test']);
        $tenant->subscriptions()->create(['type' => 'prod_TWaytwcuX4Mb03', 'stripe_status' => 'active', 'stripe_id' => 'sub_1SZwGHFHXryfbBkbrcZS0iyV', 'stripe_price' => 'price_1SZXmvFHXryfbBkbFnFMYnTJ', 'quantity' => 1]);

        $event = new WebhookReceived($this->subscriptionUpdated);

        (new StripeEventListener)->handle($event);

        $tenant->refresh();

        expect($tenant->max_sites)->toBe(2);
        expect($tenant->max_users)->toBe(15);
        expect($tenant->max_storage_gb)->toBe(15);
        expect($tenant->has_statistics)->toBeTrue();
    } finally {
        $tenant = Tenant::where('stripe_id', 'cus_test')->first();
        if ($tenant)
            $tenant->delete();



        $subscription = $tenant->subscriptions()->where('type', 'prod_TWaytwcuX4Mb03')->first();
        if ($subscription)
            $subscription->delete();
    }
});
