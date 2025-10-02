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
    User::factory()->withRole('Admin')->create();
    $this->user = User::factory()->withRole('Maintenance Manager')->create();
    $this->actingAs($this->user, 'tenant');
});

it('can, as a logged user, upload an avatar', function () {

    $file1 = UploadedFile::fake()->image('avatar.png');

    $formData = [
        'pictures' => [$file1]
    ];

    $response = $this->postToTenant('api.users.picture.store', $formData, $this->user->id);
    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
        ]);
    $this->user->refresh();
    Storage::disk('tenants')->assertExists($this->user->avatar);
});

it('can, as a logged user, delete his avatar', function () {


    $file1 = UploadedFile::fake()->image('avatar.png');

    $formData = [
        'pictures' => [$file1]
    ];

    $response = $this->postToTenant('api.users.picture.store', $formData, $this->user->id);
    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
        ]);

    $this->user->refresh();

    expect(Storage::disk('tenants')->exists($this->user->avatar))->toBeTrue();

    $response = $this->deleteFromTenant('api.users.picture.destroy',  $this->user->id);
    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
        ]);

    $avatar = $this->user->avatar;
    expect(Storage::disk('tenants')->exists($avatar))->toBeFalse();
});

it('can update the first name and last name', function() {

    $formData = [
        'first_name' => 'Jean-Paul',
        'last_name' => 'Dupont'
    ];

    $response = $this->patchToTenant('tenant.profile.update', $formData, $this->user->id);
    $response->assertOk()->assertJson([
        'status' => 'success',
    ]);

    assertDatabaseHas('users', [
        'id' => $this->user->id,
        'first_name' => 'Jean-Paul',
        'last_name' => 'Dupont'
    ]);
});