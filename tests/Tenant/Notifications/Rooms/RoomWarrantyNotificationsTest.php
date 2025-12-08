<?php

use Carbon\Carbon;
use App\Models\LocationType;
use App\Models\Tenants\Room;
use App\Models\Tenants\Site;
use App\Models\Tenants\User;
use App\Models\Tenants\Floor;
use App\Models\Tenants\Building;
use App\Models\Central\CategoryType;
use function PHPUnit\Framework\assertCount;
use function Pest\Laravel\assertDatabaseHas;
use function PHPUnit\Framework\assertEquals;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseMissing;


beforeEach(function () {
    $this->admin = User::factory()->withRole('Admin')->create();
    $this->actingAs($this->admin, 'tenant');
    $this->manager = User::factory()->withRole('Maintenance Manager')->create();

    $this->site = Site::factory()->withMaintainableData()->create();
    $this->building = Building::factory()->create();
    $this->floor = Floor::factory()->withMaintainableData()->create();
    $this->roomType = LocationType::factory()->create(['level' => 'room']);

    $this->basicLocationData = [
        'name' => 'New room',
        'description' => 'Description new room',
        'levelType' => $this->floor->id,
        'locationType' => $this->roomType->id,
    ];
});

it('creates end of warranty notification for admin & maintenance manager for a new created room when end_warranty_date > today', function () {

    $formData = [
        ...$this->basicLocationData,
        'under_warranty' => true,
        'end_warranty_date' => Carbon::tomorrow(),
        'maintenance_manager_id' => $this->manager->id,
    ];

    $this->postToTenant('api.rooms.store', $formData);

    $location = Room::first();

    assertDatabaseCount('scheduled_notifications', 2);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'end_warranty_date',
            'scheduled_at' => Carbon::tomorrow()->subDays(7)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_warranty_date',
            'scheduled_at' => Carbon::tomorrow()->subDays(7)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );
});

it('does not create end of warranty notification for admin & maintenance manager for a new created site when end_warranty_date <= today', function () {

    $formData = [
        ...$this->basicLocationData,
        'under_warranty' => true,
        'end_warranty_date' => Carbon::now(),
        'maintenance_manager_id' => $this->manager->id,
    ];

    $this->postToTenant('api.rooms.store', $formData);

    $location = Room::first();

    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'end_warranty_date',
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );

    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_warranty_date',
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );
});

it('updates end of warranty notification when end_warranty_date changes', function () {

    $formData = [
        ...$this->basicLocationData,
        'under_warranty' => true,
        'end_warranty_date' => Carbon::now()->addMonths(10),
        'maintenance_manager_id' => $this->manager->id,
    ];

    $response = $this->postToTenant('api.rooms.store', $formData);

    $location = Room::first();

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'end_warranty_date',
            'scheduled_at' => Carbon::now()->addMonths(10)->subDays(7)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_warranty_date',
            'scheduled_at' => Carbon::now()->addMonths(10)->subDays(7)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );

    $formData = [
        ...$this->basicLocationData,
        'under_warranty' => true,
        'end_warranty_date' => Carbon::now()->addYears(2),
        'maintenance_manager_id' => $this->manager->id,
    ];

    $this->patchToTenant('api.rooms.update', $formData, $location->reference_code);

    assertDatabaseCount('scheduled_notifications', 2);
    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'end_warranty_date',
            'scheduled_at' => Carbon::now()->addYears(2)->subDays(7)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_warranty_date',
            'scheduled_at' => Carbon::now()->addYears(2)->subDays(7)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );
});

it('creates warranty notifications for admin when under_warranty passes from false to true', function () {

    $formData = [
        ...$this->basicLocationData,
    ];

    $this->postToTenant('api.rooms.store', $formData);
    assertDatabaseCount('scheduled_notifications', 0);

    $formData = [
        ...$this->basicLocationData,
        'under_warranty' => true,
        'end_warranty_date' => Carbon::now()->addMonths(10),
    ];

    $location = Room::first();
    $response = $this->patchToTenant('api.rooms.update', $formData, $location->reference_code);

    assertDatabaseCount('scheduled_notifications', 1);
    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'end_warranty_date',
            'scheduled_at' => Carbon::now()->addMonths(10)->subDays(7)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );
});

it('creates warranty notifications for maintenance manager when under_warranty passes from false to true', function () {

    $formData = [
        ...$this->basicLocationData,
    ];

    $this->postToTenant('api.rooms.store', $formData);
    assertDatabaseCount('scheduled_notifications', 0);

    $formData = [
        ...$this->basicLocationData,
        'under_warranty' => true,
        'end_warranty_date' => Carbon::now()->addMonths(10),
        'maintenance_manager_id' => $this->manager->id
    ];

    $location = Room::first();
    $this->patchToTenant('api.rooms.update', $formData, $location->reference_code);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_warranty_date',
            'scheduled_at' => Carbon::now()->addMonths(10)->subDays(7)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );
});

it('deletes warranty notifications for admin when under_warranty passes from true to false', function () {

    $formData = [
        ...$this->basicLocationData,
        'under_warranty' => true,
        'end_warranty_date' => Carbon::now()->addMonths(10),
    ];

    $response = $this->postToTenant('api.rooms.store', $formData);
    $response->assertSessionHasNoErrors();
    $response->assertStatus(200);
    $location = Room::first();

    assertDatabaseCount('scheduled_notifications', 1);
    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'end_warranty_date',
            'scheduled_at' => Carbon::now()->addMonths(10)->subDays(7)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );

    $formData = [
        ...$this->basicLocationData,
        'under_warranty' => false,
    ];

    $this->patchToTenant('api.rooms.update', $formData, $location->reference_code);

    assertDatabaseCount('scheduled_notifications', 0);
    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'end_warranty_date',
            'scheduled_at' => Carbon::now()->addMonths(10)->subDays(7)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );
});

it('deletes warranty notifications for maintenance manager when under_warranty passes from true to false', function () {

    $formData = [
        ...$this->basicLocationData,
        'under_warranty' => true,
        'end_warranty_date' => Carbon::now()->addMonths(10),
        'maintenance_manager_id' => $this->manager->id,
    ];

    $this->postToTenant('api.rooms.store', $formData);

    $location = Room::first();

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_warranty_date',
            'scheduled_at' => Carbon::now()->addMonths(10)->subDays(7)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );

    $formData = [
        ...$this->basicLocationData,
        'under_warranty' => false,
        'maintenance_manager_id' => $this->manager->id,
    ];

    $this->patchToTenant('api.rooms.update', $formData, $location->reference_code);

    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_warranty_date',
            'scheduled_at' => Carbon::now()->addMonths(10)->subDays(7)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );
});

it('deletes warranty notifications for maintenance manager when maintenance manager is removed from the site', function () {

    $formData = [
        ...$this->basicLocationData,
        'under_warranty' => true,
        'end_warranty_date' => Carbon::now()->addMonths(10),
        'maintenance_manager_id' => $this->manager->id,
    ];

    $this->postToTenant('api.rooms.store', $formData);

    $location = Room::first();

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_warranty_date',
            'scheduled_at' => Carbon::now()->addMonths(10)->subDays(7)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );

    $formData = [
        ...$this->basicLocationData,
        'under_warranty' => true,
        'end_warranty_date' => Carbon::now()->addMonths(10),
        'maintenance_manager_id' => null,
    ];

    $this->patchToTenant('api.rooms.update', $formData, $location->reference_code);

    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_warranty_date',
            'scheduled_at' => Carbon::now()->addMonths(10)->subDays(7)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );
});

it('updates warranty notifications when notification preference end_warranty_date of user changes', function () {

    $formData = [
        ...$this->basicLocationData,
        'under_warranty' => true,
        'end_warranty_date' => Carbon::now()->addMonths(10),
        'maintenance_manager_id' => $this->manager->id,
    ];

    $this->postToTenant('api.rooms.store', $formData);

    $location = Room::first();

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'end_warranty_date',
            'scheduled_at' => Carbon::now()->addMonths(10)->subDays(7)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );

    $preference = $this->admin->notification_preferences()->where('notification_type', 'end_warranty_date')->first();

    $formData = [
        'asset_type' => 'maintenance',
        'notification_type' => 'end_warranty_date',
        'notification_delay_days' => 1,
        'enabled' => true,
    ];

    $this->patchToTenant('api.notifications.update', $formData, $preference->id);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'end_warranty_date',
            'scheduled_at' => Carbon::now()->addMonths(10)->subDays(1)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );
});

it('deletes warranty notifications when notification preference end_warranty_date of user is disabled', function () {

    $formData = [
        ...$this->basicLocationData,
        'under_warranty' => true,
        'end_warranty_date' => Carbon::now()->addMonths(10),
        'maintenance_manager_id' => $this->manager->id,
    ];

    $this->postToTenant('api.rooms.store', $formData);
    $location = Room::first();

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'end_warranty_date',
            'scheduled_at' => Carbon::now()->addMonth(10)->subDays(7)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );

    $preference = $this->admin->notification_preferences()->where('notification_type', 'end_warranty_date')->first();

    $formData = [
        'asset_type' => 'maintenance',
        'notification_type' => 'end_warranty_date',
        'notification_delay_days' => $preference->notification_delay_days,
        'enabled' => false,
    ];

    $this->patchToTenant('api.notifications.update', $formData, $preference->id);

    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'end_warranty_date',
            'scheduled_at' => Carbon::now()->addMonth(10)->subDays(7)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );
});

it('creates warranty notification for admin when notification preference warranty_end_date of user is enabled', function () {

    $formData = [
        ...$this->basicLocationData,
        'under_warranty' => true,
        'end_warranty_date' => Carbon::now()->addMonths(10),
    ];

    $this->postToTenant('api.rooms.store', $formData);
    $location = Room::first();

    $preference = $this->admin->notification_preferences()->where('notification_type', 'end_warranty_date')->first();

    $formData = [
        'asset_type' => 'maintenance',
        'notification_type' => 'end_warranty_date',
        'notification_delay_days' => $preference->notification_delay_days,
        'enabled' => false,
    ];

    $this->patchToTenant('api.notifications.update', $formData, $preference->id);

    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'end_warranty_date',
            'scheduled_at' => Carbon::now()->addMonth(10)->subDays(7)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );


    $formData = [
        'asset_type' => 'maintenance',
        'notification_type' => 'end_warranty_date',
        'notification_delay_days' => $preference->notification_delay_days,
        'enabled' => true,
    ];

    $this->patchToTenant('api.notifications.update', $formData, $preference->id);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'end_warranty_date',
            'scheduled_at' => Carbon::now()->addMonth(10)->subDays(7)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );
});

it('creates warranty notification for maintenance manager when notification preference warranty_end_date of user is enabled', function () {

    $formData = [
        ...$this->basicLocationData,
        'under_warranty' => true,
        'end_warranty_date' => Carbon::now()->addMonths(10),
        'maintenance_manager_id' => $this->manager->id,
    ];

    $this->postToTenant('api.rooms.store', $formData);
    $location = Room::first();

    $preference = $this->manager->notification_preferences()->where('notification_type', 'end_warranty_date')->first();

    $formData = [
        'asset_type' => 'maintenance',
        'notification_type' => 'end_warranty_date',
        'notification_delay_days' => $preference->notification_delay_days,
        'enabled' => false,
    ];

    $this->patchToTenant('api.notifications.update', $formData, $preference->id);

    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_warranty_date',
            'scheduled_at' => Carbon::now()->addMonth(10)->subDays(7)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );


    $formData = [
        'asset_type' => 'maintenance',
        'notification_type' => 'end_warranty_date',
        'notification_delay_days' => $preference->notification_delay_days,
        'enabled' => true,
    ];

    $this->patchToTenant('api.notifications.update', $formData, $preference->id);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_warranty_date',
            'scheduled_at' => Carbon::now()->addMonth(10)->subDays(7)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );
});

it('creates warranty notification for admin when notification preference warranty_end_date of user is enabled for warranty_end_date > today', function () {

    $preference = $this->admin->notification_preferences()->where('notification_type', 'end_warranty_date')->first();

    $formData = [
        'asset_type' => 'maintenance',
        'notification_type' => 'end_warranty_date',
        'notification_delay_days' => $preference->notification_delay_days,
        'enabled' => false,
    ];

    $this->patchToTenant('api.notifications.update', $formData, $preference->id);

    $location = Room::factory()->withMaintainableData()->create();
    $location->refresh();
    $location->maintainable->update([
        'under_warranty' => true,
        'end_warranty_date' => Carbon::tomorrow()
    ]);

    $locationInThePast = Room::factory()->withMaintainableData()->create();
    $locationInThePast->refresh();
    $locationInThePast->maintainable->update([
        'under_warranty' => true,
        'end_warranty_date' => Carbon::yesterday()
    ]);

    assertDatabaseCount('scheduled_notifications', 0);

    $formData = [
        'asset_type' => 'maintenance',
        'notification_type' => 'end_warranty_date',
        'notification_delay_days' => $preference->notification_delay_days,
        'enabled' => true,
    ];

    $this->patchToTenant('api.notifications.update', $formData, $preference->id);

    assertDatabaseCount('scheduled_notifications', 1);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'end_warranty_date',
            'scheduled_at' => Carbon::now()->tomorrow()->subDays(7)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );

    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'end_warranty_date',
            'notifiable_type' => get_class($locationInThePast),
            'notifiable_id' => $locationInThePast->id,
        ]
    );
});

it('creates warranty notification for maintenance manager when notification preference warranty_end_date of user is enabled for warranty_end_date > today', function () {

    $preference = $this->manager->notification_preferences()->where('notification_type', 'end_warranty_date')->first();

    $formData = [
        'asset_type' => 'maintenance',
        'notification_type' => 'end_warranty_date',
        'notification_delay_days' => $preference->notification_delay_days,
        'enabled' => false,
    ];

    $this->patchToTenant('api.notifications.update', $formData, $preference->id);

    $location = Room::factory()->withMaintainableData()->create();
    $location->refresh();
    $location->maintainable->update([
        'under_warranty' => true,
        'end_warranty_date' => Carbon::tomorrow(),
        'maintenance_manager_id' => $this->manager->id
    ]);

    $locationInThePast = Room::factory()->withMaintainableData()->create();
    $locationInThePast->refresh();
    $locationInThePast->maintainable->update([
        'under_warranty' => true,
        'end_warranty_date' => Carbon::yesterday(),
        'maintenance_manager_id' => $this->manager->id
    ]);

    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_warranty_date',
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );

    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_warranty_date',
            'notifiable_type' => get_class($locationInThePast),
            'notifiable_id' => $locationInThePast->id,
        ]
    );

    $formData = [
        'asset_type' => 'maintenance',
        'notification_type' => 'end_warranty_date',
        'notification_delay_days' => $preference->notification_delay_days,
        'enabled' => true,
    ];

    $this->patchToTenant('api.notifications.update', $formData, $preference->id);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_warranty_date',
            'scheduled_at' => Carbon::now()->tomorrow()->subDays(7)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );

    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_warranty_date',
            'notifiable_type' => get_class($locationInThePast),
            'notifiable_id' => $locationInThePast->id,
        ]
    );
});

it('creates warranty notifications for a new created user with admin role', function () {

    $location = Room::factory()->withMaintainableData()->create();

    $location->maintainable()->update([
        'under_warranty' => true,
        'end_warranty_date' => Carbon::tomorrow(),
    ]);

    $formData = [
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'janedoe@facilitywebxp.be',
        'can_login' => true,
        'role' => 'Admin',
        'job_position' => 'Manager',
    ];

    $this->postToTenant('api.users.store', $formData);

    $createdUser = User::where('email', 'janedoe@facilitywebxp.be')->first();

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $createdUser->fullName,
            'recipient_email' => $createdUser->email,
            'notification_type' => 'end_warranty_date',
            'scheduled_at' => Carbon::tomorrow()->subDays(7)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );
});

it('creates warranty notifications when the role of a maintenance manager changes to admin', function () {

    $location = Room::factory()->withMaintainableData()->create();

    $location->maintainable()->update([
        'under_warranty' => true,
        'end_warranty_date' => Carbon::tomorrow(),
    ]);

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

    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'recipient_name' => $createdUser->fullName,
            'recipient_email' => $createdUser->email,
            'notification_type' => 'end_warranty_date',
            'scheduled_at' => Carbon::tomorrow()->subDays(7)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );

    $formData = [
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'janedoe@facilitywebxp.be',
        'can_login' => true,
        'role' => 'Admin',
        'job_position' => 'Manager',
    ];

    $this->patchToTenant('api.users.update', $formData, $createdUser->id);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $createdUser->fullName,
            'recipient_email' => $createdUser->email,
            'notification_type' => 'end_warranty_date',
            'scheduled_at' => Carbon::tomorrow()->subDays(7)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );
});

it('deletes warranty notifications when the role of an admin changes to maintenance manager', function () {
    $location = Room::factory()->withMaintainableData()->create();

    $location->maintainable()->update([
        'under_warranty' => true,
        'end_warranty_date' => Carbon::tomorrow(),
    ]);

    $formData = [
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'janedoe@facilitywebxp.be',
        'can_login' => true,
        'role' => 'Admin',
        'job_position' => 'Manager',
    ];

    $this->postToTenant('api.users.store', $formData);

    $createdUser = User::where('email', 'janedoe@facilitywebxp.be')->first();

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $createdUser->fullName,
            'recipient_email' => $createdUser->email,
            'notification_type' => 'end_warranty_date',
            'scheduled_at' => Carbon::tomorrow()->subDays(7)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );

    $formData = [
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'janedoe@facilitywebxp.be',
        'can_login' => true,
        'role' => 'Maintenance Manager',
        'job_position' => 'Manager',
    ];

    $this->patchToTenant('api.users.update', $formData, $createdUser->id);

    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'recipient_name' => $createdUser->fullName,
            'recipient_email' => $createdUser->email,
            'notification_type' => 'end_warranty_date',
            'scheduled_at' => Carbon::tomorrow()->subDays(7)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );
});

it('deletes warranty notifications when the role of an admin changes to maintenance manager for sites where he is not maintenance manager', function () {
    $location = Room::factory()->withMaintainableData()->create();

    $location->maintainable()->update([
        'under_warranty' => true,
        'end_warranty_date' => Carbon::tomorrow(),
    ]);

    $locationWithManager = Room::factory()->withMaintainableData()->create();

    $locationWithManager->maintainable()->update([
        'under_warranty' => true,
        'end_warranty_date' => Carbon::tomorrow(),
    ]);


    $formData = [
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'janedoe@facilitywebxp.be',
        'can_login' => true,
        'role' => 'Admin',
        'job_position' => 'Manager',
    ];

    $this->postToTenant('api.users.store', $formData);

    $createdUser = User::where('email', 'janedoe@facilitywebxp.be')->first();

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $createdUser->fullName,
            'recipient_email' => $createdUser->email,
            'notification_type' => 'end_warranty_date',
            'scheduled_at' => Carbon::tomorrow()->subDays(7)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $createdUser->fullName,
            'recipient_email' => $createdUser->email,
            'notification_type' => 'end_warranty_date',
            'scheduled_at' => Carbon::tomorrow()->subDays(7)->toDateString(),
            'notifiable_type' => get_class($locationWithManager),
            'notifiable_id' => $locationWithManager->id,
        ]
    );

    $locationWithManager->refresh();
    $locationWithManager->maintainable()->update(['maintenance_manager_id' => $createdUser->id]);

    $formData = [
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'janedoe@facilitywebxp.be',
        'can_login' => true,
        'role' => 'Maintenance Manager',
        'job_position' => 'Manager',
    ];

    $this->patchToTenant('api.users.update', $formData, $createdUser->id);

    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'recipient_name' => $createdUser->fullName,
            'recipient_email' => $createdUser->email,
            'notification_type' => 'end_warranty_date',
            'scheduled_at' => Carbon::tomorrow()->subDays(7)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $createdUser->fullName,
            'recipient_email' => $createdUser->email,
            'notification_type' => 'end_warranty_date',
            'scheduled_at' => Carbon::tomorrow()->subDays(7)->toDateString(),
            'notifiable_type' => get_class($locationWithManager),
            'notifiable_id' => $locationWithManager->id,
        ]
    );
});

it('deletes warranty notifications when a user is deleted', function () {

    $location = Room::factory()->withMaintainableData()->create();

    $location->maintainable()->update([
        'under_warranty' => true,
        'end_warranty_date' => Carbon::tomorrow(),
    ]);

    $formData = [
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'janedoe@facilitywebxp.be',
        'can_login' => true,
        'role' => 'Admin',
        'job_position' => 'Manager',
    ];

    $this->postToTenant('api.users.store', $formData);

    $createdUser = User::where('email', 'janedoe@facilitywebxp.be')->first();

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $createdUser->fullName,
            'recipient_email' => $createdUser->email,
            'notification_type' => 'end_warranty_date',
            'scheduled_at' => Carbon::tomorrow()->subDays(7)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );

    $this->deleteFromTenant('api.users.destroy', $createdUser);

    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'recipient_name' => $createdUser->fullName,
            'recipient_email' => $createdUser->email,
            'notification_type' => 'end_warranty_date',
            'scheduled_at' => Carbon::tomorrow()->subDays(7)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );
});
