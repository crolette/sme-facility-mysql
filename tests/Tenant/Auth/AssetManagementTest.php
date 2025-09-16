<?php

use App\Models\LocationType;

use App\Models\Tenants\Room;
use App\Models\Tenants\Site;
use App\Models\Tenants\User;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Floor;
use App\Models\Tenants\Building;
use App\Models\Tenants\Provider;
use Illuminate\Http\UploadedFile;
use App\Models\Central\CategoryType;
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
    CategoryType::factory()->count(2)->create(['category' => 'document']);
    $this->categoryTypeAsset = CategoryType::factory()->create(['category' => 'asset']);
    Site::factory()->create();
    Building::factory()->create();
    Floor::factory()->create();

    $this->room = Room::factory()
        ->for(LocationType::where('level', 'room')->first())
        ->for(Floor::first())
        ->create();

    $this->asset = Asset::factory()->forLocation($this->room)->create();

    $this->formData = [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'locationId' => $this->room->id,
        'locationType' => 'room',
        'locationReference' => $this->room->reference_code,
        'surface' => 12,
        'categoryId' => $this->categoryTypeAsset->id,
        'need_maintenance' => false
    ];
});

test('test access roles to assets index page', function (string $role, int $expectedStatus) {
    $user = User::factory()->create();
    $user->assignRole($role);
    $this->actingAs($user, 'tenant');

    $response = $this->getFromTenant('tenant.assets.index');
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 403],
    ['Provider', 403]
]);

test('test access roles to create asset page', function (string $role, int $expectedStatus) {
    $user = User::factory()->create();
    $user->assignRole($role);
    $this->actingAs($user, 'tenant');

    $response = $this->getFromTenant('tenant.assets.create');
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 403],
    ['Provider', 403]
]);

test('test access roles to view any asset page', function (string $role, int $expectedStatus) {
    $user = User::factory()->create();
    $user->assignRole($role);
    $this->actingAs($user, 'tenant');

    $response = $this->getFromTenant('tenant.assets.show', $this->asset->reference_code);
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 403],
    ['Provider', 403]
]);


test('test access roles to view asset with maintenance manager page', function (string $role, int $expectedStatus) {
    $user = User::factory()->create();
    $user->assignRole($role);
    $this->actingAs($user, 'tenant');

    $asset = Asset::factory()->forLocation($this->room)->create();

    $asset->maintainable()->update(['maintenance_manager_id' => $user->id]);

    $response = $this->getFromTenant('tenant.assets.show', $asset->reference_code);
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 200],
    ['Provider', 403]
]);

test('test access roles to store an asset', function (string $role, int $expectedStatus) {

    $user = User::factory()->create();
    $user->assignRole($role);
    $this->actingAs($user, 'tenant');

    $response = $this->postToTenant('api.assets.store', $this->formData);
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 403],
    ['Provider', 403]
]);

test('test access roles to update any asset page', function (string $role, int $expectedStatus) {
    $user = User::factory()->create();
    $user->assignRole($role);
    $this->actingAs($user, 'tenant');

    $response = $this->getFromTenant('tenant.assets.edit', $this->asset->reference_code);
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 403],
    ['Provider', 403]
]);

test('test access roles to update asset with maintenance manager page', function (string $role, int $expectedStatus) {

    $user = User::factory()->create();
    $user->assignRole($role);
    $this->actingAs($user, 'tenant');
    $asset = Asset::factory()->forLocation($this->room)->create();

    $asset->maintainable()->update(['maintenance_manager_id' => $user->id]);

    $response = $this->getFromTenant('tenant.assets.edit', $asset->reference_code);
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 200],
    ['Provider', 403]
]);

test('test access roles to asset page', function (string $role, int $expectedStatus) {
    $user = User::factory()->create();
    $user->assignRole($role);
    $this->actingAs($user, 'tenant');

    $response = $this->getFromTenant('tenant.assets.show', $this->asset->reference_code);
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 403],
    ['Provider', 403]
]);


test('test access roles to delete any asset', function (string $role, int $expectedStatus) {

    $user = User::factory()->create();
    $user->assignRole($role);
    $this->actingAs($user, 'tenant');


    $response = $this->deleteFromTenant('api.assets.destroy', $this->asset->reference_code);
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 403],
    ['Provider', 403]
]);

test('test access roles to delete asset with maintenance manager', function (string $role, int $expectedStatus) {

    $user = User::factory()->create();
    $user->assignRole($role);
    $this->actingAs($user, 'tenant');

    $asset = Asset::factory()->forLocation($this->room)->create();

    $asset->maintainable()->update(['maintenance_manager_id' => $user->id]);

    $response = $this->deleteFromTenant('api.assets.destroy', $asset->reference_code);
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 200],
    ['Provider', 403]
]);

test('test access roles to restore any asset', function (string $role, int $expectedStatus) {

    $user = User::factory()->withRole($role)->create();
    $this->actingAs($user, 'tenant');

    $response = $this->postToTenant('api.assets.restore', [], $this->asset->reference_code);
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 403],
    ['Provider', 403]
]);

test('test access roles to force delete any asset', function (string $role, int $expectedStatus) {

    $user = User::factory()->withRole($role)->create();
    $user->assignRole($role);
    $this->actingAs($user, 'tenant');

    $response = $this->deleteFromTenant('api.assets.force', $this->asset->reference_code);
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 403],
    ['Provider', 403]
]);
