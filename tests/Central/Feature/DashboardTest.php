<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

// uses(RefreshDatabase::class);

// Disable all tenant-related middleware for central tests
beforeEach(function () {
    $this->withoutMiddleware([
        \Stancl\Tenancy\Middleware\InitializeTenancyByDomain::class,
        \Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain::class,
        \Stancl\Tenancy\Middleware\InitializeTenancyByPath::class,
        \Stancl\Tenancy\Middleware\InitializeTenancyByRequestData::class,
        // Add any other tenant middleware you're using
        'tenant', // If you have a custom tenant middleware alias
    ]);
});

test('guests are redirected to the login page', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});

test('authenticated users can visit the dashboard', function () {
    $this->actingAs($user = User::factory()->create());

    $this->get('/dashboard')->assertOk();
});
