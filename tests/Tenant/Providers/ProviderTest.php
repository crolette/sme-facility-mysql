<?php

use App\Models\Tenants\User;

use App\Models\Tenants\Company;
use App\Models\Tenants\Provider;
use Illuminate\Http\UploadedFile;
use App\Models\Central\CategoryType;
use Illuminate\Support\Facades\Storage;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function PHPUnit\Framework\assertEquals;

beforeEach(function () {
    $this->user = User::factory()->withRole('Admin')->create();
    $this->actingAs($this->user, 'tenant');
    $this->categoryType = CategoryType::factory()->create(['category' => 'provider']);
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

it('can post a new provider with logo', function () {

    $file1 = UploadedFile::fake()->image('logo.png')->size(1500);


    $formData = [
        'name' => 'Facility Web Experience SPRL',
        'email' => 'info@facilitywebxp.be',
        'vat_number' => 'BE0123456789',
        'address' => 'Rue sur le Hour 16A, 4910 La Reid, Belgique',
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
        'address' => 'Rue sur le Hour 16A, 4910 La Reid, Belgique',
        'phone_number' => '+32450987654',
        'website' => 'https://www.website.com',
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
        'address' => 'Rue sur le Hour 16A, 4910 La Reid, Belgique',
        'phone_number' => '+32450987654',
        'categoryId' => $newcategoryType->id,
    ];

    $response = $this->patchToTenant('api.providers.update', $formData, $provider);
    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
        ]);

    assertDatabaseCount('providers', 1);
    assertDatabaseHas('providers', [
        'id' => 1,
        'name' => 'Facility Web Experience SPRL',
        'email' => 'info@facilitywebxp.be',
        'vat_number' => 'BE0123456789',
        'address' => 'Rue sur le Hour 16A, 4910 La Reid, Belgique',
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

// it('can post a new logo for a provider', function() {

    
// });