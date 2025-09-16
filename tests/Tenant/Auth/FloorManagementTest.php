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
    $this->siteType = LocationType::factory()->create(['level' => 'site']);
    $this->buildingType = LocationType::factory()->create(['level' => 'building']);
    $this->floorType = LocationType::factory()->create(['level' => 'floor']);
    $this->site = Site::factory()->create();
    $this->building = Building::factory()->create();
    $this->floor = Floor::factory()->create();

    $this->formData = [
        'name' => 'New floor',
        'surface_floor' => 2569.12,
        'surface_walls' => 256.9,
        'description' => 'Description new floor',
        'levelType' => $this->building->id,
        'locationType' => $this->floorType->id
    ];
});

test('test access roles to floor index page', function (string $role, int $expectedStatus) {
    $user = User::factory()->create();
    $user->assignRole($role);
    $this->actingAs($user, 'tenant');

    $response = $this->getFromTenant('tenant.floors.index');
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 403],
    ['Provider', 403]
]);

test('test access roles to create floor page', function (string $role, int $expectedStatus) {
    $user = User::factory()->create();
    $user->assignRole($role);
    $this->actingAs($user, 'tenant');

    $response = $this->getFromTenant('tenant.floors.create');
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 403],
    ['Provider', 403]
]);

test('test access roles to view any floor page', function (string $role, int $expectedStatus) {
    $user = User::factory()->create();
    $user->assignRole($role);
    $this->actingAs($user, 'tenant');

    $response = $this->getFromTenant('tenant.floors.show', $this->floor->reference_code);
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 403],
    ['Provider', 403]
]);

test('test access roles to view floor with maintenance manager page', function (string $role, int $expectedStatus) {
    $user = User::factory()->create();
    $user->assignRole($role);
    $this->actingAs($user, 'tenant');

    $this->floor->maintainable()->update(['maintenance_manager_id' => $user->id]);

    $response = $this->getFromTenant('tenant.floors.show', $this->floor->reference_code);
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 200],
    ['Provider', 403]
]);

test('test access roles to store a floor', function (string $role, int $expectedStatus) {

    $user = User::factory()->create();
    $user->assignRole($role);
    $this->actingAs($user, 'tenant');

    $response = $this->postToTenant('api.floors.store', $this->formData);
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

    $response = $this->getFromTenant('tenant.floors.edit', $this->floor->reference_code);
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 403],
    ['Provider', 403]
]);

test('test access roles to update floor with maintenance manager page', function (string $role, int $expectedStatus) {

    $user = User::factory()->create();
    $user->assignRole($role);
    $this->actingAs($user, 'tenant');

    $this->floor->maintainable()->update(['maintenance_manager_id' => $user->id]);

    $response = $this->getFromTenant('tenant.floors.edit', $this->floor->reference_code);
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 200],
    ['Provider', 403]
]);

test('test access roles to update floor with maintenance manager', function (string $role, int $expectedStatus) {

    $user = User::factory()->create();
    $user->assignRole($role);
    $this->actingAs($user, 'tenant');

    $this->floor->maintainable()->update(['maintenance_manager_id' => $user->id]);

    $response = $this->patchToTenant('api.floors.update', $this->formData, $this->floor->reference_code);
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 200],
    ['Provider', 403]
]);

test('test access roles to delete any floor', function (string $role, int $expectedStatus) {

    $user = User::factory()->create();
    $user->assignRole($role);
    $this->actingAs($user, 'tenant');


    $response = $this->deleteFromTenant('api.floors.destroy', $this->floor->reference_code);
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 403],
    ['Provider', 403]
]);

test('test access roles to delete floor with maintenance manager', function (string $role, int $expectedStatus) {

    $user = User::factory()->create();
    $user->assignRole($role);
    $this->actingAs($user, 'tenant');

    $this->floor->maintainable()->update(['maintenance_manager_id' => $user->id]);

    $response = $this->deleteFromTenant('api.floors.destroy', $this->floor->reference_code);
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 403],
    ['Provider', 403]
]);
