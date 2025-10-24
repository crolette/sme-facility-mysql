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

use App\Models\Tenants\Provider;
use App\Enums\MaintenanceFrequency;
use App\Models\Central\CategoryType;

use function PHPUnit\Framework\assertCount;
use function Pest\Laravel\assertDatabaseHas;
use function PHPUnit\Framework\assertEquals;
use App\Models\Tenants\ScheduledNotification;
use function PHPUnit\Framework\assertNotNull;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseMissing;

beforeEach(function () {

    $this->admin = User::factory()->withRole('Admin')->create();
    $this->actingAs($this->admin, 'tenant');

    $this->manager = User::factory()->withRole('Maintenance Manager')->create();


    $this->categoryType = CategoryType::factory()->create(['category' => 'asset']);

    $this->site = Site::factory()->create();
    Building::factory()->create();
    Floor::factory()->create();
    $this->provider = Provider::factory()->create();

    $this->room = Room::factory()->create();

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

it('creates next_maintenance_date notification (for admin & manager) based on frequency when a new asset is created with next_maintenance_date defined', function ($frequency) {

    $formData = [
        ...$this->basicAssetData,
        'maintenance_manager_id' => $this->manager->id,
        'maintenance_frequency' => $frequency,
        'need_maintenance' => true,
        'next_maintenance_date' => Carbon::now()->addDays(MaintenanceFrequency::from($frequency)->days())->toDateString(),
        'last_maintenance_date' => Carbon::now()->toDateString(),
    ];

    $response =  $this->postToTenant('api.assets.store', $formData);
    $response->assertSessionHasNoErrors();

    assertDatabaseCount('scheduled_notifications', 2);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addDays(MaintenanceFrequency::from($frequency)->days())->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => 1,
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
            'notifiable_id' => 1,
        ]
    );
})->with(array_values(array_diff(array_column(MaintenanceFrequency::cases(), 'value'), ['on demand'])));

it('creates next_maintenance_date notification when next_maintenance_date is not defined and last_maintenance_date is defined', function ($frequency) {

    $formData = [
        ...$this->basicAssetData,
        'maintenance_manager_id' => $this->manager->id,
        'maintenance_frequency' => $frequency,
        'need_maintenance' => true,
        'last_maintenance_date' => Carbon::now()->subMonths(4)->toDateString()
    ];

    $response =  $this->postToTenant('api.assets.store', $formData);
    $response->assertSessionHasNoErrors();

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
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => 1,
        ]
    );

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => $expectedDate,
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => 1,
        ]
    );
})->with(array_values(array_diff(array_column(MaintenanceFrequency::cases(), 'value'), ['on demand'])));

it('creates next_maintenance_date notification when next/last_maintenance_date are not defined ', function ($frequency) {

    $formData = [
        ...$this->basicAssetData,
        'maintenance_manager_id' => $this->manager->id,
        'maintenance_frequency' => $frequency,
        'need_maintenance' => true,
    ];

    $response =  $this->postToTenant('api.assets.store', $formData);
    $response->assertSessionHasNoErrors();

    assertDatabaseCount('scheduled_notifications', 2);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->subDays(7)->addDays(MaintenanceFrequency::from($frequency)->days())->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => 1,
        ]
    );

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->subDays(7)->addDays(MaintenanceFrequency::from($frequency)->days())->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => 1,
        ]
    );
})->with(array_values(array_diff(array_column(MaintenanceFrequency::cases(), 'value'), ['on demand'])));

it('updates next_maintenance_date notification when updating next_maintenance_date of the asset', function () {

    $asset = Asset::factory()->forLocation($this->room)->create();

    $formData = [
        ...$this->basicAssetData,
        'maintenance_manager_id' => $this->manager->id,
        'maintenance_frequency' => 'annual',
        'need_maintenance' => true,
        'next_maintenance_date' => Carbon::now()->addMonth(),
        'last_maintenance_date' => Carbon::now()->toDateString(),
    ];

    $response = $this->patchToTenant('api.assets.update', $formData, $asset->reference_code);

    assertDatabaseCount('scheduled_notifications', 2);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addMonth()->subDays(7)->toDateString(),
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
            'scheduled_at' => Carbon::now()->addMonth()->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => $asset->id,
        ]
    );

    $newformData = [
        ...$this->basicAssetData,
        'maintenance_manager_id' => $this->manager->id,
        'maintenance_frequency' => 'annual',
        'need_maintenance' => true,
        'next_maintenance_date' => Carbon::now()->addYear(),
        'last_maintenance_date' => Carbon::now()->toDateString(),
    ];

    $asset->refresh();
    $response = $this->patchToTenant('api.assets.update', $newformData, $asset->reference_code);
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
            'scheduled_at' => Carbon::now()->addYear()->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => $asset->id,
        ]
    );
});

it('does not create a next_maintenance_date notification when next_maintenance_date is today', function () {

    $formData = [
        ...$this->basicAssetData,
        'maintenance_manager_id' => $this->manager->id,
        'maintenance_frequency' => 'annual',
        'need_maintenance' => true,
        'next_maintenance_date' => Carbon::now(),
        'last_maintenance_date' => Carbon::now()->subDays(120),
    ];

    $this->postToTenant('api.assets.store', $formData);

    assertDatabaseCount('scheduled_notifications', 0);
});

it('creates a notification even if the scheduled_at is in the past', function () {

    $formData = [
        ...$this->basicAssetData,
        'maintenance_manager_id' => $this->manager->id,
        'maintenance_frequency' => 'annual',
        'need_maintenance' => true,
        'next_maintenance_date' => Carbon::tomorrow(),
        'last_maintenance_date' => Carbon::now()->subDays(120),
    ];

    $this->postToTenant('api.assets.store', $formData);

    $asset = Asset::first();
    assertDatabaseCount('scheduled_notifications', 2);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::tomorrow()->subDays(7)->toDateString(),
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
            'scheduled_at' => Carbon::tomorrow()->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => $asset->id,
        ]
    );
});

it('updates notification when updating next_maintenance_date of the asset and scheduled_at will be in the past', function () {

    $asset = Asset::factory()->forLocation($this->room)->create();

    $formData = [
        ...$this->basicAssetData,
        'maintenance_manager_id' => $this->manager->id,
        'maintenance_frequency' => 'annual',
        'need_maintenance' => true,
        'next_maintenance_date' => Carbon::now()->addMonth(),
        'last_maintenance_date' => Carbon::now()->toDateString(),
    ];

    $response = $this->patchToTenant('api.assets.update', $formData, $asset->reference_code);

    $newformData = [
        ...$this->basicAssetData,
        'maintenance_manager_id' => $this->manager->id,
        'maintenance_frequency' => 'annual',
        'need_maintenance' => true,
        'next_maintenance_date' => Carbon::tomorrow(),
        'last_maintenance_date' => Carbon::now()->toDateString(),
    ];

    $asset->refresh();
    $response = $this->patchToTenant('api.assets.update', $newformData, $asset->reference_code);
    $response->assertStatus(200);
    $response->assertSessionHasNoErrors();
    assertDatabaseCount('scheduled_notifications', 2);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::tomorrow()->subDays(7)->toDateString(),
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
            'scheduled_at' => Carbon::tomorrow()->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => $asset->id,
        ]
    );
});

it('creates notification when the scheduled_at notification was previously in the past', function () {

    $asset = Asset::factory()->forLocation($this->room)->create();

    $formData = [
        ...$this->basicAssetData,
        'maintenance_manager_id' => $this->manager->id,
        'maintenance_frequency' => 'annual',
        'need_maintenance' => true,
        'next_maintenance_date' => Carbon::now(),
        'last_maintenance_date' => Carbon::now()->toDateString(),
    ];

    $this->patchToTenant('api.assets.update', $formData, $asset->reference_code);

    assertDatabaseCount('scheduled_notifications', 0);

    $newformData = [
        ...$this->basicAssetData,
        'maintenance_manager_id' => $this->manager->id,
        'maintenance_frequency' => 'annual',
        'need_maintenance' => true,
        'next_maintenance_date' => Carbon::now()->addMonth(),
        'last_maintenance_date' => Carbon::now()->toDateString(),
    ];

    $asset->refresh();
    $response = $this->patchToTenant('api.assets.update', $newformData, $asset->reference_code);
    $response->assertSessionHasNoErrors();

    assertDatabaseCount('scheduled_notifications', 2);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addMonth()->subDays(7)->toDateString(),
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
            'scheduled_at' => Carbon::now()->addMonth()->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => $asset->id,
        ]
    );
});

it('creates notification when need_maintenance passes from false to true', function ($frequency) {

    $asset = Asset::factory()->forLocation($this->room)->create();

    assertDatabaseCount('scheduled_notifications', 0);

    $formData = [
        ...$this->basicAssetData,
        'maintenance_frequency' => $frequency,
        'need_maintenance' => true,
        'next_maintenance_date' => Carbon::now()->addDays(MaintenanceFrequency::from($frequency)->days())->toDateString(),
        'last_maintenance_date' => Carbon::now()->toDateString(),
    ];
    $asset = Asset::find(1);
    $response = $this->patchToTenant('api.assets.update', $formData, $asset->reference_code);

    $response->assertSessionHasNoErrors();
    $response->assertStatus(200);

    assertDatabaseCount('scheduled_notifications', 1);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addDays(MaintenanceFrequency::from($frequency)->days())->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => 1,
        ]
    );
})->with(array_values(array_diff(array_column(MaintenanceFrequency::cases(), 'value'), ['on demand'])));

it('deletes notification when need_maintenance passes from true to false', function () {

    $formData = [
        ...$this->basicAssetData,
        'maintenance_frequency' => 'annual',
        'need_maintenance' => true,
        'next_maintenance_date' => Carbon::now()->addYear(),
        'last_maintenance_date' => Carbon::now()->toDateString(),
    ];

    $response = $this->postToTenant('api.assets.store', $formData);
    $response->assertSessionHasNoErrors();
    $response->assertStatus(200);

    assertDatabaseCount('scheduled_notifications', 1);

    $asset = Asset::find(1);

    $formData = [
        ...$this->basicAssetData,
        'maintenance_manager_id' => $this->manager->id,
        'need_maintenance' => false,
    ];

    $response = $this->patchToTenant('api.assets.update', $formData, $asset->reference_code);
    $response->assertSessionHasNoErrors();
    $response->assertStatus(200);

    assertDatabaseCount('scheduled_notifications', 0);
});

it('update notifications when notification preference next_maintenance_date of user changes', function ($frequency) {

    $formData = [
        ...$this->basicAssetData,
        'maintenance_frequency' => $frequency,
        'need_maintenance' => true,
        'next_maintenance_date' => Carbon::now()->addDays(MaintenanceFrequency::from($frequency)->days())->toDateString(),
        'last_maintenance_date' => Carbon::now()->toDateString(),

    ];

    $response = $this->postToTenant('api.assets.store', $formData);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addDays(MaintenanceFrequency::from($frequency)->days())->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Asset',
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
            'scheduled_at' => Carbon::now()->addDays(MaintenanceFrequency::from($frequency)->days())->subDays(1)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => 1,
        ]
    );
})->with(array_values(array_diff(array_column(MaintenanceFrequency::cases(), 'value'), ['on demand'])));

it('deletes notifications when notification preference next_maintenance_date of user changes from enabled to disabled', function () {

    $formData = [
        ...$this->basicAssetData,
        'maintenance_manager_id' => $this->manager->id,
        'maintenance_frequency' => 'annual',
        'need_maintenance' => true,
        'next_maintenance_date' => Carbon::now()->addYear(),
        'last_maintenance_date' => Carbon::now()->toDateString(),

    ];

    $response = $this->postToTenant('api.assets.store', $formData);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addYear()->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Asset',
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
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => 1,
        ]
    );
});

it('creates notifications when notification preference next_maintenance_date of user changes from disabled to enabled', function ($frequency) {

    $formData = [
        ...$this->basicAssetData,
        'maintenance_frequency' => $frequency,
        'need_maintenance' => true,
        'next_maintenance_date' => Carbon::now()->addDays(MaintenanceFrequency::from($frequency)->days())->toDateString(),
        'last_maintenance_date' => Carbon::now()->toDateString(),
    ];

    $response = $this->postToTenant('api.assets.store', $formData);

    assertDatabaseCount('scheduled_notifications', 1);

    $preference = $this->admin->notification_preferences()->where('notification_type', 'next_maintenance_date')->first();

    $formData = [
        'asset_type' => 'maintenance',
        'notification_type' => 'next_maintenance_date',
        'notification_delay_days' => $preference->notification_delay_days,
        'enabled' => false,
    ];

    $response = $this->patchToTenant('api.notifications.update', $formData, $preference->id);
    $response->assertStatus(200);
    assertDatabaseCount('scheduled_notifications', 0);

    $formData = [
        'asset_type' => 'maintenance',
        'notification_type' => 'next_maintenance_date',
        'notification_delay_days' => $preference->notification_delay_days,
        'enabled' => true,
    ];

    $response = $this->patchToTenant('api.notifications.update', $formData, $preference->id);
    $response->assertStatus(200);

    assertDatabaseCount('scheduled_notifications', 1);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addDays(MaintenanceFrequency::from($frequency)->days())->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => 1,
        ]
    );
})->with(array_values(array_diff(array_column(MaintenanceFrequency::cases(), 'value'), ['on demand'])));


it('updates notification when maintenance is marked as done and notification is not sent', function ($frequency) {

    $formData = [
        ...$this->basicAssetData,
        'maintenance_manager_id' => $this->manager->id,
        'maintenance_frequency' => $frequency,
        'need_maintenance' => true,
        'last_maintenance_date' => Carbon::now()->subDays(18)->toDateString(),
    ];

    $response =  $this->postToTenant('api.assets.store', $formData);
    $response->assertSessionHasNoErrors();
    assertDatabaseCount('scheduled_notifications', 2);

    $asset = Asset::first();

    $response = $this->patchToTenant('api.maintenance.done', [], $asset->maintainable);
    $response->assertSessionHasNoErrors();

    assertDatabaseCount('scheduled_notifications', 2);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'next_maintenance_date',
            'status' => 'pending',
            'scheduled_at' => Carbon::now()->addDays(MaintenanceFrequency::from($frequency)->days())->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => 1,
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
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => 1,
        ]
    );
})->with(array_values(array_diff(array_column(MaintenanceFrequency::cases(), 'value'), ['on demand'])));


it('creates notification when maintenance is marked as done and other notifications already sent', function ($frequency) {

    $formData = [
        ...$this->basicAssetData,
        'maintenance_manager_id' => $this->manager->id,
        'maintenance_frequency' => $frequency,
        'need_maintenance' => true,
        // 'last_maintenance_date' => Carbon::now()->subDays(18)->toDateString(),
    ];

    $response =  $this->postToTenant('api.assets.store', $formData);
    $response->assertSessionHasNoErrors();
    assertDatabaseCount('scheduled_notifications', 2);

    $asset = Asset::first();

    ScheduledNotification::updateOrCreate(
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'next_maintenance_date',
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => 1,
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
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => 1,
            'status' => 'pending'
        ],
        [
            'status' => 'sent'
        ]
    );

    $response = $this->patchToTenant('api.maintenance.done', [], $asset->maintainable);
    $response->assertSessionHasNoErrors();

    assertDatabaseCount('scheduled_notifications', 4);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'next_maintenance_date',
            'status' => 'sent',
            'scheduled_at' => Carbon::now()->addDays(MaintenanceFrequency::from($frequency)->days())->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => 1,
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
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => 1,
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
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => 1,
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
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => 1,
        ]
    );
})->with(array_values(array_diff(array_column(MaintenanceFrequency::cases(), 'value'), ['on demand'])));

it('creates notification when next_maintenance_date of ONDEMAND is given', function () {
    $formData = [
        ...$this->basicAssetData,
        'maintenance_manager_id' => $this->manager->id,
        'maintenance_frequency' => 'on demand',
        'need_maintenance' => true,
        'next_maintenance_date' => Carbon::now()->addDays(14)->toDateString(),
        'last_maintenance_date' => Carbon::now()->toDateString(),
    ];

    $response =  $this->postToTenant('api.assets.store', $formData);
    $response->assertSessionHasNoErrors();

    assertDatabaseCount('scheduled_notifications', 2);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addDays(14)->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => 1,
        ]
    );

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addDays(14)->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => 1,
        ]
    );
});

it('updates notification when next_maintenance_date of ONDEMAND is changed', function () {
    $formData = [
        ...$this->basicAssetData,
        'maintenance_manager_id' => $this->manager->id,
        'maintenance_frequency' => 'on demand',
        'need_maintenance' => true,
        'next_maintenance_date' => Carbon::now()->addDays(14)->toDateString(),
        'last_maintenance_date' => Carbon::now()->toDateString(),
    ];

    $this->postToTenant('api.assets.store', $formData);
    $asset = Asset::first();
    assertDatabaseCount('scheduled_notifications', 2);

    $formData = [
        ...$this->basicAssetData,
        'maintenance_manager_id' => $this->manager->id,
        'maintenance_frequency' => 'on demand',
        'need_maintenance' => true,
        'next_maintenance_date' => Carbon::now()->addMonth(),
        'last_maintenance_date' => Carbon::now()->toDateString(),
    ];

    $response = $this->patchToTenant('api.assets.update', $formData, $asset->reference_code);
    $response->assertSessionHasNoErrors();

    assertDatabaseCount('scheduled_notifications', 2);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addMonth()->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => 1,
        ]
    );

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addMonth()->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => 1,
        ]
    );
});

it('creates notification when maintenance_frequency changes from ONDEMAND to another one', function ($frequency) {

    $formData = [
        ...$this->basicAssetData,
        'maintenance_manager_id' => $this->manager->id,
        'maintenance_frequency' => 'on demand',
        'need_maintenance' => true,
        'last_maintenance_date' => Carbon::now()->toDateString(),
    ];

    $response =  $this->postToTenant('api.assets.store', $formData);
    $response->assertSessionHasNoErrors();

    $asset = Asset::first();

    assertDatabaseCount('scheduled_notifications', 0);

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
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addDays(MaintenanceFrequency::from($frequency)->days())->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => 1,
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
            'notifiable_id' => 1,
        ]
    );
})->with(array_values(array_diff(array_column(MaintenanceFrequency::cases(), 'value'), ['on demand'])));
