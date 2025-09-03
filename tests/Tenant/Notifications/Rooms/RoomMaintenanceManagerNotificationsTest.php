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

it('creates notification when adding maintenance manager to existing building without maintenance manager', function () {

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

    $room = Room::find(1);

    $formData = [
        ...$this->basicRoomData,
        'maintenance_manager_id' => $this->manager->id,
        'need_maintenance' => true,
        'maintenance_frequency' => 'annual',
        'next_maintenance_date' => Carbon::now()->addYear(),
        'last_maintenance_date' => Carbon::now()->toDateString(),
    ];

    $response = $this->patchToTenant('api.rooms.update', $formData, $room->reference_code);
    $response->assertSessionHasNoErrors();
    $response->assertStatus(200);

    assertDatabaseCount('scheduled_notifications', 2);

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

it('creates notification when replacing maintenance manager for the building and removes notifications for old maintenance manager', function () {

    $room = Room::factory()->create();

    $formData = [
        ...$this->basicRoomData,
        'maintenance_manager_id' => $this->manager->id,
        'maintenance_frequency' => 'annual',
        'need_maintenance' => true,
        'next_maintenance_date' => Carbon::now()->addYear(),
        'last_maintenance_date' => Carbon::now()->toDateString(),
    ];

    $response = $this->patchToTenant('api.rooms.update', $formData, $room->reference_code);
    $response->assertSessionHasNoErrors();
    $response->assertStatus(200);

    assertDatabaseCount('scheduled_notifications', 2);

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

it('deletes notification when removing maintenance_manager from existing site', function () {
    $formData = [
        ...$this->basicRoomData,
        'maintenance_frequency' => 'annual',
        'need_maintenance' => true,
        'next_maintenance_date' => Carbon::now()->addYear(),
        'last_maintenance_date' => Carbon::now()->toDateString(),
        'maintenance_manager_id' => $this->manager->id,
    ];

    $response = $this->postToTenant('api.rooms.store', $formData);
    $response->assertSessionHasNoErrors();
    $response->assertStatus(200);

    assertDatabaseCount('scheduled_notifications', 2);
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

    $formData = [
        ...$this->basicRoomData,
        'maintenance_frequency' => 'annual',
        'need_maintenance' => true,
        'next_maintenance_date' => Carbon::now()->addYear(),
        'last_maintenance_date' => Carbon::now()->toDateString(),
    ];

    $room = Room::find(1);
    $response = $this->patchToTenant('api.rooms.update', $formData, $room->reference_code);

    $response->assertSessionHasNoErrors();
    $response->assertStatus(200);

    assertDatabaseCount('scheduled_notifications', 1);
    assertDatabaseMissing(
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
