<?php

use Carbon\Carbon;
use App\Models\LocationType;
use App\Models\Tenants\Site;
use App\Models\Tenants\User;
use App\Models\Tenants\Floor;
use App\Models\Tenants\Building;
use App\Enums\MaintenanceFrequency;
use App\Models\Central\CategoryType;
use function PHPUnit\Framework\assertCount;
use function Pest\Laravel\assertDatabaseHas;
use function PHPUnit\Framework\assertEquals;
use App\Models\Tenants\ScheduledNotification;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseMissing;


beforeEach(function () {
    $this->admin = User::factory()->withRole('Admin')->create();
    $this->manager = User::factory()->withRole('Maintenance Manager')->create();
    $this->actingAs($this->admin, 'tenant');
    $this->floorType = LocationType::factory()->create(['level' => 'floor']);
    $this->site = Site::factory()->withMaintainableData()->create();
    $this->building = Building::factory()->withMaintainableData()->create();

    $this->basicLocationData = [
        'name' => 'New floor',
        'description' => 'Description new floor',
        'levelType' => $this->building->id,
        'locationType' => $this->floorType->id,
    ];
});


it('creates next_maintenance_date notification (for admin & manager) based on frequency when a new floor is created with next_maintenance_date defined', function ($frequency) {

    $formData = [
        ...$this->basicLocationData,
        'maintenance_manager_id' => $this->manager->id,
        'maintenance_frequency' => $frequency,
        'need_maintenance' => true,
        'next_maintenance_date' => Carbon::now()->addDays(MaintenanceFrequency::from($frequency)->days())->toDateString(),
        'last_maintenance_date' => Carbon::now()->toDateString(),
    ];

    $this->postToTenant('api.floors.store', $formData);

    $location = Floor::first();

    assertDatabaseCount('scheduled_notifications', 2);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addDays(MaintenanceFrequency::from($frequency)->days())->subDays(7)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addDays(MaintenanceFrequency::from($frequency)->days())->subDays(7)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );
})->with(array_values(array_diff(array_column(MaintenanceFrequency::cases(), 'value'), ['on_demand'])));

it('creates next_maintenance_date notification when next_maintenance_date is not defined and last_maintenance_date is defined', function ($frequency) {

    $formData = [
        ...$this->basicLocationData,
        'maintenance_manager_id' => $this->manager->id,
        'maintenance_frequency' => $frequency,
        'need_maintenance' => true,
        'last_maintenance_date' => Carbon::now()->subMonths(4)->toDateString()
    ];

    $this->postToTenant('api.floors.store', $formData);

    $location = Floor::first();

    $nextMaintenanceDate = Carbon::now()->subMonths(4)->addDays(MaintenanceFrequency::from($frequency)->days())->toDateString();

    if ($nextMaintenanceDate < now())
        $expectedDate = Carbon::now()->subDays(7)->addDays(MaintenanceFrequency::from($frequency)->days())->toDateString();
    else
        $expectedDate = Carbon::now()->subMonths(4)->subDays(7)->addDays(MaintenanceFrequency::from($frequency)->days())->toDateString();


    assertDatabaseCount('scheduled_notifications', 2);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => $expectedDate,
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => $expectedDate,
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );
})->with(array_values(array_diff(array_column(MaintenanceFrequency::cases(), 'value'), ['on_demand'])));

it('creates next_maintenance_date notification when next/last_maintenance_date are not defined ', function ($frequency) {

    $formData = [
        ...$this->basicLocationData,
        'maintenance_manager_id' => $this->manager->id,
        'maintenance_frequency' => $frequency,
        'need_maintenance' => true,
    ];

    $this->postToTenant('api.floors.store', $formData);
    $location = Floor::first();

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->subDays(7)->addDays(MaintenanceFrequency::from($frequency)->days())->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->subDays(7)->addDays(MaintenanceFrequency::from($frequency)->days())->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );
})->with(array_values(array_diff(array_column(MaintenanceFrequency::cases(), 'value'), ['on_demand'])));


it('updates next_maintenance_date notification when updating next_maintenance_date of the location manually', function ($frequency) {

    $location = Floor::factory()->withMaintainableData()->create();

    $formData = [
        ...$this->basicLocationData,
        'locationType' => $location->location_type_id,
        'maintenance_manager_id' => $this->manager->id,
        'maintenance_frequency' => $frequency,
        'need_maintenance' => true,
        'next_maintenance_date' => Carbon::now()->addMonth(),
        'last_maintenance_date' => Carbon::now()->toDateString(),
    ];

    $this->patchToTenant('api.floors.update', $formData, $location->reference_code);

    assertDatabaseCount('scheduled_notifications', 2);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addMonth()->subDays(7)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addMonth()->subDays(7)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );

    $newformData = [
        ...$this->basicLocationData,
        'locationType' => $location->location_type_id,
        'maintenance_manager_id' => $this->manager->id,
        'maintenance_frequency' => $frequency,
        'need_maintenance' => true,
        'next_maintenance_date' => Carbon::now()->addYear(),
        'last_maintenance_date' => Carbon::now()->toDateString(),
    ];

    $location->refresh();
    $this->patchToTenant('api.floors.update', $newformData, $location->reference_code);
    assertDatabaseCount('scheduled_notifications', 2);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addYear()->subDays(7)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addYear()->subDays(7)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );
})->with(array_values(array_diff(array_column(MaintenanceFrequency::cases(), 'value'), ['on_demand'])));

it('does not create a next_maintenance_date notification when next_maintenance_date is today', function ($frequency) {

    $formData = [
        ...$this->basicLocationData,
        'maintenance_manager_id' => $this->manager->id,
        'maintenance_frequency' => $frequency,
        'need_maintenance' => true,
        'next_maintenance_date' => Carbon::now(),
        'last_maintenance_date' => Carbon::now()->subDays(120),
    ];

    $this->postToTenant('api.floors.store', $formData);

    assertDatabaseCount('scheduled_notifications', 0);
})->with(array_values(array_diff(array_column(MaintenanceFrequency::cases(), 'value'), ['on_demand'])));

it('creates a notification if next_maintenance_date is > today even if the scheduled_at is in the past', function ($frequency) {

    $formData = [
        ...$this->basicLocationData,
        'maintenance_manager_id' => $this->manager->id,
        'maintenance_frequency' => $frequency,
        'need_maintenance' => true,
        'next_maintenance_date' => Carbon::tomorrow(),
        'last_maintenance_date' => Carbon::now()->subDays(120),
    ];

    $this->postToTenant('api.floors.store', $formData);

    $location = Floor::first();
    assertDatabaseCount('scheduled_notifications', 2);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'next_maintenance_date',
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
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::tomorrow()->subDays(7)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );
})->with(array_values(array_diff(array_column(MaintenanceFrequency::cases(), 'value'), ['on_demand'])));

it('updates notification when updating next_maintenance_date of the location and scheduled_at will be in the past', function ($frequency) {

    $location = Floor::factory()->withMaintainableData()->create();

    $formData = [
        ...$this->basicLocationData,
        'locationType' => $location->location_type_id,
        'maintenance_manager_id' => $this->manager->id,
        'maintenance_frequency' => $frequency,
        'need_maintenance' => true,
        'next_maintenance_date' => Carbon::yesterday(),
        'last_maintenance_date' => Carbon::now()->subMonth(),
    ];

    assertDatabaseCount('scheduled_notifications', 0);

    $this->patchToTenant('api.floors.update', $formData, $location->reference_code);

    $newformData = [
        ...$this->basicLocationData,
        'locationType' => $location->location_type_id,
        'maintenance_manager_id' => $this->manager->id,
        'maintenance_frequency' => $frequency,
        'need_maintenance' => true,
        'next_maintenance_date' => Carbon::tomorrow(),
        'last_maintenance_date' => Carbon::now()->toDateString(),
    ];

    $location->refresh();
    $response = $this->patchToTenant('api.floors.update', $newformData, $location->reference_code);
    assertDatabaseCount('scheduled_notifications', 2);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'next_maintenance_date',
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
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::tomorrow()->subDays(7)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );
})->with(array_values(array_diff(array_column(MaintenanceFrequency::cases(), 'value'), ['on_demand'])));

it('creates notification when the scheduled_at notification was previously in the past', function ($frequency) {

    $location = Floor::factory()->withMaintainableData()->create();

    $formData = [
        ...$this->basicLocationData,
        'locationType' => $location->location_type_id,
        'maintenance_manager_id' => $this->manager->id,
        'maintenance_frequency' => $frequency,
        'need_maintenance' => true,
        'next_maintenance_date' => Carbon::now(),
        'last_maintenance_date' => Carbon::now()->toDateString(),
    ];

    $this->patchToTenant('api.floors.update', $formData, $location->reference_code);

    assertDatabaseCount('scheduled_notifications', 0);

    $formData = [
        ...$this->basicLocationData,
        'locationType' => $location->location_type_id,
        'maintenance_manager_id' => $this->manager->id,
        'maintenance_frequency' => $frequency,
        'need_maintenance' => true,
        'next_maintenance_date' => Carbon::now()->addMonth(),
        'last_maintenance_date' => Carbon::now()->toDateString(),
    ];

    $location->refresh();
    $response = $this->patchToTenant('api.floors.update', $formData, $location->reference_code);
    $response->assertSessionHasNoErrors();

    assertDatabaseCount('scheduled_notifications', 2);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addMonth()->subDays(7)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addMonth()->subDays(7)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );
})->with(array_values(array_diff(array_column(MaintenanceFrequency::cases(), 'value'), ['on_demand'])));

it('creates notification when need_maintenance passes from false to true', function ($frequency) {

    $location = Floor::factory()->withMaintainableData()->create();

    assertDatabaseCount('scheduled_notifications', 0);

    $formData = [
        ...$this->basicLocationData,
        'locationType' => $location->location_type_id,
        'maintenance_frequency' => $frequency,
        'maintenance_manager_id' => $this->manager->id,
        'need_maintenance' => true,
        'next_maintenance_date' => Carbon::now()->addDays(MaintenanceFrequency::from($frequency)->days())->toDateString(),
        'last_maintenance_date' => Carbon::now()->toDateString(),
    ];

    $location = Floor::first();
    $this->patchToTenant('api.floors.update', $formData, $location->reference_code);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addDays(MaintenanceFrequency::from($frequency)->days())->subDays(7)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addDays(MaintenanceFrequency::from($frequency)->days())->subDays(7)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );
})->with(array_values(array_diff(array_column(MaintenanceFrequency::cases(), 'value'), ['on_demand'])));

it('deletes notification when need_maintenance passes from true to false', function ($frequency) {

    $formData = [
        ...$this->basicLocationData,
        'maintenance_frequency' => $frequency,
        'maintenance_manager_id' => $this->manager->id,
        'need_maintenance' => true,
        'next_maintenance_date' => Carbon::now()->addYear(),
        'last_maintenance_date' => Carbon::now()->toDateString(),
    ];

    $this->postToTenant('api.floors.store', $formData);
    $location = Floor::first();

    assertDatabaseCount('scheduled_notifications', 2);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addYear()->subDays(7)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addYear()->subDays(7)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );

    $formData = [
        ...$this->basicLocationData,
        'maintenance_manager_id' => $this->manager->id,
        'need_maintenance' => false,
    ];

    $this->patchToTenant('api.floors.update', $formData, $location->reference_code);

    assertDatabaseCount('scheduled_notifications', 0);
    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'next_maintenance_date',
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );

    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'next_maintenance_date',
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );
})->with(array_values(array_diff(array_column(MaintenanceFrequency::cases(), 'value'), ['on_demand'])));

it('update notifications when notification preference next_maintenance_date of user changes', function ($frequency) {

    $formData = [
        ...$this->basicLocationData,
        'maintenance_frequency' => $frequency,
        'need_maintenance' => true,
        'next_maintenance_date' => Carbon::now()->addDays(MaintenanceFrequency::from($frequency)->days())->toDateString(),
        'last_maintenance_date' => Carbon::now()->toDateString(),

    ];

    $this->postToTenant('api.floors.store', $formData);

    $location = Floor::first();

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addDays(MaintenanceFrequency::from($frequency)->days())->subDays(7)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );

    $preference = $this->admin->notification_preferences()->where('notification_type', 'next_maintenance_date')->first();

    $formData = [
        'asset_type' => 'maintenance',
        'notification_type' => 'next_maintenance_date',
        'notification_delay_days' => 1,
        'enabled' => true,
    ];

    $this->patchToTenant('api.notifications.update', $formData, $preference->id);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addDays(MaintenanceFrequency::from($frequency)->days())->subDays(1)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );
})->with(array_values(array_diff(array_column(MaintenanceFrequency::cases(), 'value'), ['on_demand'])));

it('deletes notifications when notification preference next_maintenance_date of user changes from enabled to disabled', function ($frequency) {

    $formData = [
        ...$this->basicLocationData,
        'maintenance_frequency' => $frequency,
        'need_maintenance' => true,
        'next_maintenance_date' => Carbon::now()->addYear(),
        'last_maintenance_date' => Carbon::now()->toDateString(),

    ];

    $this->postToTenant('api.floors.store', $formData);

    $location = Floor::first();

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addYear()->subDays(7)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );

    $preference = $this->admin->notification_preferences()->where('notification_type', 'next_maintenance_date')->first();

    $formData = [
        'asset_type' => 'maintenance',
        'notification_type' => 'next_maintenance_date',
        'notification_delay_days' => $preference->notification_delay_days,
        'enabled' => false,
    ];

    $this->patchToTenant('api.notifications.update', $formData, $preference->id);

    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addYear()->subDays(7)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );
})->with(array_values(array_diff(array_column(MaintenanceFrequency::cases(), 'value'), ['on_demand'])));

it('creates notifications when notification preference next_maintenance_date of user changes from disabled to enabled', function ($frequency) {

    $formData = [
        ...$this->basicLocationData,
        'maintenance_frequency' => $frequency,
        'need_maintenance' => true,
        'next_maintenance_date' => Carbon::now()->addDays(MaintenanceFrequency::from($frequency)->days())->toDateString(),
        'last_maintenance_date' => Carbon::now()->toDateString(),
    ];

    $this->postToTenant('api.floors.store', $formData);

    $location = Floor::first();

    assertDatabaseCount('scheduled_notifications', 1);

    $preference = $this->admin->notification_preferences()->where('notification_type', 'next_maintenance_date')->first();

    $formData = [
        'asset_type' => 'maintenance',
        'notification_type' => 'next_maintenance_date',
        'notification_delay_days' => $preference->notification_delay_days,
        'enabled' => false,
    ];

    $this->patchToTenant('api.notifications.update', $formData, $preference->id);
    assertDatabaseCount('scheduled_notifications', 0);

    $formData = [
        'asset_type' => 'maintenance',
        'notification_type' => 'next_maintenance_date',
        'notification_delay_days' => $preference->notification_delay_days,
        'enabled' => true,
    ];

    $this->patchToTenant('api.notifications.update', $formData, $preference->id);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addDays(MaintenanceFrequency::from($frequency)->days())->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );
})->with(array_values(array_diff(array_column(MaintenanceFrequency::cases(), 'value'), ['on_demand'])));

it('updates notification when maintenance is marked as done and notification is not sent', function ($frequency) {

    $formData = [
        ...$this->basicLocationData,
        'maintenance_manager_id' => $this->manager->id,
        'maintenance_frequency' => $frequency,
        'need_maintenance' => true,
        'last_maintenance_date' => Carbon::now()->subDays(18)->toDateString(),
    ];

    $this->postToTenant('api.floors.store', $formData);
    assertDatabaseCount('scheduled_notifications', 2);

    $location = Floor::first();

    $this->patchToTenant('api.maintenance.done', [], $location->maintainable);

    assertDatabaseCount('scheduled_notifications', 2);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'next_maintenance_date',
            'status' => 'pending',
            'scheduled_at' => Carbon::now()->addDays(MaintenanceFrequency::from($frequency)->days())->subDays(7)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'next_maintenance_date',
            'status' => 'pending',
            'scheduled_at' => Carbon::now()->addDays(MaintenanceFrequency::from($frequency)->days())->subDays(7)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );
})->with(array_values(array_diff(array_column(MaintenanceFrequency::cases(), 'value'), ['on_demand'])));

it('creates new notification when maintenance is marked as done and other notifications already sent', function ($frequency) {

    $formData = [
        ...$this->basicLocationData,
        'maintenance_manager_id' => $this->manager->id,
        'maintenance_frequency' => $frequency,
        'need_maintenance' => true,
        // 'last_maintenance_date' => Carbon::now()->subDays(18)->toDateString(),
    ];

    $this->postToTenant('api.floors.store', $formData);
    assertDatabaseCount('scheduled_notifications', 2);

    $location = Floor::first();

    ScheduledNotification::updateOrCreate(
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'next_maintenance_date',
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
            'status' => 'pending'
        ],
        [
            'status' => 'sent'
        ]
    );

    ScheduledNotification::updateOrCreate(
        [
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'next_maintenance_date',
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
            'status' => 'pending'
        ],
        [
            'status' => 'sent'
        ]
    );

    $response = $this->patchToTenant('api.maintenance.done', [], $location->maintainable);

    assertDatabaseCount('scheduled_notifications', 4);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'next_maintenance_date',
            'status' => 'sent',
            'scheduled_at' => Carbon::now()->addDays(MaintenanceFrequency::from($frequency)->days())->subDays(7)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'next_maintenance_date',
            'status' => 'sent',
            'scheduled_at' => Carbon::now()->addDays(MaintenanceFrequency::from($frequency)->days())->subDays(7)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'next_maintenance_date',
            'status' => 'pending',
            'scheduled_at' => Carbon::now()->addDays(MaintenanceFrequency::from($frequency)->days())->subDays(7)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'next_maintenance_date',
            'status' => 'pending',
            'scheduled_at' => Carbon::now()->addDays(MaintenanceFrequency::from($frequency)->days())->subDays(7)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );
})->with(array_values(array_diff(array_column(MaintenanceFrequency::cases(), 'value'), ['on_demand'])));

it('creates notification when next_maintenance_date of ONDEMAND is given', function () {
    $formData = [
        ...$this->basicLocationData,
        'maintenance_manager_id' => $this->manager->id,
        'maintenance_frequency' => 'on_demand',
        'need_maintenance' => true,
        'next_maintenance_date' => Carbon::now()->addDays(14)->toDateString(),
        'last_maintenance_date' => Carbon::now()->toDateString(),
    ];

    $this->postToTenant('api.floors.store', $formData);

    $location = Floor::first();

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addDays(14)->subDays(7)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addDays(14)->subDays(7)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );
});

it('updates notification when next_maintenance_date of ONDEMAND is changed', function () {

    $formData = [
        ...$this->basicLocationData,
        'maintenance_manager_id' => $this->manager->id,
        'maintenance_frequency' => 'on_demand',
        'need_maintenance' => true,
        'next_maintenance_date' => Carbon::now()->addDays(14)->toDateString(),
        'last_maintenance_date' => Carbon::now()->toDateString(),
    ];

    $this->postToTenant('api.floors.store', $formData);
    $location = Floor::first();
    assertDatabaseCount('scheduled_notifications', 2);

    $formData = [
        ...$this->basicLocationData,
        'maintenance_manager_id' => $this->manager->id,
        'maintenance_frequency' => 'on_demand',
        'need_maintenance' => true,
        'next_maintenance_date' => Carbon::now()->addMonth(),
        'last_maintenance_date' => Carbon::now()->toDateString(),
    ];

    $this->patchToTenant('api.floors.update', $formData, $location->reference_code);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addMonth()->subDays(7)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addMonth()->subDays(7)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );
});

it('creates notification when maintenance_frequency changes from ONDEMAND to another one', function ($frequency) {

    $formData = [
        ...$this->basicLocationData,
        'maintenance_manager_id' => $this->manager->id,
        'maintenance_frequency' => 'on_demand',
        'need_maintenance' => true,
        'last_maintenance_date' => Carbon::now()->toDateString(),
    ];

    $this->postToTenant('api.floors.store', $formData);

    $location = Floor::first();

    assertDatabaseCount('scheduled_notifications', 0);

    $formData = [
        ...$this->basicLocationData,
        'maintenance_manager_id' => $this->manager->id,
        'maintenance_frequency' => $frequency,
        'need_maintenance' => true,
        'last_maintenance_date' => Carbon::now()->toDateString(),
    ];

    $this->patchToTenant('api.floors.update', $formData, $location->reference_code);

    assertDatabaseCount('scheduled_notifications', 2);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addDays(MaintenanceFrequency::from($frequency)->days())->subDays(7)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addDays(MaintenanceFrequency::from($frequency)->days())->subDays(7)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );
})->with(array_values(array_diff(array_column(MaintenanceFrequency::cases(), 'value'), ['on_demand'])));

it('creates next_maintenance_date notifications for a new created user with admin role', function ($frequency) {

    $location = Floor::factory()->withMaintainableData()->create();

    $location->maintainable()->update([
        'maintenance_frequency' => $frequency,
        'need_maintenance' => true,
        'last_maintenance_date' => Carbon::now()->toDateString(),
        'next_maintenance_date' => Carbon::now()->addDays(MaintenanceFrequency::from($frequency)->days())->toDateString(),
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
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addDays(MaintenanceFrequency::from($frequency)->days())->subDays(7)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );
})->with(array_values(array_diff(array_column(MaintenanceFrequency::cases(), 'value'), ['on_demand'])));

it('creates next_maintenance_date notifications when the role of a maintenance manager changes to admin', function ($frequency) {

    $location = Floor::factory()->withMaintainableData()->create();

    $location->maintainable()->update([
        'maintenance_frequency' => $frequency,
        'need_maintenance' => true,
        'last_maintenance_date' => Carbon::now()->toDateString(),
        'next_maintenance_date' => Carbon::now()->addDays(MaintenanceFrequency::from($frequency)->days())->toDateString(),
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
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addDays(MaintenanceFrequency::from($frequency)->days())->subDays(7)->toDateString(),
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
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addDays(MaintenanceFrequency::from($frequency)->days())->subDays(7)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );
})->with(array_values(array_diff(array_column(MaintenanceFrequency::cases(), 'value'), ['on_demand'])));

it('deletes next_maintenance_date notifications when the role of an admin changes to maintenance manager', function ($frequency) {
    $location = Floor::factory()->withMaintainableData()->create();

    $location->maintainable()->update([
        'maintenance_frequency' => $frequency,
        'need_maintenance' => true,
        'last_maintenance_date' => Carbon::now()->toDateString(),
        'next_maintenance_date' => Carbon::now()->addDays(MaintenanceFrequency::from($frequency)->days())->toDateString(),
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
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addDays(MaintenanceFrequency::from($frequency)->days())->subDays(7)->toDateString(),
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
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addDays(MaintenanceFrequency::from($frequency)->days())->subDays(7)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );
})->with(array_values(array_diff(array_column(MaintenanceFrequency::cases(), 'value'), ['on_demand'])));

it('deletes next_maintenance_date notifications when the role of an admin changes to maintenance manager for sites only where he is not maintenance manager', function ($frequency) {
    $location = Floor::factory()->withMaintainableData()->create();

    $location->maintainable()->update([
        'maintenance_frequency' => $frequency,
        'need_maintenance' => true,
        'last_maintenance_date' => Carbon::now()->toDateString(),
        'next_maintenance_date' => Carbon::now()->addDays(MaintenanceFrequency::from($frequency)->days())->toDateString(),
    ]);

    $locationWithoutManager = Floor::factory()->withMaintainableData()->create();

    $locationWithoutManager->maintainable()->update([
        'maintenance_frequency' => $frequency,
        'need_maintenance' => true,
        'last_maintenance_date' => Carbon::now()->toDateString(),
        'next_maintenance_date' => Carbon::now()->addDays(MaintenanceFrequency::from($frequency)->days())->toDateString(),
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
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addDays(MaintenanceFrequency::from($frequency)->days())->subDays(7)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $createdUser->fullName,
            'recipient_email' => $createdUser->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addDays(MaintenanceFrequency::from($frequency)->days())->subDays(7)->toDateString(),
            'notifiable_type' => get_class($locationWithoutManager),
            'notifiable_id' => $locationWithoutManager->id,
        ]
    );

    $location->refresh();
    $location->maintainable()->update(['maintenance_manager_id' => $createdUser->id]);

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
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addDays(MaintenanceFrequency::from($frequency)->days())->subDays(7)->toDateString(),
            'notifiable_type' => get_class($locationWithoutManager),
            'notifiable_id' => $locationWithoutManager->id,
        ]
    );

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $createdUser->fullName,
            'recipient_email' => $createdUser->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addDays(MaintenanceFrequency::from($frequency)->days())->subDays(7)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );
})->with(array_values(array_diff(array_column(MaintenanceFrequency::cases(), 'value'), ['on_demand'])));

it('deletes next_maintenance_date notifications when a user is deleted', function ($frequency) {

    $location = Floor::factory()->withMaintainableData()->create();

    $location->maintainable()->update([
        'maintenance_frequency' => $frequency,
        'need_maintenance' => true,
        'last_maintenance_date' => Carbon::now()->toDateString(),
        'next_maintenance_date' => Carbon::now()->addDays(MaintenanceFrequency::from($frequency)->days())->toDateString(),
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
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addDays(MaintenanceFrequency::from($frequency)->days())->subDays(7)->toDateString(),
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
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addDays(MaintenanceFrequency::from($frequency)->days())->subDays(7)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );
})->with(array_values(array_diff(array_column(MaintenanceFrequency::cases(), 'value'), ['on_demand'])));
