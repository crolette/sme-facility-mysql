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
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use App\Models\Tenants\CategoryProvider;
use function PHPUnit\Framework\assertCount;
use function Pest\Laravel\assertDatabaseHas;
use function PHPUnit\Framework\assertEquals;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;

beforeEach(function () {
    $this->user = User::factory()->withRole('Admin')->create();
    $this->actingAs($this->user, 'tenant');
    $this->providerCategoryOne = CategoryType::factory()->create(['category' => 'provider']);
    $this->providerCategoryTwo = CategoryType::factory()->create(['category' => 'provider']);

    CategoryType::factory()->count(2)->create(['category' => 'document']);

    $this->site = Site::factory()->withMaintainableData()->create();
    $this->building = Building::factory()->create();
    $this->floor = Floor::factory()->withMaintainableData()->create();
    $this->room = Room::factory()->withMaintainableData()->create();

    $this->assetSite = Asset::factory()->withMaintainableData()->forLocation($this->site)->create();
    $this->assetBuilding = Asset::factory()->withMaintainableData()->forLocation($this->building)->create();
    $this->assetFloor = Asset::factory()->withMaintainableData()->forLocation($this->floor)->create();
    $this->assetRoom = Asset::factory()->withMaintainableData()->forLocation($this->room)->create();
});

it('can factory a provider', function () {
    Provider::factory()->create();
    assertDatabaseCount('providers', 1);
});

it('can post a new provider', function () {
    // dump($this->providerCategoryOne->attributes);
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
        'categories' => [$this->providerCategoryOne, $this->providerCategoryTwo],
        'website' => 'www.website.com',
    ];

    $response = $this->postToTenant('api.providers.store', $formData);
    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
        ]);

    $country = Country::where('iso_code', 'BEL')->first();
    $provider = Provider::first();
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
    ]);

    assertDatabaseCount(CategoryProvider::class, 2);
    assertDatabaseHas(CategoryProvider::class, [
        'provider_id' => $provider->id,
        'category_type_id' => $this->providerCategoryOne->id
    ], 'tenant');

    assertDatabaseHas(CategoryProvider::class, [
        'provider_id' => $provider->id,
        'category_type_id' => $this->providerCategoryTwo->id
    ], 'tenant');

    assertCount(2, $provider->categories);
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
        'categories' => [$this->providerCategoryOne],
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
        'categories' => [$this->providerCategoryOne],
        'website' => 'www.website.com',
        'pictures' => [$file1]
    ];

    $response = $this->postToTenant('api.providers.store', $formData);
    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
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
        'categories' => [$this->providerCategoryOne],
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
    ]);

    assertDatabaseHas(CategoryProvider::class, [
        'provider_id' => $provider->id,
        'category_type_id' => $this->providerCategoryOne->id
    ], 'tenant');
});

it('can delete an existing provider', function () {
    $provider = Provider::factory()->create();

    $response = $this->deleteFromTenant('api.providers.destroy', $provider);
    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
        ]);

    assertDatabaseEmpty('providers');
    assertDatabaseEmpty('category_type_provider');
});

it('can delete an existing provider and deletes the provider directory', function () {
    Queue::fake();
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
        'categories' => [$this->providerCategoryOne],
        'website' => 'www.website.com',
        'pictures' => [$file1]
    ];

    $response = $this->postToTenant('api.providers.store', $formData);
    $provider = Provider::first();
    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
        ]);

    assertDatabaseCount('providers', 1);
    assertDatabaseHas('providers', [
        'name' => 'Facility Web Experience SPRL',
        'email' => 'info@facilitywebxp.be',
        'vat_number' => 'BE0123456789',
        'logo' => Provider::first()->logo
    ]);

    $company = Company::first();
    assertEquals(round($company->disk_size / 1024), 1500);

    Storage::disk('tenants')->assertExists($provider->logo);

    $response = $this->deleteFromTenant('api.providers.destroy', $provider);
    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
        ]);

    assertDatabaseEmpty('providers');

    $company->refresh();

    Storage::disk('tenants')->assertMissing($provider->directory);
    Storage::disk('tenants')->assertMissing($provider->logo);
});
