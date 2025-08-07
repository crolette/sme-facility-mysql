<?php

use App\Models\Tenants\User;

use App\Models\Tenants\Provider;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseHas;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user, 'tenant');
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
            $page->component('tenants/providers/index')
                ->has('providers', 3)
        );
});

it('can render the show provider page', function () {
    $provider = Provider::factory()->create();
    $response = $this->getFromTenant('tenant.providers.show', $provider);
    $response->assertOk()
        ->assertInertia(
            fn($page) =>
            $page->component('tenants/providers/show')
                ->has('item')
                ->where('item.id', $provider->id)
        );
});

it('can render the create provider page', function () {
    $response = $this->getFromTenant('tenant.providers.create');
    $response->assertOk()
        ->assertInertia(
            fn($page) =>
            $page->component('tenants/providers/create')
        );
});

it('can render the edit provider page', function () {
    $provider = Provider::factory()->create();
    $response = $this->getFromTenant('tenant.providers.edit', $provider);
    $response->assertOk()
        ->assertInertia(
            fn($page) =>
            $page->component('tenants/providers/create')
                ->has('provider')
                ->where('provider.id', $provider->id)
        );
});

it('can post a new provider', function () {

    $file1 = UploadedFile::fake()->image('logo.png');

    $formData = [
        'name' => 'Facility Web Experience SPRL',
        'email' => 'info@facilitywebxp.be',
        'vat_number' => 'BE0123456789',
        'address' => 'Rue sur le Hour 16A, 4910 La Reid, Belgique',
        'phone_number' => '+32450987654',
        'logo' => $file1
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
        'logo' => Provider::first()->logo
    ]);

    Storage::disk('tenants')->assertExists(Provider::first()->logo);
});

it('can update an existing provider', function () {

    $provider = Provider::factory()->create();

    $file1 = UploadedFile::fake()->image('logo.png');

    $formData = [
        'name' => 'Facility Web Experience SPRL',
        'email' => 'info@facilitywebxp.be',
        'vat_number' => 'BE0123456789',
        'address' => 'Rue sur le Hour 16A, 4910 La Reid, Belgique',
        'phone_number' => '+32450987654',
        'logo' => $file1
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
        'logo' => Provider::first()->logo
    ]);

    Storage::disk('tenants')->assertExists(Provider::first()->logo);
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
