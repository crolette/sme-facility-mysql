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
    $this->site = Site::factory()->withMaintainableData()->create();

    $this->formData = [
        'name' => 'New site',
        'surface_floor' => 2569.12,
        'surface_walls' => 256.9,
        'description' => 'Description new site',
        'locationType' => $this->siteType->id,
    ];
});

test('test access roles to sites index page', function (string $role, int $expectedStatus) {
    $user = User::factory()->create();
    $user->assignRole($role);
    $this->actingAs($user, 'tenant');

    $response = $this->getFromTenant('tenant.sites.index');
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 403],
    ['Provider', 403]
]);

test('test access roles to create site page', function (string $role, int $expectedStatus) {
    $user = User::factory()->create();
    $user->assignRole($role);
    $this->actingAs($user, 'tenant');

    $response = $this->getFromTenant('tenant.sites.create');
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 403],
    ['Provider', 403]
]);

test('test access roles to view any site page', function (string $role, int $expectedStatus) {
    $user = User::factory()->create();
    $user->assignRole($role);
    $this->actingAs($user, 'tenant');

    $response = $this->getFromTenant('tenant.sites.show', $this->site->reference_code);
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 403],
    ['Provider', 403]
]);


test('test access roles to view site with maintenance manager page', function (string $role, int $expectedStatus) {
    $user = User::factory()->create();
    $user->assignRole($role);
    $this->actingAs($user, 'tenant');

    $this->site->maintainable()->update(['maintenance_manager_id' => $user->id]);

    $response = $this->getFromTenant('tenant.sites.show', $this->site->reference_code);
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 200],
    ['Provider', 403]
]);

test('test access roles to store a site', function (string $role, int $expectedStatus) {

    $user = User::factory()->create();
    $user->assignRole($role);
    $this->actingAs($user, 'tenant');

    $response = $this->postToTenant('api.sites.store', $this->formData);
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

    $response = $this->getFromTenant('tenant.sites.edit', $this->site->reference_code);
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 403],
    ['Provider', 403]
]);

test('test access roles to update site with maintenance manager page', function (string $role, int $expectedStatus) {

    $user = User::factory()->create();
    $user->assignRole($role);
    $this->actingAs($user, 'tenant');

    $this->site->maintainable()->update(['maintenance_manager_id' => $user->id]);

    $response = $this->getFromTenant('tenant.sites.edit', $this->site->reference_code);
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 200],
    ['Provider', 403]
]);

test('test access roles to update site with maintenance manager', function (string $role, int $expectedStatus) {

    $user = User::factory()->create();
    $user->assignRole($role);
    $this->actingAs($user, 'tenant');

    $this->site->maintainable()->update(['maintenance_manager_id' => $user->id]);

    $response = $this->patchToTenant('api.sites.update', $this->formData, $this->site->reference_code);
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 200],
    ['Provider', 403]
]);

test('test access roles to delete any asset', function (string $role, int $expectedStatus) {

    $user = User::factory()->create();
    $user->assignRole($role);
    $this->actingAs($user, 'tenant');


    $response = $this->deleteFromTenant('api.sites.destroy', $this->site->reference_code);
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 403],
    ['Provider', 403]
]);

test('test access roles to delete site with maintenance manager', function (string $role, int $expectedStatus) {

    $user = User::factory()->create();
    $user->assignRole($role);
    $this->actingAs($user, 'tenant');

    $this->site->maintainable()->update(['maintenance_manager_id' => $user->id]);

    $response = $this->deleteFromTenant('api.sites.destroy', $this->site->reference_code);
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 403],
    ['Provider', 403]
]);

test('cannot regenerate a QR Code if user is not maintenance manager from the site', function (string $role, int $expectedStatus) {

    $user = User::factory()->withRole($role)->create();
    $user->assignRole($role);
    $this->actingAs($user, 'tenant');

    $response = $this->postToTenant('api.sites.qr.regen', [], $this->site->reference_code);
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 403],
    ['Provider', 403]
]);

test('can regenerate a QR Code if user is not maintenance manager from the site', function (string $role, int $expectedStatus) {

    $user = User::factory()->withRole($role)->create();
    $user->assignRole($role);
    $this->actingAs($user, 'tenant');
    $this->site->maintainable()->update(['maintenance_manager_id' => $user->id]);

    $response = $this->postToTenant('api.sites.qr.regen', [], $this->site->reference_code);
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 200],
    ['Provider', 403]
]);
