<?php


use Carbon\Carbon;
use App\Models\LocationType;
use App\Models\Tenants\Room;
use App\Models\Tenants\Site;
use App\Models\Tenants\User;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Floor;
use App\Enums\NoticePeriodEnum;
use App\Models\Tenants\Building;

use App\Models\Tenants\Contract;
use App\Models\Tenants\Document;
use App\Models\Tenants\Provider;
use App\Enums\ContractStatusEnum;
use Illuminate\Http\UploadedFile;
use App\Enums\ContractDurationEnum;
use App\Enums\MaintenanceFrequency;
use App\Models\Central\CategoryType;
use App\Enums\ContractRenewalTypesEnum;
use function PHPUnit\Framework\assertCount;
use function Pest\Laravel\assertDatabaseHas;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertNotNull;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseMissing;


beforeEach(function () {

    $this->user = User::factory()->create();
    $this->user->assignRole('Admin');
    $this->actingAs($this->user, 'tenant');

    $this->site = Site::factory()->withMaintainableData()->create();
    $this->building = Building::factory()->create();
    $this->floor = Floor::factory()->withMaintainableData()->create();
    $this->provider = Provider::factory()->create();

    $this->room = Room::factory()->withMaintainableData()->create();

    $this->asset = Asset::factory()->withMaintainableData()->forLocation(Room::first())->create();

    // $this->notificationTypes = [...collect(config('notifications.notification_types'))->flatten()];
});

it('creates default notification preferences when user with role is created', function ($role) {
    $formData = [
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'janedoe@facilitywebxp.be',
        'can_login' => true,
        'role' => $role,
        'job_position' => 'Manager',
    ];

    $response = $this->postToTenant('api.users.store', $formData);
    $response->assertSessionHasNoErrors();
    $response->assertStatus(200);

    $createdUser = User::where('email', 'janedoe@facilitywebxp.be')->first();

    $nbNotifications = collect(config('notifications.notification_types'))->flatten()->count();
    assertDatabaseCount('user_notification_preferences', $nbNotifications);
    assertEquals($createdUser->notification_preferences()->count(), $nbNotifications);
})->with(['Admin', 'Maintenance Manager']);


it('creates default notification preferences when user with no roles is assigned a role', function ($role) {

    $user = User::factory()->create();

    assertDatabaseCount('user_notification_preferences', 0);
    assertEquals($user->notification_preferences()->count(), 0);
    $formData = [
        'first_name' => $user->first_name,
        'last_name' => $user->last_name,
        'email' => $user->email,
        'provider_id' => null,
        'can_login' => true,
        'role' => $role
    ];

    $response = $this->patchToTenant('api.users.update', $formData, $user);
    $response->assertSessionHasNoErrors();

    $nbNotifications = collect(config('notifications.notification_types'))->flatten()->count();
    assertDatabaseCount('user_notification_preferences', $nbNotifications);
    assertEquals($user->notification_preferences()->count(), $nbNotifications);
})->with(['Admin', 'Maintenance Manager']);




it('can update notification preferences days', function () {

    collect(config('notifications.notification_types'))
        ->flatten()
        ->each(function ($type) {
            $formData = [
                'first_name' => 'Jane',
                'last_name' => 'Doe',
                'email' => 'janedoe@facilitywebxp.be',
                'can_login' => true,
                'role' => 'Maintenance Manager',
                'job_position' => 'Manager',
            ];

            $this->postToTenant('api.users.store', $formData);

            $createdUser = User::where('email', 'janedoe@facilitywebxp.be')->first();

            $preference = $createdUser->notification_preferences()->where('notification_type', $type)->first();

            $formData = [
                'asset_type' => $preference->asset_type,
                'notification_type' => $type,
                'notification_delay_days' => 30,
                'enabled' => true,
            ];

            $response = $this->patchToTenant('api.notifications.update', $formData, $preference->id);
            $response->assertStatus(200);

            assertDatabaseHas(
                'user_notification_preferences',
                [
                    'user_id' => $createdUser->id,
                    'asset_type' => $preference->asset_type,
                    'notification_type' => $type,
                    'notification_delay_days' => 30,
                    'enabled' => true,
                ]
            );
        });
});

it('can disable notification preferences', function () {

    collect(config('notifications.notification_types'))
        ->flatten()
        ->each(function ($type) {
            $formData = [
                'first_name' => 'Jane',
                'last_name' => 'Doe',
                'email' => 'janedoe@facilitywebxp.be',
                'can_login' => true,
                'role' => 'Maintenance Manager',
                'job_position' => 'Manager',
            ];

            $this->postToTenant('api.users.store', $formData);

            $createdUser = User::where('email', 'janedoe@facilitywebxp.be')->first();

            $preference = $createdUser->notification_preferences()->where('notification_type', $type)->first();

            $formData = [
                'asset_type' => $preference->asset_type,
                'notification_type' => $type,
                'notification_delay_days' => 30,
                'enabled' => false,
            ];

            $response = $this->patchToTenant('api.notifications.update', $formData, $preference->id);
            $response->assertStatus(200);

            assertDatabaseHas(
                'user_notification_preferences',
                [
                    'user_id' => $createdUser->id,
                    'asset_type' => $preference->asset_type,
                    'notification_type' => $type,
                    'notification_delay_days' => 30,
                    'enabled' => false,
                ]
            );
        });
});
