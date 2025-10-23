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

it('creates depreciation notification for a new created asset', function () {

    $formData = [
        ...$this->basicAssetData,
        'maintenance_manager_id' => $this->manager->id,
        'depreciable' => true,
        'depreciation_start_date' => Carbon::now()->toDateString(),
        'depreciation_end_date' => Carbon::now()->addYear(3)->toDateString(),
        'depreciation_duration' => 3,
        'residual_value' => 1250.69,
    ];

    $this->postToTenant('api.assets.store', $formData);

    assertDatabaseCount('scheduled_notifications', 2);
    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'depreciation_end_date',
            'scheduled_at' => Carbon::now()->addYear(3)->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => 1,
        ]
    );

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'depreciation_end_date',
            'scheduled_at' => Carbon::now()->addYear(3)->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => 1,
        ]
    );
});

it('creates a notification scheduled_at is in the past', function () {

    $formData = [
        ...$this->basicAssetData,
        'maintenance_manager_id' => $this->manager->id,
        'depreciable' => true,
        'depreciation_start_date' => Carbon::now()->toDateString(),
        'depreciation_end_date' => Carbon::tomorrow()->toDateString(),
        'depreciation_duration' => 3,
        'residual_value' => 1250.69,
    ];

    $this->postToTenant('api.assets.store', $formData);

    assertDatabaseCount('scheduled_notifications', 2);
    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'depreciation_end_date',
            'scheduled_at' => Carbon::tomorrow()->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => 1,
        ]
    );

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'depreciation_end_date',
            'scheduled_at' => Carbon::tomorrow()->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => 1,
        ]
    );
});

it('creates no notification if depreciation_end_date is today or in the past', function () {

    $formData = [
        ...$this->basicAssetData,
        'maintenance_manager_id' => $this->manager->id,
        'depreciable' => true,
        'depreciation_start_date' => Carbon::now()->subYears(2)->toDateString(),
        'depreciation_end_date' => Carbon::now()->toDateString(),
        'depreciation_duration' => 3,
        'residual_value' => 1250.69,
    ];

    $this->postToTenant('api.assets.store', $formData);

    assertDatabaseCount('scheduled_notifications', 0);
});

it('creates depreciation notification when depreciables passes from false to true', function () {

    $formData = [
        ...$this->basicAssetData,
        'maintenance_manager_id' => $this->manager->id,
        'depreciable' => false,
    ];

    $this->postToTenant('api.assets.store', $formData);

    assertDatabaseCount('scheduled_notifications', 0);


    $formData = [
        ...$this->basicAssetData,
        'maintenance_manager_id' => $this->manager->id,
        'depreciable' => true,
        'depreciation_start_date' => Carbon::now()->toDateString(),
        'depreciation_end_date' => Carbon::now()->addYear(3)->toDateString(),
        'depreciation_duration' => 3,
        'residual_value' => 1250.69,
    ];

    $asset = Asset::find(1);
    $response = $this->patchToTenant('api.assets.update', $formData, $asset->reference_code);
    $response->assertSessionHasNoErrors();
    $response->assertStatus(200);

    $asset->refresh();

    assertDatabaseCount('scheduled_notifications', 2);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'depreciation_end_date',
            'scheduled_at' => Carbon::now()->addYear(3)->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => 1,
        ]
    );

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'depreciation_end_date',
            'scheduled_at' => Carbon::now()->addYear(3)->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => 1,
        ]
    );
});

it(
    'updates depreciation notification when depreciable_end_date changes',
    function () {
        $formData = [
            ...$this->basicAssetData,
            'maintenance_manager_id' => $this->manager->id,
            'depreciable' => true,
            'depreciation_start_date' => Carbon::now()->toDateString(),
            'depreciation_end_date' => Carbon::now()->addYear()->toDateString(),
            'depreciation_duration' => 3,
            'residual_value' => 1250.69,
        ];

        $this->postToTenant('api.assets.store', $formData);

        assertDatabaseCount('scheduled_notifications', 2);

        assertDatabaseHas(
            'scheduled_notifications',
            [
                'recipient_name' => $this->admin->fullName,
                'recipient_email' => $this->admin->email,
                'notification_type' => 'depreciation_end_date',
                'scheduled_at' => Carbon::now()->addYear()->subDays(7)->toDateString(),
                'notifiable_type' => 'App\Models\Tenants\Asset',
                'notifiable_id' => 1,
            ]
        );

        assertDatabaseHas(
            'scheduled_notifications',
            [
                'recipient_name' => $this->manager->fullName,
                'recipient_email' => $this->manager->email,
                'notification_type' => 'depreciation_end_date',
                'scheduled_at' => Carbon::now()->addYear()->subDays(7)->toDateString(),
                'notifiable_type' => 'App\Models\Tenants\Asset',
                'notifiable_id' => 1,
            ]
        );

        $formData = [
            ...$this->basicAssetData,
            'maintenance_manager_id' => $this->manager->id,
            'depreciable' => true,
            'depreciation_start_date' => Carbon::now()->toDateString(),
            'depreciation_end_date' => Carbon::now()->addYears(3)->toDateString(),
            'depreciation_duration' => 3,
            'residual_value' => 1250.69,
        ];

        $asset = Asset::find(1);
        $response = $this->patchToTenant('api.assets.update', $formData, $asset->reference_code);
        $response->assertSessionHasNoErrors();
        $response->assertStatus(200);

        assertDatabaseCount('scheduled_notifications', 2);

        assertDatabaseHas(
            'scheduled_notifications',
            [
                'recipient_name' => $this->admin->fullName,
                'recipient_email' => $this->admin->email,
                'notification_type' => 'depreciation_end_date',
                'scheduled_at' => Carbon::now()->addYears(3)->subDays(7)->toDateString(),
                'notifiable_type' => 'App\Models\Tenants\Asset',
                'notifiable_id' => 1,
            ]
        );

        assertDatabaseHas(
            'scheduled_notifications',
            [
                'recipient_name' => $this->manager->fullName,
                'recipient_email' => $this->manager->email,
                'notification_type' => 'depreciation_end_date',
                'scheduled_at' => Carbon::now()->addYears(3)->subDays(7)->toDateString(),
                'notifiable_type' => 'App\Models\Tenants\Asset',
                'notifiable_id' => 1,
            ]
        );
    }
);

it('deletes depreciation notification when depreciable passes from true to false', function () {

    $formData = [
        ...$this->basicAssetData,
        'maintenance_manager_id' => $this->manager->id,
        'depreciable' => true,
        'depreciation_start_date' => Carbon::now()->toDateString(),
        'depreciation_end_date' => Carbon::now()->addYear(3)->toDateString(),
        'depreciation_duration' => 3,
        'residual_value' => 1250.69,
    ];

    $this->postToTenant('api.assets.store', $formData);

    assertDatabaseCount('scheduled_notifications', 2);
    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'depreciation_end_date',
            'scheduled_at' => Carbon::now()->addYear(3)->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => 1,
        ]
    );

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'depreciation_end_date',
            'scheduled_at' => Carbon::now()->addYear(3)->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => 1,
        ]
    );

    $formData = [
        ...$this->basicAssetData,
        'maintenance_manager_id' => $this->manager->id,
        'depreciable' => false,
    ];

    $asset = Asset::find(1);
    $response = $this->patchToTenant('api.assets.update', $formData, $asset->reference_code);
    $response->assertSessionHasNoErrors();
    $response->assertStatus(200);

    assertDatabaseCount('scheduled_notifications', 0);
});

it('update notifications when notification preference depreciation_end_date of user changes', function () {

    $formData = [
        ...$this->basicAssetData,
        'maintenance_manager_id' => $this->manager->id,
        'depreciable' => true,
        'depreciation_start_date' => Carbon::now()->toDateString(),
        'depreciation_end_date' => Carbon::now()->addYears(3)->toDateString(),
        'depreciation_duration' => 3,
        'residual_value' => 1250.69,

    ];

    $response = $this->postToTenant('api.assets.store', $formData);
    assertDatabaseCount('scheduled_notifications', 2);
    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'depreciation_end_date',
            'scheduled_at' => Carbon::now()->addYears(3)->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => 1,
        ]
    );

    $preference = $this->admin->notification_preferences()->where('notification_type', 'depreciation_end_date')->first();

    $formData = [
        'asset_type' => 'asset',
        'notification_type' => 'depreciation_end_date',
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
            'notification_type' => 'depreciation_end_date',
            'scheduled_at' => Carbon::now()->addYears(3)->subDays(1)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => 1,
        ]
    );
});

it('deletes notifications when notification preference depreciation_end_date of user is disabled', function () {


    $formData = [
        ...$this->basicAssetData,
        'maintenance_manager_id' => $this->manager->id,
        'depreciable' => true,
        'depreciation_start_date' => Carbon::now()->toDateString(),
        'depreciation_end_date' => Carbon::now()->addYears(3)->toDateString(),
        'depreciation_duration' => 3,
        'residual_value' => 1250.69,

    ];

    $response = $this->postToTenant('api.assets.store', $formData);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'depreciation_end_date',
            'scheduled_at' => Carbon::now()->addYears(3)->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => 1,
        ]
    );

    $preference = $this->admin->notification_preferences()->where('notification_type', 'depreciation_end_date')->first();

    $formData = [
        'asset_type' => 'asset',
        'notification_type' => 'depreciation_end_date',
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
            'notification_type' => 'depreciation_end_date',
            'scheduled_at' => Carbon::now()->addYears(3)->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => 1,
        ]
    );
});

it('creates notifications when notification preference depreciation_end_date of user is enabled', function () {


    $formData = [
        ...$this->basicAssetData,
        'maintenance_manager_id' => $this->manager->id,
        'depreciable' => true,
        'depreciation_start_date' => Carbon::now()->toDateString(),
        'depreciation_end_date' => Carbon::now()->addYears(3)->toDateString(),
        'depreciation_duration' => 3,
        'residual_value' => 1250.69,
    ];


    $response = $this->postToTenant('api.assets.store', $formData);


    $preference = $this->admin->notification_preferences()->where('notification_type', 'depreciation_end_date')->first();

    $formData = [
        'asset_type' => 'asset',
        'notification_type' => 'depreciation_end_date',
        'notification_delay_days' => $preference->notification_delay_days,
        'enabled' => false,
    ];

    $response = $this->patchToTenant('api.notifications.update', $formData, $preference->id);
    $response->assertStatus(200);

    $formData = [
        'asset_type' => 'asset',
        'notification_type' => 'depreciation_end_date',
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
            'notification_type' => 'depreciation_end_date',
            'scheduled_at' => Carbon::now()->addYears(3)->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => 1,
        ]
    );
});

it('creates notification for maintenance manager when maintenance manager is added to an asset', function () {

    $formData = [
        ...$this->basicAssetData,
        'depreciable' => true,
        'depreciation_start_date' => Carbon::now()->toDateString(),
        'depreciation_end_date' => Carbon::now()->addYear(3)->toDateString(),
        'depreciation_duration' => 3,
        'residual_value' => 1250.69,
    ];

    $this->postToTenant('api.assets.store', $formData);

    assertDatabaseCount('scheduled_notifications', 1);

    $formData = [
        ...$this->basicAssetData,
        'maintenance_manager_id' => $this->manager->id,
        'depreciable' => true,
        'depreciation_start_date' => Carbon::now()->toDateString(),
        'depreciation_end_date' => Carbon::now()->addYear(3)->toDateString(),
        'depreciation_duration' => 3,
        'residual_value' => 1250.69,
    ];

    $asset = Asset::first();

    $response = $this->patchToTenant('api.assets.update', $formData, $asset->reference_code);

    $asset->refresh();

    assertDatabaseCount('scheduled_notifications', 2);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'depreciation_end_date',
            'scheduled_at' => Carbon::now()->addYear(3)->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => 1,
        ]
    );
});

it('removes notification when maintenance manager is removed from the asset', function () {

    $formData = [
        ...$this->basicAssetData,
        'maintenance_manager_id' => $this->manager->id,
        'depreciable' => true,
        'depreciation_start_date' => Carbon::now()->toDateString(),
        'depreciation_end_date' => Carbon::now()->addYear(3)->toDateString(),
        'depreciation_duration' => 3,
        'residual_value' => 1250.69,
    ];

    $this->postToTenant('api.assets.store', $formData);

    assertDatabaseCount('scheduled_notifications', 2);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'depreciation_end_date',
            'scheduled_at' => Carbon::now()->addYear(3)->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => 1,
        ]
    );

    $formData = [
        ...$this->basicAssetData,
        'depreciable' => true,
        'depreciation_start_date' => Carbon::now()->toDateString(),
        'depreciation_end_date' => Carbon::now()->addYear(3)->toDateString(),
        'depreciation_duration' => 3,
        'residual_value' => 1250.69,
    ];

    $asset = Asset::first();

    $response = $this->patchToTenant('api.assets.update', $formData, $asset->reference_code);

    assertDatabaseCount('scheduled_notifications', 1);

    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'depreciation_end_date',
            'scheduled_at' => Carbon::now()->addYear(3)->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => 1,
        ]
    );
});

it('removes old maintenance manager notification when new one is added', function () {

    $tempManager = User::factory()->withRole('Maintenance Manager')->create();

    $formData = [
        ...$this->basicAssetData,
        'maintenance_manager_id' => $tempManager->id,
        'depreciable' => true,
        'depreciation_start_date' => Carbon::now()->toDateString(),
        'depreciation_end_date' => Carbon::now()->addYear(3)->toDateString(),
        'depreciation_duration' => 3,
        'residual_value' => 1250.69,
    ];

    $this->postToTenant('api.assets.store', $formData);

    assertDatabaseCount('scheduled_notifications', 2);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $tempManager->fullName,
            'recipient_email' => $tempManager->email,
            'notification_type' => 'depreciation_end_date',
            'scheduled_at' => Carbon::now()->addYear(3)->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => 1,
        ]
    );

    $formData = [
        ...$this->basicAssetData,
        'maintenance_manager_id' => $this->manager->id,
        'depreciable' => true,
        'depreciation_start_date' => Carbon::now()->toDateString(),
        'depreciation_end_date' => Carbon::now()->addYear(3)->toDateString(),
        'depreciation_duration' => 3,
        'residual_value' => 1250.69,
    ];

    $asset = Asset::first();

    $response = $this->patchToTenant('api.assets.update', $formData, $asset->reference_code);

    assertDatabaseCount('scheduled_notifications', 2);


    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'depreciation_end_date',
            'scheduled_at' => Carbon::now()->addYear(3)->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => 1,
        ]
    );

    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'recipient_name' => $tempManager->fullName,
            'recipient_email' => $tempManager->email,
            'notification_type' => 'depreciation_end_date',
            'scheduled_at' => Carbon::now()->addYear(3)->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => 1,
        ]
    );
});
