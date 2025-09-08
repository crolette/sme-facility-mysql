<?php

use Carbon\Carbon;
use App\Models\LocationType;
use App\Models\Tenants\Room;
use App\Models\Tenants\Site;
use App\Models\Tenants\User;
use App\Models\Tenants\Floor;
use App\Models\Tenants\Building;
use App\Enums\MaintenanceFrequency;
use App\Models\Central\CategoryType;
use function PHPUnit\Framework\assertCount;
use function Pest\Laravel\assertDatabaseHas;
use function PHPUnit\Framework\assertEquals;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseMissing;


beforeEach(function () {
    $this->admin = User::factory()->withRole('Admin')->create();
    $this->manager = User::factory()->withRole('Maintenance Manager')->create();
    $this->actingAs($this->admin, 'tenant');
    $this->siteType = LocationType::factory()->create(['level' => 'site']);
    $this->buildingType = LocationType::factory()->create(['level' => 'building']);
    $this->floorType = LocationType::factory()->create(['level' => 'floor']);
    $this->roomType = LocationType::factory()->create(['level' => 'room']);
    $this->site = Site::factory()->create();
    $this->building = Building::factory()->create();
    $this->floor = Floor::factory()->create();
    $this->wallMaterial = CategoryType::factory()->create(['category' => 'wall_materials']);
    $this->floorMaterial = CategoryType::factory()->create(['category' => 'floor_materials']);

    $this->basicRoomData = [
        'name' => 'New building',
        'description' => 'Description new building',
        'surface_floor' => 2569.12,
        'floor_material_id' => $this->floorMaterial->id,
        'surface_walls' => 256.9,
        'wall_material_id' => $this->wallMaterial->id,
        'levelType' => $this->floor->id,
        'description' => 'Description new site',
        'locationType' => $this->roomType->id,
    ];
});


it('creates next maintenance date notification for a new created site with maintenance manager', function () {

    $formData = [
        ...$this->basicRoomData,
        'maintenance_manager_id' => $this->manager->id,
        'maintenance_frequency' => 'annual',
        'need_maintenance' => true,
        'next_maintenance_date' => Carbon::now()->addYear(),
        'last_maintenance_date' => Carbon::now()->toDateString(),
    ];

    $response =  $this->postToTenant('api.rooms.store', $formData);
    $response->assertSessionHasNoErrors();

    assertDatabaseCount('scheduled_notifications', 2);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addYear()->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Room',
            'notifiable_id' => 1,
        ]
    );

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addYear()->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Room',
            'notifiable_id' => 1,
        ]
    );
});

it('updates notification when updating next_maintenance_date of the asset', function () {

    $room = Room::factory()->create();

    $formData = [
        ...$this->basicRoomData,
        'maintenance_manager_id' => $this->manager->id,
        'maintenance_frequency' => 'annual',
        'need_maintenance' => true,
        'next_maintenance_date' => Carbon::now()->addMonth(),
        'last_maintenance_date' => Carbon::now()->toDateString(),
    ];

    $response = $this->patchToTenant('api.rooms.update', $formData, $room->reference_code);

    assertDatabaseCount('scheduled_notifications', 2);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addMonth()->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Room',
            'notifiable_id' => $room->id,
        ]
    );

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addMonth()->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Room',
            'notifiable_id' => $room->id,
        ]
    );

    $newformData = [
        ...$this->basicRoomData,
        'maintenance_manager_id' => $this->manager->id,
        'maintenance_frequency' => 'annual',
        'need_maintenance' => true,
        'next_maintenance_date' => Carbon::now()->addYear(),
        'last_maintenance_date' => Carbon::now()->toDateString(),
    ];

    $room->refresh();
    $response = $this->patchToTenant('api.rooms.update', $newformData, $room->reference_code);
    $response->assertStatus(200);
    $response->assertSessionHasNoErrors();
    assertDatabaseCount('scheduled_notifications', 2);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addYear()->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Room',
            'notifiable_id' => $room->id,
        ]
    );

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addYear()->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Room',
            'notifiable_id' => $room->id,
        ]
    );
});

it('creates no notification if next_maintenance_date is in the past', function () {

    $formData = [
        ...$this->basicRoomData,
        'maintenance_manager_id' => $this->manager->id,
        'maintenance_frequency' => 'annual',
        'need_maintenance' => true,
        'next_maintenance_date' => Carbon::now(),
        'last_maintenance_date' => Carbon::now()->subDays(120),
    ];

    $this->postToTenant('api.rooms.store', $formData);

    assertDatabaseCount('scheduled_notifications', 0);
});

it('creates notification when need_maintenance passes from false to true', function () {

    $formData = [
        ...$this->basicRoomData,
    ];

    $response = $this->postToTenant('api.rooms.store', $formData);
    assertDatabaseCount('scheduled_notifications', 0);
    $response->assertSessionHasNoErrors();
    $response->assertStatus(200);

    $formData = [
        ...$this->basicRoomData,
        'maintenance_frequency' => 'annual',
        'need_maintenance' => true,
        'next_maintenance_date' => Carbon::now()->addYear(),
        'last_maintenance_date' => Carbon::now()->toDateString(),
    ];
    $asset = Room::find(1);
    $response = $this->patchToTenant('api.rooms.update', $formData, $asset->reference_code);

    $response->assertSessionHasNoErrors();
    $response->assertStatus(200);

    assertDatabaseCount('scheduled_notifications', 1);
});

it('deletes notification when need_maintenance passes from true to false', function () {

    $formData = [
        ...$this->basicRoomData,
        'maintenance_frequency' => 'annual',
        'need_maintenance' => true,
        'next_maintenance_date' => Carbon::now()->addYear(),
        'last_maintenance_date' => Carbon::now()->toDateString(),
    ];

    $response = $this->postToTenant('api.rooms.store', $formData);
    $response->assertSessionHasNoErrors();
    $response->assertStatus(200);

    assertDatabaseCount('scheduled_notifications', 1);

    $asset = Room::find(1);

    $formData = [
        ...$this->basicRoomData,
        'maintenance_manager_id' => $this->manager->id,
        'need_maintenance' => false,
    ];

    $response = $this->patchToTenant('api.rooms.update', $formData, $asset->reference_code);
    $response->assertSessionHasNoErrors();
    $response->assertStatus(200);

    assertDatabaseCount('scheduled_notifications', 0);
});

it('update notifications when notification preference next_maintenance_date of user changes', function () {

    $formData = [
        ...$this->basicRoomData,
        'maintenance_manager_id' => $this->manager->id,
        'maintenance_frequency' => 'annual',
        'need_maintenance' => true,
        'next_maintenance_date' => Carbon::now()->addYear(),
        'last_maintenance_date' => Carbon::now()->toDateString(),
    ];

    $response = $this->postToTenant('api.rooms.store', $formData);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addYear()->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Room',
            'notifiable_id' => 1,
        ]
    );

    $preference = $this->admin->notification_preferences()->where('notification_type', 'next_maintenance_date')->first();

    $formData = [
        'asset_type' => 'maintenance',
        'notification_type' => 'next_maintenance_date',
        'notification_delay_days' => 1,
        'enabled' => true,
    ];

    $response = $this->patchToTenant('api.notifications.update', $formData, $preference->id);
    $response->assertStatus(200);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addYear()->subDays(1)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Room',
            'notifiable_id' => 1,
        ]
    );
});


it('deletes notifications when notification preference next_maintenance_date of user is disabled', function () {

    $formData = [
        ...$this->basicRoomData,
        'maintenance_manager_id' => $this->manager->id,
        'maintenance_frequency' => 'annual',
        'need_maintenance' => true,
        'next_maintenance_date' => Carbon::now()->addYear(),
        'last_maintenance_date' => Carbon::now()->toDateString(),

    ];

    $response = $this->postToTenant('api.rooms.store', $formData);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addYear()->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Room',
            'notifiable_id' => 1,
        ]
    );

    $preference = $this->admin->notification_preferences()->where('notification_type', 'next_maintenance_date')->first();

    $formData = [
        'asset_type' => 'maintenance',
        'notification_type' => 'next_maintenance_date',
        'notification_delay_days' => $preference->notification_delay_days,
        'enabled' => false,
    ];

    $response = $this->patchToTenant('api.notifications.update', $formData, $preference->id);
    $response->assertStatus(200);

    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addYear()->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Room',
            'notifiable_id' => 1,
        ]
    );
});


it('creates notifications when notification preference next_maintenance_date of user is enabled', function () {
    $formData = [
        ...$this->basicRoomData,
        'maintenance_manager_id' => $this->manager->id,
        'maintenance_frequency' => 'annual',
        'need_maintenance' => true,
        'next_maintenance_date' => Carbon::now()->addYear(),
        'last_maintenance_date' => Carbon::now()->toDateString(),
    ];

    $response = $this->postToTenant('api.rooms.store', $formData);

    $preference = $this->admin->notification_preferences()->where('notification_type', 'next_maintenance_date')->first();

    $formData = [
        'asset_type' => 'maintenance',
        'notification_type' => 'next_maintenance_date',
        'notification_delay_days' => $preference->notification_delay_days,
        'enabled' => false,
    ];

    $response = $this->patchToTenant('api.notifications.update', $formData, $preference->id);
    $response->assertStatus(200);

    $formData = [
        'asset_type' => 'maintenance',
        'notification_type' => 'next_maintenance_date',
        'notification_delay_days' => $preference->notification_delay_days,
        'enabled' => true,
    ];

    $response = $this->patchToTenant('api.notifications.update', $formData, $preference->id);
    $response->assertStatus(200);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addYear()->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Room',
            'notifiable_id' => 1,
        ]
    );
});
