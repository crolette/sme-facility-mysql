<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

// Disable all tenant-related middleware for central tests
beforeEach(function () {
    $this->withoutMiddleware([
        \Stancl\Tenancy\Middleware\InitializeTenancyByDomain::class,
        \Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain::class,
        \Stancl\Tenancy\Middleware\InitializeTenancyByPath::class,
        \Stancl\Tenancy\Middleware\InitializeTenancyByRequestData::class,
    ]);
});

test('login screen can be rendered', function () {
    $response = $this->get(route('central.login'));

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->post(route('central.login.post'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('central.dashboard', absolute: false));

    $user->delete();
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post(route('central.login.post'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();

    $user->delete();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('central.logout'));

    $this->assertGuest();
    $response->assertRedirect('/');

    $user->delete();
});
