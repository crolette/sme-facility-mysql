<?php

use App\Models\Central\CategoryType;
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

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->user->assignRole('Admin');
    $this->actingAs($this->user, 'tenant');
});

it('can factory a user', function () {
    User::factory()->create();
    assertDatabaseCount('users', 2);
});

it('can render the index users page', function () {
    User::factory()->count(3)->create();
    $response = $this->getFromTenant('tenant.users.index');
    $response->assertOk()
        ->assertInertia(
            fn($page) =>
            $page->component('tenants/users/index')
                ->has('users', 4)
        );
});

it('can render the show user page', function () {
    $user = User::factory()->create();
    $response = $this->getFromTenant('tenant.users.show', $user);
    $response->assertOk()
        ->assertInertia(
            fn($page) =>
            $page->component('tenants/users/show')
                ->has('item')
                ->where('item.id', $user->id)
        );
});

it('can render the create user page', function () {
    $response = $this->getFromTenant('tenant.users.create');
    $response->assertOk()
        ->assertInertia(
            fn($page) =>
            $page->component('tenants/users/create')
        );
});

it('can render the edit user page', function () {
    $user = User::factory()->create();
    $response = $this->getFromTenant('tenant.users.edit', $user);
    $response->assertOk()
        ->assertInertia(
            fn($page) =>
            $page->component('tenants/users/create')
                ->has('user')
                ->where('user.id', $user->id)
        );
});


it('can post a new "loginable" user and return the password', function () {

    $file1 = UploadedFile::fake()->image('avatar.png');

    $formData = [
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'janedoe@facilitywebxp.be',
        'can_login' => true,
        'role' => 'Maintenance Manager',
        'job_position' => 'Manager',
        'avatar' => $file1
    ];

    $response = $this->postToTenant('api.users.store', $formData);
    $response->assertStatus(200)
        ->assertJson(
            fn(AssertableJson $json) =>
            $json->where('status', 'success')
                ->whereType('data.password', 'string')
                ->etc()
        );

    $createdUser = User::where('email', 'janedoe@facilitywebxp.be')->first();

    assertNotNull($createdUser->password);

    assertDatabaseHas('users', [
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'job_position' => 'Manager',
        'email' => 'janedoe@facilitywebxp.be',
        'can_login' => 1,
        'avatar' => $createdUser->avatar
    ]);

    Storage::disk('tenants')->assertExists($createdUser->avatar);
});

it('can post a new "non loginable" user and attach a provider', function () {
    CategoryType::factory()->create(['category' => 'provider']);
    $provider = Provider::factory()->create();

    $formData = [
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'can_login' => false,
        'job_position' => 'Manager',
        'email' => 'janedoe@facilitywebxp.be',
        'provider_id' => $provider->id
    ];

    $response = $this->postToTenant('api.users.store', $formData);
    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);

    $createdUser = User::where('email', 'janedoe@facilitywebxp.be')->first();

    assertNull($createdUser->password);

    assertDatabaseHas('users', [
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'job_position' => 'Manager',
        'email' => 'janedoe@facilitywebxp.be',
        'provider_id' => $provider->id,
        'can_login' => 0
    ]);
});


it('can update an existing user', function () {

    $user = User::factory()->create();

    $file1 = UploadedFile::fake()->image('avatar.png');

    $formData = [
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'job_position' => 'Manager',
        'email' => 'janedoe@facilitywebxp.be',
        'avatar' => $file1
    ];

    $response = $this->patchToTenant('api.users.update', $formData, $user);
    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
        ]);

    assertDatabaseCount('users', 2);
    assertDatabaseHas('users', [
        'id' => 2,
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'job_position' => 'Manager',
        'email' => 'janedoe@facilitywebxp.be',
        'can_login' => false,
        'avatar' => User::find(2)->avatar
    ]);

    Storage::disk('tenants')->assertExists(User::find(2)->avatar);
});

it('can update the role of an existing user', function () {

    $user = User::factory()->create(['can_login' => true]);
    $user->assignRole('Provider');

    $formData = [
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'janedoe@facilitywebxp.be',
        'can_login' => true,
        'role' => 'Maintenance Manager',
    ];

    $response = $this->patchToTenant('api.users.update', $formData, $user);
    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
        ]);

    assertDatabaseCount('users', 2);
    assertDatabaseHas('users', [
        'id' => 2,
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'janedoe@facilitywebxp.be',
        'can_login' => true,
    ]);

    $user->refresh();
    assertTrue($user->hasRole('Maintenance Manager'));
});


it('can delete an existing user', function () {
    $user = User::factory()->create();

    $response = $this->deleteFromTenant('api.users.destroy', $user);
    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
        ]);

    assertDatabaseMissing('users', ['id' => $user->id]);
});
