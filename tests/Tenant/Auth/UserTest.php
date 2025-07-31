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

beforeEach(function () {
    $this->user = User::factory()->create();
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
                ->has('user')
                ->where('user.id', $user->id)
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


it('can post a new user', function () {

    $file1 = UploadedFile::fake()->image('avatar.png');

    $formData = [
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'janedoe@facilitywebxp.be',
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

    assertDatabaseHas('users', [
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'janedoe@facilitywebxp.be',
        'avatar' => $createdUser->avatar
    ]);

    Storage::disk('tenants')->assertExists($createdUser->avatar);
});


it('can update an existing provider', function () {

    $user = User::factory()->create();

    $file1 = UploadedFile::fake()->image('avatar.png');

    $formData = [
        'first_name' => 'Jane',
        'last_name' => 'Doe',
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
        'email' => 'janedoe@facilitywebxp.be',
        'avatar' => User::find(2)->avatar
    ]);

    Storage::disk('tenants')->assertExists(User::find(2)->avatar);
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
