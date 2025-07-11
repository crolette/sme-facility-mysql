<?php

use App\Models\Tenant;
use App\Models\Address;
use App\Enums\AddressTypes;
use App\Models\Tenants\User;
use Illuminate\Support\Facades\DB;
use App\Models\Central\CentralUser;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use function PHPUnit\Framework\assertTrue;

use Tests\Concerns\ManagesTenantDatabases;
use function PHPUnit\Framework\assertFalse;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(ManagesTenantDatabases::class);

beforeEach(function () {
    $this->withoutMiddleware([
        \Stancl\Tenancy\Middleware\InitializeTenancyByDomain::class,
        \Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain::class,
        \Stancl\Tenancy\Middleware\InitializeTenancyByPath::class,
        \Stancl\Tenancy\Middleware\InitializeTenancyByRequestData::class,
    ]);
});

// afterEach(function () {
//     User::query()->delete();
//     Tenant::query()->delete();
// });

// Check tenancy creation
// Check tenancy domain creation
// Check tenancy database creation
// Check tenancy databse deletion after tenant deletion
// Check unique tenancy
// Check tenancy edit page
// Check tenancy update


it('renders the tenant show page', function () {
    $this->actingAs($user = CentralUser::factory()->create());

    $userTenant = User::factory()->raw();
    Session::put([...$userTenant]);

    $tenant = Tenant::factory()->create();
    $tenant->domain()->create(['domain' => $tenant->id]);

    $tenant->addresses()->create(Address::factory()->raw());
    $tenant->addresses()->create(Address::factory()->raw(['address_type' => AddressTypes::INVOICE->value]));
    $tenant->save();

    $response = $this->get(route('central.tenants.show', $tenant));
    $response->assertOk();

    try {
        $response->assertInertia(
            fn($page) =>
            $page->component('central/tenants/show')
                ->has('tenant')
                ->where('tenant.id', $tenant->id)
                ->where('tenant.domain.id', $tenant->domain->id)
                ->where('tenant.company_address.id', $tenant->companyAddress->id)
                ->where('tenant.domain.domain', $tenant->domain->domain)
        );
    } finally {
        $this->cleanupTenant($tenant);
        $user->delete();
    }
});


it('returns 404 if tenant does not exist', function () {
    $this->actingAs($user = CentralUser::factory()->create());
    $response = $this->get(route('central.tenants.show', ['tenant' => 9999]));

    $response->assertNotFound();
    $user->delete();
});



it('can render tenancy create page', function () {

    $this->actingAs($user = CentralUser::factory()->create());

    $this->get(route('central.tenants.create'))->assertOk();

    $user->delete();
});

it('can create tenant & attach the domain & verifies that database exists', function () {

    Log::info('TEST CREATE TENANT');
    $this->actingAs($user = CentralUser::factory()->create());
    Log::info($user);
    $companyAddress = Address::factory()->make();
    $invoiceAddress = Address::factory()->make(['address_type' => AddressTypes::INVOICE->value]);

    $pwd = fake()->password(10);

    $formData = [
        'company_name' => 'Buzon',
        'first_name' => 'Michel',
        'last_name' => 'Dupont',
        'password' => $pwd,
        'password_confirmation' => $pwd,
        'email' => 'buzon@buzon.com',
        'vat_number' => 'BE0987654321',
        'domain_name' => 'buzon',
        'company_code' => 'bzon',
        'phone_number' => '+3242212215',
        'company' => [
            'street' => $companyAddress->street,
            'house_number' => $companyAddress->house_number,
            'zip_code' => $companyAddress->zip_code,
            'city' => $companyAddress->city,
            'country' => $companyAddress->country,
        ],
        'same_address_as_company' => false,
        'invoice' => [
            'street' => $invoiceAddress->street,
            'house_number' => $invoiceAddress->house_number,
            'zip_code' => $invoiceAddress->zip_code,
            'city' => $invoiceAddress->city,
            'country' => $invoiceAddress->country,
        ],
    ];

    $tenant = null;

    try {

        $response = $this->post(route('central.tenants.store'), $formData);
        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();

        $tenant = Tenant::find('bzon');
        expect($tenant)->toBeInstanceOf(Tenant::class);

        assertDatabaseHas('tenants', [
            'id' => 'bzon',
        ]);

        assertDatabaseHas('addresses', [
            'street' => $companyAddress->street,
            'tenant_id' => 'bzon',
            'address_type' => 'company'
        ]);

        assertDatabaseHas('addresses', [
            'street' => $invoiceAddress->street,
            'tenant_id' => $tenant->id,
            'address_type' => 'invoice'
        ]);

        assertDatabaseHas('domains', [
            'tenant_id' => $tenant->id,
            'domain' => 'buzon',
        ]);

        tenancy()->initialize($tenant);

        assertDatabaseHas('users', [
            'email' => 'buzon@buzon.com',
        ]);

        tenancy()->end();

        $tenantDbName = config('tenancy.database.prefix') . $tenant->id . config('tenancy.database.suffix');


        $exists = DB::table('information_schema.schemata')
            ->where('schema_name', $tenantDbName)
            ->exists();

        assertTrue($exists);
    } finally {
        if ($tenant) {
            $this->cleanupTenant($tenant);
        }
        $user->delete();
    }
});




it('renders the tenant update page', function () {
    $this->actingAs($user = CentralUser::factory()->create());
    $userTenant = User::factory()->raw();
    Session::put([...$userTenant]);

    $tenant = Tenant::factory()->create();
    $tenant->addresses()->create(Address::factory()->raw());
    $tenant->domain()->create(['domain' => 'blabla']);

    try {
        $response = $this->get(route('central.tenants.edit', ['tenant' => $tenant]));

        $response->assertInertia(
            fn($page) =>
            $page->component('central/tenants/create')
                ->has('tenant')
                ->where('tenant.id', $tenant->id)
                ->where('tenant.domain.domain', $tenant->domain->domain)
        );
    } finally {
        $this->cleanupTenant($tenant);
        $user->delete();
    }
});


it('verifies that initialized tenant is on his database', function () {

    $this->actingAs($user = CentralUser::factory()->create());
    $companyAddress = Address::factory()->make();
    $invoiceAddress = Address::factory()->make(['address_type' => AddressTypes::INVOICE->value]);

    $pwd = fake()->password(10);

    $formData = [
        'company_name' => 'Buzon',
        'first_name' => 'Michel',
        'last_name' => 'Dupont',
        'password' => $pwd,
        'password_confirmation' => $pwd,
        'email' => 'buzon@buzon.com',
        'vat_number' => 'BE0987654321',
        'domain_name' => 'buzon',
        'company_code' => 'bzon',
        'phone_number' => '+3242212215',
        'company' => [
            'street' => $companyAddress->street,
            'house_number' => $companyAddress->house_number,
            'zip_code' => $companyAddress->zip_code,
            'city' => $companyAddress->city,
            'country' => $companyAddress->country,
        ],
        'same_address_as_company' => false,
        'invoice' => [
            'street' => $invoiceAddress->street,
            'house_number' => $invoiceAddress->house_number,
            'zip_code' => $invoiceAddress->zip_code,
            'city' => $invoiceAddress->city,
            'country' => $invoiceAddress->country,
        ],
    ];
    $response = $this->post(route('central.tenants.store'), $formData);

    $tenant = Tenant::find('bzon');

    tenancy()->initialize($tenant);
    try {

        expect(tenancy()->initialized)->toBeTrue();
        expect(tenancy()->tenant->id)->toBe($tenant->id);

        // Verify we're using the correct database
        $currentDatabase = DB::getDatabaseName();
        $tenantDb = $tenant->tenancy_db_name;

        $tenantDbName = config('tenancy.database.prefix') . $tenant->id . config('tenancy.database.suffix');

        // check that the name of the tenant's database is conform to the definition in config/tenancy
        expect($tenantDb)->toBe($tenantDbName);

        expect($currentDatabase)->toBe($tenantDbName);
    } finally {
        tenancy()->end();

        $this->cleanupTenant($tenant);
        $user->delete();
    }
});


it('deletes the tenant and the domain and the database', function () {
    $this->actingAs($user = CentralUser::factory()->create());

    $userTenant = User::factory()->raw();
    Session::put([...$userTenant]);

    $tenant = Tenant::factory()->create();


    try {

        assertTrue(DB::table('information_schema.schemata')
            ->where('schema_name', $tenant->tenancy_db_name)
            ->exists());

        $response = $this->delete(
            route('central.tenants.delete', $tenant)
        );


        $response->assertRedirect(route('central.tenants.index'));



        $deleted = DB::table('information_schema.schemata')
            ->where('schema_name', $tenant->tenancy_db_name)
            ->exists();
        assertFalse($deleted);

        assertDatabaseMissing('tenants', ['id' => $tenant->id]);
        assertDatabaseMissing('domains', ['tenant_id' => $tenant->id]);
    } finally {
        $this->cleanupTenant($tenant);
        $user->delete();
    }
});
