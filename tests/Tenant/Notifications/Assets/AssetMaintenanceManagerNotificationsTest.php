<?php

use App\Enums\MaintenanceFrequency;
use Carbon\Carbon;
use App\Models\LocationType;
use App\Models\Tenants\Room;
use App\Models\Tenants\Site;
use App\Models\Tenants\User;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Floor;
use App\Enums\NoticePeriodEnum;
use App\Models\Tenants\Building;

use App\Models\Tenants\Provider;
use App\Models\Central\CategoryType;
use App\Models\Tenants\ScheduledNotification;

use function PHPUnit\Framework\assertCount;
use function Pest\Laravel\assertDatabaseHas;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertNotNull;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseMissing;

beforeEach(function () {

    $this->admin = User::factory()->withRole('Admin')->create();
    $this->actingAs($this->admin, 'tenant');

    $this->manager = User::factory()->withRole('Maintenance Manager')->create();

    LocationType::factory()->create(['level' => 'site']);
    LocationType::factory()->create(['level' => 'building']);
    LocationType::factory()->create(['level' => 'floor']);
    LocationType::factory()->create(['level' => 'room']);
    CategoryType::factory()->create(['category' => 'provider']);
    $this->categoryType = CategoryType::factory()->create(['category' => 'asset']);

    $this->site = Site::factory()->withMaintainableData()->create();
    Building::factory()->withMaintainableData()->create();
    Floor::factory()->withMaintainableData()->create();
    $this->provider = Provider::factory()->create();

    $this->room = Room::factory()->withMaintainableData()->create();

    $this->basicAssetData = [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'locationId' => $this->site->id,
        'locationType' => 'site',
        'locationReference' => $this->site->reference_code,
        'surface' => 12,
        'categoryId' => $this->categoryType->id,
    ];
});

it('creates notification when adding maintenance manager to existing asset without maintenance manager', function ($frequency) {

    $formData = [
        ...$this->basicAssetData,
        'maintenance_frequency' => $frequency,
        'need_maintenance' => true,
        'last_maintenance_date' => Carbon::now()->toDateString(),
    ];

    $response = $this->postToTenant('api.assets.store', $formData);
    $response->assertSessionHasNoErrors();
    $response->assertStatus(200);

    assertDatabaseCount('scheduled_notifications', 1);

    $asset = Asset::first();

    $formData = [
        ...$this->basicAssetData,
        'maintenance_manager_id' => $this->manager->id,
        'maintenance_frequency' => $frequency,
        'need_maintenance' => true,
        'last_maintenance_date' => Carbon::now()->toDateString(),
    ];

    $response = $this->patchToTenant('api.assets.update', $formData, $asset->reference_code);

    assertDatabaseCount('scheduled_notifications', 2);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addDays(MaintenanceFrequency::from($frequency)->days())->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => 1,
        ]
    );
})->with(array_values(array_diff(array_column(MaintenanceFrequency::cases(), 'value'), ['on_demand'])));

it('creates notification when replacing maintenance manager for the asset and removes notifications for old maintenance manager', function ($frequency) {

    $tempManager =  User::factory()->withRole('Maintenance Manager')->create();

    $formData = [
        ...$this->basicAssetData,
        'maintenance_manager_id' => $tempManager->id,
        'maintenance_frequency' => $frequency,
        'need_maintenance' => true,
        'last_maintenance_date' => Carbon::now()->toDateString(),
    ];

    $response = $this->postToTenant('api.assets.store', $formData);

    $asset = Asset::first();

    assertDatabaseCount('scheduled_notifications', 2);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $tempManager->fullName,
            'recipient_email' => $tempManager->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addDays(MaintenanceFrequency::from($frequency)->days())->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => $asset->id,
        ]
    );


    $formData = [
        ...$this->basicAssetData,
        'maintenance_manager_id' => $this->manager->id,
        'maintenance_frequency' => $frequency,
        'need_maintenance' => true,
        'last_maintenance_date' => Carbon::now()->toDateString(),
    ];

    $response = $this->patchToTenant('api.assets.update', $formData, $asset->reference_code);
    $response->assertSessionHasNoErrors();
    $response->assertStatus(200);

    assertDatabaseCount('scheduled_notifications', 2);

    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'recipient_name' => $tempManager->fullName,
            'recipient_email' => $tempManager->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addDays(MaintenanceFrequency::from($frequency)->days())->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => $asset->id,
        ]
    );

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addDays(MaintenanceFrequency::from($frequency)->days())->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => $asset->id,
        ]
    );
})->with(array_values(array_diff(array_column(MaintenanceFrequency::cases(), 'value'), ['on_demand'])));

it('deletes notification when removing maintenance_manager from existing asset', function ($frequency) {

    $formData = [
        ...$this->basicAssetData,
        'maintenance_frequency' => $frequency,
        'need_maintenance' => true,
        'last_maintenance_date' => Carbon::now()->toDateString(),
        'maintenance_manager_id' => $this->manager->id,
    ];

    $response = $this->postToTenant('api.assets.store', $formData);
    $response->assertSessionHasNoErrors();
    $response->assertStatus(200);

    assertDatabaseCount('scheduled_notifications', 2);
    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addDays(MaintenanceFrequency::from($frequency)->days())->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => 1,
        ]
    );

    $formData = [
        ...$this->basicAssetData,
        'maintenance_frequency' => $frequency,
        'need_maintenance' => true,
        'last_maintenance_date' => Carbon::now()->toDateString(),
        'maintenance_manager_id' => null,
    ];

    $asset = Asset::find(1);
    $response = $this->patchToTenant('api.assets.update', $formData, $asset->reference_code);

    $response->assertSessionHasNoErrors();
    $response->assertStatus(200);

    assertDatabaseCount('scheduled_notifications', 1);
    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addDays(MaintenanceFrequency::from($frequency)->days())->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => 1,
        ]
    );
})->with(array_values(array_diff(array_column(MaintenanceFrequency::cases(), 'value'), ['on_demand'])));

it('deletes only pending notification when removing maintenance_manager from existing asset', function ($frequency) {

    $formData = [
        ...$this->basicAssetData,
        'maintenance_frequency' => $frequency,
        'need_maintenance' => true,
        'last_maintenance_date' => Carbon::now()->toDateString(),
        'maintenance_manager_id' => $this->manager->id,
    ];

    $response = $this->postToTenant('api.assets.store', $formData);
    $response->assertSessionHasNoErrors();
    $response->assertStatus(200);

    assertDatabaseCount('scheduled_notifications', 2);
    $asset = Asset::find(1);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'next_maintenance_date',
            'status' => 'pending',
            'scheduled_at' => Carbon::now()->addDays(MaintenanceFrequency::from($frequency)->days())->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => 1,
        ]
    );

    $notification = $asset->notifications()->create([
        'recipient_name' => $this->manager->fullName,
        'recipient_email' => $this->manager->email,
        'notification_type' => 'next_maintenance_date',
        'scheduled_at' => Carbon::now()->subDays(MaintenanceFrequency::from($frequency)->days())->toDateString(),
        'status' => 'sent'
    ]);

    $notification->user()->associate($this->manager)->save();

    $formData = [
        ...$this->basicAssetData,
        'maintenance_frequency' => $frequency,
        'need_maintenance' => true,
        'last_maintenance_date' => Carbon::now()->toDateString(),
        'maintenance_manager_id' => null,
    ];


    $response = $this->patchToTenant('api.assets.update', $formData, $asset->reference_code);

    $response->assertSessionHasNoErrors();
    $response->assertStatus(200);

    assertDatabaseCount('scheduled_notifications', 2);
    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'status' => 'sent',
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->subDays(MaintenanceFrequency::from($frequency)->days())->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => 1,
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
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => 1,
        ]
    );
})->with(array_values(array_diff(array_column(MaintenanceFrequency::cases(), 'value'), ['on_demand'])));
