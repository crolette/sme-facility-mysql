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
    $this->roomType = LocationType::factory()->create(['level' => 'room']);
    $this->site = Site::factory()->create();
    $this->building = Building::factory()->create();
    $this->floor = Floor::factory()->create();
    $this->room = Room::factory()
        ->for($this->roomType)
        ->for($this->floor)
        ->create();

    $this->formData = [
        'name' => 'New room',
        'surface_floor' => 2569.12,
        'surface_walls' => 256.9,
        'description' => 'Description new room',
        'levelType' => $this->floor->id,
        'locationType' => $this->roomType->id
    ];
});

test('test access roles to room index page', function (string $role, int $expectedStatus) {
    $user = User::factory()->create();
    $user->assignRole($role);
    $this->actingAs($user, 'tenant');

    $response = $this->getFromTenant('tenant.rooms.index');
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 403],
    ['Provider', 403]
]);

test('test access roles to create room page', function (string $role, int $expectedStatus) {
    $user = User::factory()->create();
    $user->assignRole($role);
    $this->actingAs($user, 'tenant');

    $response = $this->getFromTenant('tenant.rooms.create');
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 403],
    ['Provider', 403]
]);

test('test access roles to view any room page', function (string $role, int $expectedStatus) {
    $user = User::factory()->create();
    $user->assignRole($role);
    $this->actingAs($user, 'tenant');

    $response = $this->getFromTenant('tenant.rooms.show', $this->room->reference_code);
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 403],
    ['Provider', 403]
]);

test('test access roles to view room with maintenance manager page', function (string $role, int $expectedStatus) {
    $user = User::factory()->create();
    $user->assignRole($role);
    $this->actingAs($user, 'tenant');

    $this->room->maintainable()->update(['maintenance_manager_id' => $user->id]);

    $response = $this->getFromTenant('tenant.rooms.show', $this->room->reference_code);
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 200],
    ['Provider', 403]
]);

test('test access roles to store a room', function (string $role, int $expectedStatus) {

    $user = User::factory()->create();
    $user->assignRole($role);
    $this->actingAs($user, 'tenant');

    $response = $this->postToTenant('api.rooms.store', $this->formData);
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

    $response = $this->getFromTenant('tenant.rooms.edit', $this->room->reference_code);
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 403],
    ['Provider', 403]
]);

test('test access roles to update room with maintenance manager page', function (string $role, int $expectedStatus) {

    $user = User::factory()->create();
    $user->assignRole($role);
    $this->actingAs($user, 'tenant');

    $this->room->maintainable()->update(['maintenance_manager_id' => $user->id]);

    $response = $this->getFromTenant('tenant.rooms.edit', $this->room->reference_code);
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 200],
    ['Provider', 403]
]);

test('test access roles to update room with maintenance manager', function (string $role, int $expectedStatus) {

    $user = User::factory()->create();
    $user->assignRole($role);
    $this->actingAs($user, 'tenant');

    $this->room->maintainable()->update(['maintenance_manager_id' => $user->id]);

    $response = $this->patchToTenant('api.rooms.update', $this->formData, $this->room->reference_code);
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 200],
    ['Provider', 403]
]);

test('test access roles to delete any room', function (string $role, int $expectedStatus) {

    $user = User::factory()->create();
    $user->assignRole($role);
    $this->actingAs($user, 'tenant');


    $response = $this->deleteFromTenant('api.rooms.destroy', $this->room->reference_code);
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 403],
    ['Provider', 403]
]);

test('test access roles to delete room with maintenance manager', function (string $role, int $expectedStatus) {

    $user = User::factory()->create();
    $user->assignRole($role);
    $this->actingAs($user, 'tenant');

    $this->room->maintainable()->update(['maintenance_manager_id' => $user->id]);

    $response = $this->deleteFromTenant('api.rooms.destroy', $this->room->reference_code);
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 403],
    ['Provider', 403]
]);

test('cannot regenerate a QR Code if user is not maintenance manager from the room', function (string $role, int $expectedStatus) {

    $user = User::factory()->withRole($role)->create();
    $user->assignRole($role);
    $this->actingAs($user, 'tenant');

    $response = $this->postToTenant('api.rooms.qr.regen', [], $this->room->reference_code);
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 403],
    ['Provider', 403]
]);

test('can regenerate a QR Code if user is not maintenance manager from the room', function (string $role, int $expectedStatus) {

    $user = User::factory()->withRole($role)->create();
    $user->assignRole($role);
    $this->actingAs($user, 'tenant');
    $this->room->maintainable()->update(['maintenance_manager_id' => $user->id]);

    $response = $this->postToTenant('api.rooms.qr.regen', [], $this->room->reference_code);
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 200],
    ['Provider', 403]
]);