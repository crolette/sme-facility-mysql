<?php

use Carbon\Carbon;
use App\Models\LocationType;
use App\Models\Tenants\Site;
use App\Models\Tenants\User;
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
    LocationType::factory()->create(['level' => 'site']);
    $this->buildingType = LocationType::factory()->create(['level' => 'building']);
    $this->site = Site::factory()->create();

    $this->basicLocationData = [
        'name' => 'New building',
        'description' => 'Description new building',
        'levelType' => $this->site->id,
        'locationType' => $this->buildingType->id,
    ];
});

it('creates notification when adding maintenance manager to existing site without maintenance manager', function ($frequency) {

    $formData = [
        ...$this->basicLocationData,
        'maintenance_frequency' => $frequency,
        'need_maintenance' => true,
        'last_maintenance_date' => Carbon::now()->toDateString(),
    ];

    $response = $this->postToTenant('api.buildings.store', $formData);
    $response->assertSessionHasNoErrors();
    $response->assertStatus(200);

    assertDatabaseCount('scheduled_notifications', 1);

    $location = Building::first();

    $formData = [
        ...$this->basicLocationData,
        'maintenance_manager_id' => $this->manager->id,
        'maintenance_frequency' => $frequency,
        'need_maintenance' => true,
        'last_maintenance_date' => Carbon::now()->toDateString(),
    ];

    $response = $this->patchToTenant('api.buildings.update', $formData, $location->reference_code);

    assertDatabaseCount('scheduled_notifications', 2);

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
})->with(array_values(array_diff(array_column(MaintenanceFrequency::cases(), 'value'), ['on demand'])));

it('creates notification when replacing maintenance manager for the site and removes notifications for old maintenance manager', function ($frequency) {

    $tempManager =  User::factory()->withRole('Maintenance Manager')->create();

    $formData = [
        ...$this->basicLocationData,
        'maintenance_manager_id' => $tempManager->id,
        'maintenance_frequency' => $frequency,
        'need_maintenance' => true,
        'last_maintenance_date' => Carbon::now()->toDateString(),
    ];

    $this->postToTenant('api.buildings.store', $formData);

    $location = Building::first();

    assertDatabaseCount('scheduled_notifications', 2);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $tempManager->fullName,
            'recipient_email' => $tempManager->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addDays(MaintenanceFrequency::from($frequency)->days())->subDays(7)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );


    $formData = [
        ...$this->basicLocationData,
        'maintenance_manager_id' => $this->manager->id,
        'maintenance_frequency' => $frequency,
        'need_maintenance' => true,
        'last_maintenance_date' => Carbon::now()->toDateString(),
    ];

    $this->patchToTenant('api.buildings.update', $formData, $location->reference_code);

    assertDatabaseCount('scheduled_notifications', 2);

    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'recipient_name' => $tempManager->fullName,
            'recipient_email' => $tempManager->email,
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
})->with(array_values(array_diff(array_column(MaintenanceFrequency::cases(), 'value'), ['on demand'])));

it('deletes notification when removing maintenance_manager from existing asset', function ($frequency) {

    $formData = [
        ...$this->basicLocationData,
        'maintenance_frequency' => $frequency,
        'need_maintenance' => true,
        'last_maintenance_date' => Carbon::now()->toDateString(),
        'maintenance_manager_id' => $this->manager->id,
    ];

    $this->postToTenant('api.buildings.store', $formData);

    $location = Building::first();

    assertDatabaseCount('scheduled_notifications', 2);
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

    $formData = [
        ...$this->basicLocationData,
        'maintenance_frequency' => $frequency,
        'need_maintenance' => true,
        'last_maintenance_date' => Carbon::now()->toDateString(),
        'maintenance_manager_id' => null,
    ];


    $this->patchToTenant('api.buildings.update', $formData, $location->reference_code);

    assertDatabaseCount('scheduled_notifications', 1);
    assertDatabaseMissing(
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
})->with(array_values(array_diff(array_column(MaintenanceFrequency::cases(), 'value'), ['on demand'])));

it('deletes only pending notification when removing maintenance_manager from existing asset', function ($frequency) {

    $formData = [
        ...$this->basicLocationData,
        'maintenance_frequency' => $frequency,
        'need_maintenance' => true,
        'last_maintenance_date' => Carbon::now()->toDateString(),
        'maintenance_manager_id' => $this->manager->id,
    ];

    $this->postToTenant('api.buildings.store', $formData);

    $location = Building::first();

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

    $notification = $location->notifications()->create([
        'recipient_name' => $this->manager->fullName,
        'recipient_email' => $this->manager->email,
        'notification_type' => 'next_maintenance_date',
        'scheduled_at' => Carbon::now()->subDays(MaintenanceFrequency::from($frequency)->days())->toDateString(),
        'status' => 'sent',
    ]);

    $notification->user()->associate($this->manager)->save();

    $formData = [
        ...$this->basicLocationData,
        'maintenance_frequency' => $frequency,
        'need_maintenance' => true,
        'last_maintenance_date' => Carbon::now()->toDateString(),
        'maintenance_manager_id' => null,
    ];


    $this->patchToTenant('api.buildings.update', $formData, $location->reference_code);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'status' => 'sent',
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->subDays(MaintenanceFrequency::from($frequency)->days())->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );

    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'status' => 'pending',
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addDays(MaintenanceFrequency::from($frequency)->days())->subDays(7)->toDateString(),
            'notifiable_type' => get_class($location),
            'notifiable_id' => $location->id,
        ]
    );
})->with(array_values(array_diff(array_column(MaintenanceFrequency::cases(), 'value'), ['on demand'])));
