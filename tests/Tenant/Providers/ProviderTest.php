<?php

use App\Models\Tenants\Room;

use App\Models\Tenants\Site;
use App\Models\Tenants\User;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Floor;
use App\Models\Tenants\Company;
use App\Models\Tenants\Country;
use App\Models\Tenants\Building;
use App\Models\Tenants\Provider;
use Illuminate\Http\UploadedFile;
use App\Models\Central\CategoryType;
use Illuminate\Support\Facades\Storage;
use function Pest\Laravel\assertDatabaseHas;
use function PHPUnit\Framework\assertEquals;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;

beforeEach(function () {
    $this->user = User::factory()->withRole('Admin')->create();
    $this->actingAs($this->user, 'tenant');
    $this->categoryType = CategoryType::factory()->create(['category' => 'provider']);

    CategoryType::factory()->count(2)->create(['category' => 'document']);

    $this->site = Site::factory()->create();
    $this->building = Building::factory()->create();
    $this->floor = Floor::factory()->create();
    $this->room = Room::factory()->create();

    $this->assetSite = Asset::factory()->forLocation($this->site)->create();
    $this->assetBuilding = Asset::factory()->forLocation($this->building)->create();
    $this->assetFloor = Asset::factory()->forLocation($this->floor)->create();
    $this->assetRoom = Asset::factory()->forLocation($this->room)->create();
});

it('can factory a provider', function () {
    Provider::factory()->create();
    assertDatabaseCount('providers', 1);
});

it('can render the index providers page', function () {
    Provider::factory()->count(3)->create();
    $response = $this->getFromTenant('tenant.providers.index');
    $response->assertOk()
        ->assertInertia(
            fn($page) =>
            $page->component('tenants/providers/IndexProviders')
                ->has('items.data', 3)
        );
});

it('can render the show provider page', function () {
    $provider = Provider::factory()->create();
    $response = $this->getFromTenant('tenant.providers.show', $provider);
    $response->assertOk()
        ->assertInertia(
            fn($page) =>
            $page->component('tenants/providers/ShowProvider')
                ->has('item')
                ->where('item.id', $provider->id)
        );
});

it('can render the create provider page', function () {
    $response = $this->getFromTenant('tenant.providers.create');
    $response->assertOk()
        ->assertInertia(
            fn($page) =>
            $page->component('tenants/providers/CreateUpdateProvider')
        );
});

it('can render the edit provider page', function () {
    $provider = Provider::factory()->create();
    $response = $this->getFromTenant('tenant.providers.edit', $provider);
    $response->assertOk()
        ->assertInertia(
            fn($page) =>
            $page->component('tenants/providers/CreateUpdateProvider')
                ->has('provider')
                ->where('provider.id', $provider->id)
        );
});

it('can post a new provider', function () {

    $formData = [
        'name' => 'Facility Web Experience SPRL',
        'email' => 'info@facilitywebxp.be',
        'vat_number' => 'BE0123456789',
        'street' => 'Rue sur le Hour',
        'house_number' => '16A',
        'postal_code' => '4910',
        'city' => 'La Reid',
        'country_code' => 'BEL',
        'phone_number' => '+32450987654',
        'categoryId' => $this->categoryType->id,
        'website' => 'www.website.com',
    ];

    $response = $this->postToTenant('api.providers.store', $formData);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
        ]);

    $country = Country::where('iso_code', 'BEL')->first();

    assertDatabaseCount('providers', 1);
    assertDatabaseHas('providers', [
        'name' => 'Facility Web Experience SPRL',
        'email' => 'info@facilitywebxp.be',
        'vat_number' => 'BE0123456789',
        'street' => 'Rue sur le Hour',
        'house_number' => '16A',
        'postal_code' => '4910',
        'city' => 'La Reid',
        'country_id' => $country->id,
        'phone_number' => '+32450987654',
        'website' => 'https://www.website.com',
        'category_type_id' => $this->categoryType->id,
    ]);
});

it('can post a new provider with contact persons', function () {
    $formData = [
        'name' => 'Facility Web Experience SPRL',
        'email' => 'info@facilitywebxp.be',
        'vat_number' => 'BE0123456789',
        'street' => 'Rue sur le Hour',
        'house_number' => '16A',
        'postal_code' => '4910',
        'city' => 'La Reid',
        'country_code' => 'BEL',
        'phone_number' => '+32450987654',
        'categoryId' => $this->categoryType->id,
        'website' => 'www.website.com',
        'users' => [
            [
                'first_name' => 'Michel',
                'last_name' => 'Dupont',
                'email' => 'micheldupont@email.com',
                'phone_number' => '+32123456789',
                'job_position' => 'Account Manager'
            ],
            [
                'first_name' => 'Micheline',
                'last_name' => 'Dupont',
                'email' => 'michelinedupont@email.com',
                'phone_number' => '+32123456789',
                'job_position' => 'Account Manager'
            ]
        ]
    ];

    $response = $this->postToTenant('api.providers.store', $formData);
    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
        ]);

    $provider = Provider::first();

    assertDatabaseHas('users', [
        'first_name' => 'Michel',
        'last_name' => 'Dupont',
        'email' => 'micheldupont@email.com',
        'phone_number' => '+32123456789',
        'job_position' => 'Account Manager',
        'provider_id' => $provider->id
    ]);
    assertDatabaseHas('users', [
        'first_name' => 'Micheline',
        'last_name' => 'Dupont',
        'email' => 'michelinedupont@email.com',
        'phone_number' => '+32123456789',
        'job_position' => 'Account Manager',
        'provider_id' => $provider->id
    ]);
});

it('can post a new provider with logo', function () {

    $file1 = UploadedFile::fake()->image('logo.png')->size(1500);

    $formData = [
        'name' => 'Facility Web Experience SPRL',
        'email' => 'info@facilitywebxp.be',
        'vat_number' => 'BE0123456789',
        'street' => 'Rue sur le Hour',
        'house_number' => '16A',
        'postal_code' => '4910',
        'city' => 'La Reid',
        'country_code' => 'BEL',
        'phone_number' => '+32450987654',
        'categoryId' => $this->categoryType->id,
        'website' => 'www.website.com',
        'pictures' => [$file1]
    ];

    $response = $this->postToTenant('api.providers.store', $formData);
    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
        ]);

    assertDatabaseCount('providers', 1);
    assertDatabaseHas('providers', [
        'name' => 'Facility Web Experience SPRL',
        'email' => 'info@facilitywebxp.be',
        'vat_number' => 'BE0123456789',
        'category_type_id' => $this->categoryType->id,
        'logo' => Provider::first()->logo
    ]);

    $company = Company::first();
    assertEquals(round($company->disk_size / 1024), 1500);

    Storage::disk('tenants')->assertExists(Provider::first()->logo);
});

it('can update an existing provider', function () {

    $provider = Provider::factory()->create();
    $newcategoryType = CategoryType::factory()->create(['category' => 'provider']);

    $formData = [
        'name' => 'Facility Web Experience SPRL',
        'email' => 'info@facilitywebxp.be',
        'vat_number' => 'BE0123456789',
        'street' => 'Rue sur le Hour',
        'house_number' => '16A',
        'postal_code' => '4910',
        'city' => 'La Reid',
        'country_code' => 'DEU',
        'phone_number' => '+32450987654',
        'categoryId' => $newcategoryType->id,
    ];

    $response = $this->patchToTenant('api.providers.update', $formData, $provider);
    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
        ]);

    $country = Country::where('iso_code', 'DEU')->first();

    assertDatabaseCount('providers', 1);
    assertDatabaseHas('providers', [
        'id' => 1,
        'name' => 'Facility Web Experience SPRL',
        'email' => 'info@facilitywebxp.be',
        'vat_number' => 'BE0123456789',
        'street' => 'Rue sur le Hour',
        'house_number' => '16A',
        'postal_code' => '4910',
        'city' => 'La Reid',
        'country_id' => $country->id,
        'phone_number' => '+32450987654',
        'category_type_id' => $newcategoryType->id,
    ]);
});

it('can delete an existing provider', function () {
    $provider = Provider::factory()->create();

    $response = $this->deleteFromTenant('api.providers.destroy', $provider);
    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
        ]);

    assertDatabaseEmpty('providers');
});

it('can retrieve all assets linked to a provider', function () {

    $provider = Provider::factory()->create();

    $this->assetRoom->refresh();
    $this->assetSite->refresh();

    $provider->maintainables()->sync([$this->assetRoom->maintainable, $this->assetSite->maintainable]);

    $response = $this->getFromTenant('api.providers.assets', $provider);
    $response->assertJsonCount(2, 'data.data');
});

it('can retrieve all locations linked to a provider', function () {

    $provider = Provider::factory()->create();

    $this->site->refresh();
    $this->floor->refresh();

    $provider->maintainables()->sync([$this->site->maintainable, $this->floor->maintainable]);

    $response = $this->getFromTenant('api.providers.locations', $provider);
    $response->assertJsonCount(2, 'data.data');
});
