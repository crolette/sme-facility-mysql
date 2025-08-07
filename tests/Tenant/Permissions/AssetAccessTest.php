<?php

dataset('rolesWithAccess', [
    ['Admin'],
    ['Maintenance Manager'],
]);

it('allows roles to view assets', function (string $role) {
    $user = User::factory()->create();
    $user->assignRole($role);

    actingAs($user)
        ->getJson('/api/assets')
        ->assertOk();
})->with('rolesWithAccess');
