<?php

use App\Models\User;
use Tests\Concerns\ManagesTenantDatabases;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;


test('login screen can be rendered', function () {
    $response = $this->getFromTenant('tenant.login');
    $response->assertOk();
    $response->assertInertia(
        fn($page) =>
        $page->component('tenants/auth/login')
    );
});

test('users can authenticate using the login screen', function () {

    $user = User::factory()->create(['email' => 'test@test.com']);
    $response = $this->postToTenant('tenant.login.post',  [
        'email' => 'test@test.com',
        'password' => 'password',
    ]);

    $this->assertAuthenticated();

    $response->assertRedirect($this->tenantRoute('tenant.dashboard'));
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->postToTenant('tenant.login.post', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postToTenant('tenant.logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});
