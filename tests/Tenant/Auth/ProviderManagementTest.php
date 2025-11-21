<?php

use Carbon\Carbon;

use App\Models\LocationType;
use App\Models\Tenants\Room;
use App\Models\Tenants\Site;
use App\Models\Tenants\User;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Floor;
use App\Enums\NoticePeriodEnum;
use App\Models\Tenants\Building;
use App\Models\Tenants\Contract;
use App\Models\Tenants\Document;
use App\Models\Tenants\Provider;
use App\Enums\ContractStatusEnum;
use Illuminate\Http\UploadedFile;
use App\Enums\ContractDurationEnum;
use App\Models\Central\CategoryType;
use App\Enums\ContractRenewalTypesEnum;
use Illuminate\Support\Facades\Storage;
use function PHPUnit\Framework\assertNull;
use function PHPUnit\Framework\assertTrue;
use function Pest\Laravel\assertDatabaseHas;
use function PHPUnit\Framework\assertNotNull;
use Illuminate\Testing\Fluent\AssertableJson;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseMissing;


beforeEach(function () {
    $this->user = User::factory()->withRole('Admin')->create();
    LocationType::factory()->create(['level' => 'site']);
    LocationType::factory()->create(['level' => 'building']);
    LocationType::factory()->create(['level' => 'floor']);
    LocationType::factory()->create(['level' => 'room']);
    CategoryType::factory()->create(['category' => 'document']);
    CategoryType::factory()->create(['category' => 'asset']);
    $this->categoryType = CategoryType::factory()->create(['category' => 'provider']);
    Site::factory()->create();
    Building::factory()->create();
    Floor::factory()->create();

    $this->room = Room::factory()
        ->for(LocationType::where('level', 'room')->first())
        ->for(Floor::first())
        ->create();

    $this->asset = Asset::factory()->forLocation($this->room)->create();
    $this->provider = Provider::factory()->create();
    $this->contract = Contract::factory()->create();

    $file1 = UploadedFile::fake()->image('logo.png');


    $this->formData = [
        'name' => 'Facility Web Experience SPRL',
        'email' => 'info@facilitywebxp.be',
        'vat_number' => 'BE0123456789',
        'street' => fake()->streetName(),
        'postal_code' => '' . fake()->randomNumber(4, true) . '',
        'city' => fake()->city(),
        'country_code' => 'BEL',
        'phone_number' => '+32450987654',
        'categoryId' => $this->categoryType->id,
        'website' => 'www.website.com',
        'pictures' => [$file1]
    ];
});

test('test access roles to providers index page', function (string $role, int $expectedStatus) {
    $user = User::factory()->withRole($role)->create();
    $this->actingAs($user, 'tenant');

    $response = $this->getFromTenant('tenant.providers.index');
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 200],
    ['Provider', 403]
]);

test('test access roles to create provider page', function (string $role, int $expectedStatus) {
    $user = User::factory()->withRole($role)->create();
    $this->actingAs($user, 'tenant');

    $response = $this->getFromTenant('tenant.providers.create');
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 200],
    ['Provider', 403]
]);

test('test access roles to view any provider page', function (string $role, int $expectedStatus) {
    $user = User::factory()->withRole($role)->create();
    $this->actingAs($user, 'tenant');

    $response = $this->getFromTenant('tenant.providers.show', $this->provider);
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 200],
    ['Provider', 403]
]);

test('test access roles to store a provider', function (string $role, int $expectedStatus) {
    $user = User::factory()->withRole($role)->create();
    $this->actingAs($user, 'tenant');

    $response = $this->postToTenant('api.providers.store', $this->formData);
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 200],
    ['Provider', 403]
]);

test('test access roles to update any provider page', function (string $role, int $expectedStatus) {
    $user = User::factory()->withRole($role)->create();
    $this->actingAs($user, 'tenant');

    $response = $this->getFromTenant('tenant.providers.edit', $this->provider);
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 200],
    ['Provider', 403]
]);

test('test access roles to update a provider', function (string $role, int $expectedStatus) {
    $user = User::factory()->withRole($role)->create();
    $this->actingAs($user, 'tenant');

    $response = $this->patchToTenant('api.providers.update', $this->formData, $this->provider);
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 200],
    ['Provider', 403]
]);


test('test access roles to provider page', function (string $role, int $expectedStatus) {
    $user = User::factory()->create();
    $user->assignRole($role);
    $this->actingAs($user, 'tenant');

    $response = $this->getFromTenant('tenant.providers.show', $this->provider);
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 200],
    ['Provider', 403]
]);


test('test access roles to delete any provider', function (string $role, int $expectedStatus) {

    $user = User::factory()->create();
    $user->assignRole($role);
    $this->actingAs($user, 'tenant');


    $response = $this->deleteFromTenant('api.providers.destroy', $this->provider);
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 403],
    ['Provider', 403]
]);

test('test access roles to post a new logo to a provider', function (string $role, int $expectedStatus) {
    $user = User::factory()->withRole($role)->create();
    $this->actingAs($user, 'tenant');

    $file1 = UploadedFile::fake()->image('avatar.png');

    $formData = ['pictures' => [$file1]];

    $response = $this->postToTenant('api.providers.logo.store', $formData, $this->provider);
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 200],
    ['Provider', 403]
]);

test('test access roles to delete a logo of a provider', function (string $role, int $expectedStatus) {
    $user = User::factory()->withRole($role)->create();
    $this->actingAs($user, 'tenant');

    $file1 = UploadedFile::fake()->image('avatar.png');

    $formData = ['pictures' => [$file1]];

    $this->postToTenant('api.providers.logo.store', $formData, $this->provider);

    $response = $this->deleteFromTenant('api.providers.logo.destroy', $this->provider);
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 200],
    ['Provider', 403]
]);
