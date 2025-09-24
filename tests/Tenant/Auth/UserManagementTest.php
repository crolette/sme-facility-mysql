<?php

use App\Models\Tenants\User;

use App\Models\Tenants\Provider;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use function Pest\Laravel\assertDatabaseHas;
use Illuminate\Testing\Fluent\AssertableJson;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseMissing;
use function PHPUnit\Framework\assertNotNull;
use function PHPUnit\Framework\assertNull;
use function PHPUnit\Framework\assertTrue;

beforeEach(function() {
    $this->user = User::factory()->withRole('Admin')->create();
    $this->actingAs($this->user, 'tenant');
});

test('test access roles to users index', function (string $role, int $expectedStatus) {
    $user = User::factory()->withRole($role)->create();
    $this->actingAs($user, 'tenant');

    $response = $this->getFromTenant('tenant.users.index');
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 403],
    ['Provider', 403]
]);

test('test access roles to view own user', function (string $role, int $expectedStatus) {
    $user = User::factory()->withRole($role)->create();
    $this->actingAs($user, 'tenant');
    $response = $this->getFromTenant('tenant.users.show', $user);
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 200],
    ['Provider', 200]
]);

test('test access roles to view another user', function (string $role, int $expectedStatus) {
    $user = User::factory()->withRole($role)->create();
    $this->actingAs($user, 'tenant');

      $newUser = User::factory()->create();

    $response = $this->getFromTenant('tenant.users.show', $newUser);
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 403],
    ['Provider', 403]
]);

test('test access roles to users create', function (string $role, int $expectedStatus) {
    $user = User::factory()->withRole($role)->create();
    $this->actingAs($user, 'tenant');
  
    $response = $this->getFromTenant('tenant.users.create');
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 403],
    ['Provider', 403]
]);


test('an admin can create a new user with a role', function (string $role) {
    $user = User::factory()->withRole('Admin')->create();
    $this->actingAs($user, 'tenant');

    $formData = [
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'janedoe@facilitywebxp.be',
        'can_login' => true,
        'role' => $role
    ];

    $response = $this->postToTenant('api.users.store', $formData);
    $response->assertStatus(200)
        ->assertJson(
            fn(AssertableJson $json) =>
            $json->where('status', 'success')
                ->etc()
        );

    $createdUser = User::where('email', 'janedoe@facilitywebxp.be')->first();

    assertTrue($createdUser->hasRole($role));

})->with([
    ['Admin'],
    ['Maintenance Manager'],
    ['Provider']
]);

test('an admin can update the role of a user', function (string $role) {

    $user = User::factory()->withRole('Admin')->create();
    $this->actingAs($user, 'tenant');

    $user = User::factory()->create();

    $formData = [
        'first_name' => $user->first_name,
        'last_name' => $user->last_name,
        'email' => $user->email,
        'can_login' => true,
        'role' => $role
    ];

    $response = $this->patchToTenant('api.users.update', $formData, $user->id);
    $response->assertStatus(200)
        ->assertJson(
            fn(AssertableJson $json) =>
            $json->where('status', 'success')
                ->etc()
        );

    $user->refresh();
    assertTrue($user->hasRole($role));
})->with([
    ['Admin'],
    ['Maintenance Manager'],
    ['Provider']
]);

test('another user as an admin cannot create a user', function (string $role) {
    $user = User::factory()->withRole($role)->create();
    $this->actingAs($user, 'tenant');
    
    $formData = [
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'janedoe@facilitywebxp.be',
        'can_login' => true,
        'role' => 'Admin'
    ];

    $response = $this->postToTenant('api.users.store', $formData);
    $response->assertStatus(403);
})->with([
    ['Maintenance Manager'],
    ['Provider']
]);
