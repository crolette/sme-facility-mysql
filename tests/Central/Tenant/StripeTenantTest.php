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
});

// it('creates a stripe customer and returns stripe ID when creating a new tenant', function () {

//     $companyAddress = Address::factory()->make();
//     $invoiceAddress = Address::factory()->make(['address_type' => AddressTypes::INVOICE->value]);
//     $faker = \Faker\Factory::create('fr_BE');

//     $formData = [
//         'company_name' =>  $faker->company(),
//         'first_name' => 'Michel',
//         'last_name' => 'Dupont',
//         'email' => fake()->email(),
//         'vat_number' => $faker->vat(false),
//         'domain_name' => $faker->regexify('[a-z]{4}'),
//         'company_code' => Str::lower(Str::random(4)),
//         'phone_number' => '+3242212215',
//         'company' => [
//             'street' => $companyAddress->street,
//             'house_number' => $companyAddress->house_number,
//             'zip_code' => $companyAddress->zip_code,
//             'city' => $companyAddress->city,
//             'country' => $companyAddress->country,
//         ],
//         'same_address_as_company' => false,
//         'invoice' => [
//             'street' => $invoiceAddress->street,
//             'house_number' => $invoiceAddress->house_number,
//             'zip_code' => $invoiceAddress->zip_code,
//             'city' => $invoiceAddress->city,
//             'country' => $invoiceAddress->country,
//         ],
//     ];

//     $response = $this->post(route('central.tenants.store'), $formData);
//     $response->assertSessionHasNoErrors();

//     assertDatabaseHas('tenants', [
//         'id' => $formData['company_code'],
//     ]);

//     $tenant = Tenant::find($formData['company_code']);
//     expect($tenant->stripe_id)->not->toBeNull()->toStartWith('cus_');

//     $stripeCustomer = $tenant->asStripeCustomer();
//     expect($stripeCustomer->email)->toBe($tenant->email);
//     expect($stripeCustomer->address->country)->toBe($formData['company']['country']);
//     expect($stripeCustomer->business_name)->toBe($tenant->company_name);
//     expect($stripeCustomer->individual_name)->toBe($tenant->first_name . ' ' . $tenant->last_name);
//     expect($stripeCustomer->phone)->toBe($tenant->phone_number);

//     $tenant->asStripeCustomer()->delete();
//     $tenant->delete();
// })->group('stripe-integration');

// it('adds the stripe ID to tenant if customer is created in Stripe', function () {
//     $email = fake()->email();
//     $tenant = Tenant::factory()->create(['email' => $email]);
//     expect(Tenant::where('email', $email)->exists())->toBeTrue();

//     $event = new WebhookReceived([
//         'type' => 'customer.created',
//         'data' =>
//         [
//             'object' =>
//             [
//                 'id' => 'cus_test',
//                 'email' => $email,
//             ],

//         ]
//     ]);

//     // Appeler directement ton listener
//     (new StripeEventListener)->handle($event);

//     expect($tenant->fresh()->stripe_id)->not->toBeNull()->toStartWith('cus_');
// });

// it('updates the tax status from Stripe webhook tax_id.created', function () {
//     $tenant = Tenant::factory()->create(['stripe_id' => 'cus_test']);

//     $event = new WebhookReceived([
//         'type' => 'customer.tax_id.created',
//         'data' =>
//         [
//             'object' =>
//             [
//                 'customer' => 'cus_test',
//                 'verification' =>
//                 [
//                     'status' => 'pending'
//                 ],
//                 'value' => $tenant->vat_number
//             ],

//         ]
//     ]);

//     // Appeler directement ton listener
//     (new StripeEventListener)->handle($event);

//     expect($tenant->fresh()->verified_vat_status)->toBe('pending');
//     $tenant->delete();
// });

// it('updates the tax status from Stripe webhook tax_id.updated', function () {
//     $tenant = Tenant::factory()->create(['stripe_id' => 'cus_test']);

//     $event = new WebhookReceived([
//         'type' => 'customer.tax_id.updated',
//         'data' =>
//         [
//             'object' =>
//             [
//                 'customer' => 'cus_test',
//                 'name' => 'Updated Name',
//                 'verification' =>
//                 [
//                     'status' => 'verified'
//                 ],
//                 'value' => $tenant->vat_number
//             ],

//         ]
//     ]);

//     // Appeler directement ton listener
//     (new StripeEventListener)->handle($event);

//     expect($tenant->fresh()->verified_vat_status)->toBe('verified');
//     $tenant->delete();
// });

it('removes the stripe_id if customer is deleted on Stripe', function () {
    $tenant = Tenant::factory()->create(['stripe_id' => 'cus_test']);

    $event = new WebhookReceived([
        'type' => 'customer.deleted',
        'data' =>
        [
            'object' =>
            [
                'id' => 'cus_test',
            ],

        ]
    ]);

    // Appeler directement ton listener
    (new StripeEventListener)->handle($event);

    expect($tenant->fresh()->stripe_id)->toBeNull();
    $tenant->delete();
});

// it('updates the tax status from Stripe webhook for', function ($taxId, $status) {

//     $companyAddress = Address::factory()->make();
//     $faker = \Faker\Factory::create('fr_BE');

//     $formData = [
//         'company_name' =>  $faker->company(),
//         'first_name' => 'Michel',
//         'last_name' => 'Dupont',
//         'email' => fake()->email(),
//         'vat_number' => $taxId,
//         'domain_name' => $faker->regexify('[a-z]{4}'),
//         'company_code' => Str::lower(Str::random(4)),
//         'phone_number' => '+3242212215',
//         'company' => [
//             'street' => $companyAddress->street,
//             'house_number' => $companyAddress->house_number,
//             'zip_code' => $companyAddress->zip_code,
//             'city' => $companyAddress->city,
//             'country' => $companyAddress->country,
//         ],
//         'same_address_as_company' => true,
//     ];

//     $response = $this->post(route('central.tenants.store'), $formData);
//     $response->assertSessionHasNoErrors();

//     $tenant = Tenant::find($formData['company_code']);

//     $event = new WebhookReceived([
//         'type' => 'customer.tax_id.updated',
//         'data' =>
//         [
//             'object' =>
//             [
//                 'customer' => $tenant->stripe_id,
//                 'name' => 'Updated Name',
//                 'verification' =>
//                 [
//                     'status' => $status
//                 ]
//             ],

//         ]
//     ]);

//     // Appeler directement ton listener
//     (new StripeEventListener)->handle($event);

//     expect($tenant->verified_vat_status)->toBe($status);
//     $tenant->delete();
// })->with([
//     ['000000000', 'verified'],
//     ['111111111', 'verified'],
//     ['111111112', 'unavailable'],
// ]);
