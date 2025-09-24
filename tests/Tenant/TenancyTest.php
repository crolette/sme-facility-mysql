<?php

use App\Models\Tenants\User;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\ManagesTenantDatabases;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;


beforeEach(function () {
    $this->tenant = $this->initializeTenancy();
});

afterEach(function () {
    // Truncate only tables used in tests
    DB::table('users')->truncate();
});


it('can access tenant dashboard', function () {
    $this->actingAs($user = User::factory()->withRole('Admin')->create());

    // $url = 'http://test.localhost:8000/dashboard';

    $response = $this->getFromTenant('tenant.dashboard');

    // $response = $this->get(route('tenant.dashboard'));
    $response->assertInertia(
        fn($page) =>
        $page->component('tenants/dashboard')
    );
    $response->assertOk();

    $user->delete();
    // $response->assertSee('Dashboard');
});

it('can create and access tenant user', function () {
    // Using Eloquent model (recommended)

    try {

        $user = User::create([
            'last_name' => 'User',
            'first_name' => 'Tenant',
            'username' => 'user.tenant',
            'email' => 'tenant@example.com',
            'password' => Hash::make('secret'),
        ]);

        expect($user)->toBeInstanceOf(User::class);
        expect($user->email)->toBe('tenant@example.com');

        assertDatabaseHas('users', [
            'email' => 'tenant@example.com',
        ]);
    } finally {
        $user->delete();
    }
});



// it('tenant users are isolated between tenants', function () {

//     try {
//         // Create user in first tenant
//         $user1 = User::create([
//             'last_name' => 'Last name Tenant User 1',
//             'username' => 'username_one',
//             'first_name' => 'First Tenant User 1',
//             'email' => 'user1@tenant1.com',
//             'password' => Hash::make('secret'),
//         ]);

//         // Switch to second tenant
//         dump(tenancy()->tenant->id);
//         dump(tenancy()->initialized);
//         tenancy()->end();

//         $userTenant = User::factory()->raw();
//         Session::put([...$userTenant]);

//         $tenant2 = Tenant::factory()->create();

//         tenancy()->initialize($tenant2);

//         // User from first tenant should not exist in second tenant
//         assertDatabaseMissing('users', [
//             'email' => 'user1@tenant1.com',
//         ]);

//         // Create different user in second tenant
//         User::create([
//             'last_name' => 'Last name Tenant User 2',
//             'username' => 'username_two',
//             'first_name' => 'First Tenant User 2',
//             'email' => 'user2@tenant2.com',
//             'password' => Hash::make('secret'),
//         ]);

//         assertDatabaseHas('users', [
//             'email' => 'user2@tenant2.com',
//         ]);

//         tenancy()->end();
//     } finally {

//         $user1?->delete();
//         $tenant2->delete();
//     }
// });

it('can check tenant database connection', function () {
    expect(tenancy()->initialized)->toBeTrue();
    expect(tenancy()->tenant->id)->toBe($this->tenant->id);

    // Verify we're using the correct database
    $currentDatabase = DB::getDatabaseName();
    expect($currentDatabase)->toContain($this->tenant->id);
});

it('can perform database operations in tenant context', function () {
    // Test multiple database operations
    $users = collect([
        ['first_name' => 'User 1', 'last_name' => 'User 1', 'username' => 'username1', 'email' => 'user1@test.com'],
        ['first_name' => 'User 2', 'last_name' => 'User 1', 'username' => 'username2', 'email' => 'user2@test.com'],
        ['first_name' => 'User 3', 'last_name' => 'User 1', 'username' => 'username3', 'email' => 'user3@test.com'],
    ]);

    foreach ($users as $userData) {
        User::create(array_merge($userData, ['password' => Hash::make('password')]));
    }

    expect(User::count())->toBe(3);

    $emails = User::pluck('email')->toArray();
    expect($emails)->toContain('user1@test.com', 'user2@test.com', 'user3@test.com');
});
