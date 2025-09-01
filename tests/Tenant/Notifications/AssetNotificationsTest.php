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

    LocationType::factory()->create(['level' => 'site']);
    LocationType::factory()->create(['level' => 'building']);
    LocationType::factory()->create(['level' => 'floor']);
    LocationType::factory()->create(['level' => 'room']);
    CategoryType::factory()->create(['category' => 'provider']);
    $this->categoryType = CategoryType::factory()->create(['category' => 'asset']);

    $this->site = Site::factory()->create();
    Building::factory()->create();
    Floor::factory()->create();
    $this->provider = Provider::factory()->create();

    $this->room = Room::factory()
        ->for(LocationType::where('level', 'room')->first())
        ->for(Floor::first())
        ->create();

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

// it('creates end of warranty notification for a new created asset', function () {

//     $formData = [
//         ...$this->basicAssetData,
//         'under_warranty' => true,
//         'end_warranty_date' => Carbon::now()->addMonths(10),
//         'maintenance_manager_id' => $this->manager->id,
//     ];

//     $response = $this->postToTenant('api.assets.store', $formData);
//     $response->assertSessionHasNoErrors();

//     assertDatabaseCount('scheduled_notifications', 2);
//     assertDatabaseHas(
//         'scheduled_notifications',
//         [
//             'recipient_name' => $this->admin->fullName,
//             'recipient_email' => $this->admin->email,
//             'notification_type' => 'end_warranty_date',
//             'scheduled_at' => Carbon::now()->addMonth(10)->subDays(7)->toDateString(),
//             'notifiable_type' => 'App\Models\Tenants\Asset',
//             'notifiable_id' => 1,
//         ]
//     );

//     assertDatabaseHas(
//         'scheduled_notifications',
//         [
//             'recipient_name' => $this->manager->fullName,
//             'recipient_email' => $this->manager->email,
//             'notification_type' => 'end_warranty_date',
//             'scheduled_at' => Carbon::now()->addMonth(10)->subDays(7)->toDateString(),
//             'notifiable_type' => 'App\Models\Tenants\Asset',
//             'notifiable_id' => 1,
//         ]
//     );
// });

// it('creates depreciation notification for a new created asset', function () {

//     $formData = [
//         ...$this->basicAssetData,
//         'maintenance_manager_id' => $this->manager->id,
//         'depreciable' => true,
//         'depreciation_start_date' => Carbon::now()->toDateString(),
//         'depreciation_end_date' => Carbon::now()->addYear(3)->toDateString(),
//         'depreciation_duration' => 3,
//         'residual_value' => 1250.69,
//     ];

//     $this->postToTenant('api.assets.store', $formData);

//     assertDatabaseCount('scheduled_notifications', 2);
//     assertDatabaseHas(
//         'scheduled_notifications',
//         [
//             'recipient_name' => $this->admin->fullName,
//             'recipient_email' => $this->admin->email,
//             'notification_type' => 'depreciation_end_date',
//             'scheduled_at' => Carbon::now()->addYear(3)->subDays(7)->toDateString(),
//             'notifiable_type' => 'App\Models\Tenants\Asset',
//             'notifiable_id' => 1,
//         ]
//     );

//     assertDatabaseHas(
//         'scheduled_notifications',
//         [
//             'recipient_name' => $this->manager->fullName,
//             'recipient_email' => $this->manager->email,
//             'notification_type' => 'depreciation_end_date',
//             'scheduled_at' => Carbon::now()->addYear(3)->subDays(7)->toDateString(),
//             'notifiable_type' => 'App\Models\Tenants\Asset',
//             'notifiable_id' => 1,
//         ]
//     );
// });

// it('creates maintenance notification for a new created asset', function () {

//     $formData = [
//         ...$this->basicAssetData,
//         'maintenance_manager_id' => $this->manager->id,
//         'maintenance_frequency' => 'annual',
//         'need_maintenance' => true,
//         'next_maintenance_date' => Carbon::now()->addYear(),
//         'last_maintenance_date' => Carbon::now()->toDateString(),
//     ];

//     $response =  $this->postToTenant('api.assets.store', $formData);
//     $response->assertSessionHasNoErrors();

//     assertDatabaseCount('scheduled_notifications', 2);
//     assertDatabaseHas(
//         'scheduled_notifications',
//         [
//             'recipient_name' => $this->admin->fullName,
//             'recipient_email' => $this->admin->email,
//             'notification_type' => 'next_maintenance_date',
//             'scheduled_at' => Carbon::now()->addYear()->subDays(7)->toDateString(),
//             'notifiable_type' => 'App\Models\Tenants\Asset',
//             'notifiable_id' => 1,
//         ]
//     );

//     assertDatabaseHas(
//         'scheduled_notifications',
//         [
//             'recipient_name' => $this->manager->fullName,
//             'recipient_email' => $this->manager->email,
//             'notification_type' => 'next_maintenance_date',
//             'scheduled_at' => Carbon::now()->addYear()->subDays(7)->toDateString(),
//             'notifiable_type' => 'App\Models\Tenants\Asset',
//             'notifiable_id' => 1,
//         ]
//     );
// });

// it('update notifications when notification preference end_warranty_date of user changes', function () {

//     $formData = [
//         ...$this->basicAssetData,
//         'under_warranty' => true,
//         'end_warranty_date' => Carbon::now()->addMonths(10),
//         'maintenance_manager_id' => $this->manager->id,
//     ];

//     $this->postToTenant('api.assets.store', $formData);

//     assertDatabaseCount('scheduled_notifications', 2);
//     assertDatabaseHas(
//         'scheduled_notifications',
//         [
//             'recipient_name' => $this->admin->fullName,
//             'recipient_email' => $this->admin->email,
//             'notification_type' => 'end_warranty_date',
//             'scheduled_at' => Carbon::now()->addMonth(10)->subDays(7)->toDateString(),
//             'notifiable_type' => 'App\Models\Tenants\Asset',
//             'notifiable_id' => 1,
//         ]
//     );

//     $preference = $this->admin->notification_preferences()->where('notification_type', 'end_warranty_date')->first();

//     $formData = [
//         'asset_type' => 'maintenance',
//         'notification_type' => 'end_warranty_date',
//         'notification_delay_days' => 1,
//         'enabled' => true,
//     ];

//     $response = $this->patchToTenant('api.notifications.update', $formData, $preference->id);
//     $response->assertStatus(200);

//     assertDatabaseHas(
//         'scheduled_notifications',
//         [
//             'recipient_name' => $this->admin->fullName,
//             'recipient_email' => $this->admin->email,
//             'notification_type' => 'end_warranty_date',
//             'scheduled_at' => Carbon::now()->addMonth(10)->subDays(1)->toDateString(),
//             'notifiable_type' => 'App\Models\Tenants\Asset',
//             'notifiable_id' => 1,
//         ]
//     );
// });

// it('update notifications when notification preference depreciation_end_date of user changes', function () {

//     $formData = [
//         ...$this->basicAssetData,
//         'maintenance_manager_id' => $this->manager->id,
//         'depreciable' => true,
//         'depreciation_start_date' => Carbon::now()->toDateString(),
//         'depreciation_end_date' => Carbon::now()->addYears(3)->toDateString(),
//         'depreciation_duration' => 3,
//         'residual_value' => 1250.69,

//     ];

//     $response = $this->postToTenant('api.assets.store', $formData);
//     assertDatabaseCount('scheduled_notifications', 2);
//     assertDatabaseHas(
//         'scheduled_notifications',
//         [
//             'recipient_name' => $this->admin->fullName,
//             'recipient_email' => $this->admin->email,
//             'notification_type' => 'depreciation_end_date',
//             'scheduled_at' => Carbon::now()->addYears(3)->subDays(7)->toDateString(),
//             'notifiable_type' => 'App\Models\Tenants\Asset',
//             'notifiable_id' => 1,
//         ]
//     );

//     $preference = $this->admin->notification_preferences()->where('notification_type', 'depreciation_end_date')->first();

//     $formData = [
//         'asset_type' => 'asset',
//         'notification_type' => 'depreciation_end_date',
//         'notification_delay_days' => 1,
//         'enabled' => true,
//     ];

//     $response = $this->patchToTenant('api.notifications.update', $formData, $preference->id);
//     $response->assertStatus(200);

//     assertDatabaseHas(
//         'scheduled_notifications',
//         [
//             'recipient_name' => $this->admin->fullName,
//             'recipient_email' => $this->admin->email,
//             'notification_type' => 'depreciation_end_date',
//             'scheduled_at' => Carbon::now()->addYears(3)->subDays(1)->toDateString(),
//             'notifiable_type' => 'App\Models\Tenants\Asset',
//             'notifiable_id' => 1,
//         ]
//     );
// });

// it('update notifications when notification preference next_maintenance_date of user changes', function () {


//     $formData = [
//         ...$this->basicAssetData,
//         'maintenance_manager_id' => $this->manager->id,
//         'maintenance_frequency' => 'annual',
//         'need_maintenance' => true,
//         'next_maintenance_date' => Carbon::now()->addYear(),
//         'last_maintenance_date' => Carbon::now()->toDateString(),

//     ];

//     $response = $this->postToTenant('api.assets.store', $formData);

//     assertDatabaseHas(
//         'scheduled_notifications',
//         [
//             'recipient_name' => $this->admin->fullName,
//             'recipient_email' => $this->admin->email,
//             'notification_type' => 'next_maintenance_date',
//             'scheduled_at' => Carbon::now()->addYear()->subDays(7)->toDateString(),
//             'notifiable_type' => 'App\Models\Tenants\Asset',
//             'notifiable_id' => 1,
//         ]
//     );

//     $preference = $this->admin->notification_preferences()->where('notification_type', 'next_maintenance_date')->first();

//     $formData = [
//         'asset_type' => 'maintenance',
//         'notification_type' => 'next_maintenance_date',
//         'notification_delay_days' => 1,
//         'enabled' => true,
//     ];

//     $response = $this->patchToTenant('api.notifications.update', $formData, $preference->id);
//     $response->assertStatus(200);

//     assertDatabaseHas(
//         'scheduled_notifications',
//         [
//             'recipient_name' => $this->admin->fullName,
//             'recipient_email' => $this->admin->email,
//             'notification_type' => 'next_maintenance_date',
//             'scheduled_at' => Carbon::now()->addYear()->subDays(1)->toDateString(),
//             'notifiable_type' => 'App\Models\Tenants\Asset',
//             'notifiable_id' => 1,
//         ]
//     );
// });

// it('deletes notifications when notification preference end_warranty_date of user is disabled', function () {


//     $formData = [
//         ...$this->basicAssetData,
//         'under_warranty' => true,
//         'end_warranty_date' => Carbon::now()->addMonths(10),
//         'maintenance_manager_id' => $this->manager->id,
//     ];

//     $response = $this->postToTenant('api.assets.store', $formData);

//     assertDatabaseHas(
//         'scheduled_notifications',
//         [
//             'recipient_name' => $this->admin->fullName,
//             'recipient_email' => $this->admin->email,
//             'notification_type' => 'end_warranty_date',
//             'scheduled_at' => Carbon::now()->addMonth(10)->subDays(7)->toDateString(),
//             'notifiable_type' => 'App\Models\Tenants\Asset',
//             'notifiable_id' => 1,
//         ]
//     );

//     $preference = $this->admin->notification_preferences()->where('notification_type', 'end_warranty_date')->first();

//     $formData = [
//         'asset_type' => 'maintenance',
//         'notification_type' => 'end_warranty_date',
//         'notification_delay_days' => $preference->notification_delay_days,
//         'enabled' => false,
//     ];

//     $response = $this->patchToTenant('api.notifications.update', $formData, $preference->id);
//     $response->assertStatus(200);

//     assertDatabaseMissing(
//         'scheduled_notifications',
//         [
//             'recipient_name' => $this->admin->fullName,
//             'recipient_email' => $this->admin->email,
//             'notification_type' => 'end_warranty_date',
//             'scheduled_at' => Carbon::now()->addMonth(10)->subDays(7)->toDateString(),
//             'notifiable_type' => 'App\Models\Tenants\Asset',
//             'notifiable_id' => 1,
//         ]
//     );
// });

// it('deletes notifications when notification preference depreciation_end_date of user is disabled', function () {


//     $formData = [
//         ...$this->basicAssetData,
//         'maintenance_manager_id' => $this->manager->id,
//         'depreciable' => true,
//         'depreciation_start_date' => Carbon::now()->toDateString(),
//         'depreciation_end_date' => Carbon::now()->addYears(3)->toDateString(),
//         'depreciation_duration' => 3,
//         'residual_value' => 1250.69,

//     ];

//     $response = $this->postToTenant('api.assets.store', $formData);

//     assertDatabaseHas(
//         'scheduled_notifications',
//         [
//             'recipient_name' => $this->admin->fullName,
//             'recipient_email' => $this->admin->email,
//             'notification_type' => 'depreciation_end_date',
//             'scheduled_at' => Carbon::now()->addYears(3)->subDays(7)->toDateString(),
//             'notifiable_type' => 'App\Models\Tenants\Asset',
//             'notifiable_id' => 1,
//         ]
//     );

//     $preference = $this->admin->notification_preferences()->where('notification_type', 'depreciation_end_date')->first();

//     $formData = [
//         'asset_type' => 'asset',
//         'notification_type' => 'depreciation_end_date',
//         'notification_delay_days' => $preference->notification_delay_days,
//         'enabled' => false,
//     ];

//     $response = $this->patchToTenant('api.notifications.update', $formData, $preference->id);
//     $response->assertStatus(200);

//     assertDatabaseMissing(
//         'scheduled_notifications',
//         [
//             'recipient_name' => $this->admin->fullName,
//             'recipient_email' => $this->admin->email,
//             'notification_type' => 'depreciation_end_date',
//             'scheduled_at' => Carbon::now()->addYears(3)->subDays(7)->toDateString(),
//             'notifiable_type' => 'App\Models\Tenants\Asset',
//             'notifiable_id' => 1,
//         ]
//     );
// });

// it('deletes notifications when notification preference next_maintenance_date of user is disabled', function () {

//     $formData = [
//         ...$this->basicAssetData,
//         'maintenance_manager_id' => $this->manager->id,
//         'maintenance_frequency' => 'annual',
//         'need_maintenance' => true,
//         'next_maintenance_date' => Carbon::now()->addYear(),
//         'last_maintenance_date' => Carbon::now()->toDateString(),

//     ];

//     $response = $this->postToTenant('api.assets.store', $formData);

//     assertDatabaseHas(
//         'scheduled_notifications',
//         [
//             'recipient_name' => $this->admin->fullName,
//             'recipient_email' => $this->admin->email,
//             'notification_type' => 'next_maintenance_date',
//             'scheduled_at' => Carbon::now()->addYear()->subDays(7)->toDateString(),
//             'notifiable_type' => 'App\Models\Tenants\Asset',
//             'notifiable_id' => 1,
//         ]
//     );

//     $preference = $this->admin->notification_preferences()->where('notification_type', 'next_maintenance_date')->first();

//     $formData = [
//         'asset_type' => 'maintenance',
//         'notification_type' => 'next_maintenance_date',
//         'notification_delay_days' => $preference->notification_delay_days,
//         'enabled' => false,
//     ];

//     $response = $this->patchToTenant('api.notifications.update', $formData, $preference->id);
//     $response->assertStatus(200);

//     assertDatabaseMissing(
//         'scheduled_notifications',
//         [
//             'recipient_name' => $this->admin->fullName,
//             'recipient_email' => $this->admin->email,
//             'notification_type' => 'next_maintenance_date',
//             'scheduled_at' => Carbon::now()->addYear()->subDays(7)->toDateString(),
//             'notifiable_type' => 'App\Models\Tenants\Asset',
//             'notifiable_id' => 1,
//         ]
//     );
// });

// it('creates notifications when notification preference warranty_end_date of user is enabled', function () {

//     $formData = [
//         ...$this->basicAssetData,
//         'under_warranty' => true,
//         'end_warranty_date' => Carbon::now()->addMonths(10),
//         'maintenance_manager_id' => $this->manager->id,
//     ];;


//     $response = $this->postToTenant('api.assets.store', $formData);

//     $preference = $this->admin->notification_preferences()->where('notification_type', 'end_warranty_date')->first();

//     $formData = [
//         'asset_type' => 'maintenance',
//         'notification_type' => 'end_warranty_date',
//         'notification_delay_days' => $preference->notification_delay_days,
//         'enabled' => false,
//     ];

//     $response = $this->patchToTenant('api.notifications.update', $formData, $preference->id);
//     $response->assertStatus(200);


//     $formData = [
//         'asset_type' => 'maintenance',
//         'notification_type' => 'end_warranty_date',
//         'notification_delay_days' => $preference->notification_delay_days,
//         'enabled' => true,
//     ];

//     $response = $this->patchToTenant('api.notifications.update', $formData, $preference->id);
//     $response->assertStatus(200);

//     assertDatabaseHas(
//         'scheduled_notifications',
//         [
//             'recipient_name' => $this->admin->fullName,
//             'recipient_email' => $this->admin->email,
//             'notification_type' => 'end_warranty_date',
//             'scheduled_at' => Carbon::now()->addMonth(10)->subDays(7)->toDateString(),
//             'notifiable_type' => 'App\Models\Tenants\Asset',
//             'notifiable_id' => 1,
//         ]
//     );
// });

// it('creates notifications when notification preference depreciation_end_date of user is enabled', function () {


//     $formData = [
//         ...$this->basicAssetData,
//         'maintenance_manager_id' => $this->manager->id,
//         'depreciable' => true,
//         'depreciation_start_date' => Carbon::now()->toDateString(),
//         'depreciation_end_date' => Carbon::now()->addYears(3)->toDateString(),
//         'depreciation_duration' => 3,
//         'residual_value' => 1250.69,
//     ];


//     $response = $this->postToTenant('api.assets.store', $formData);


//     $preference = $this->admin->notification_preferences()->where('notification_type', 'depreciation_end_date')->first();

//     $formData = [
//         'asset_type' => 'asset',
//         'notification_type' => 'depreciation_end_date',
//         'notification_delay_days' => $preference->notification_delay_days,
//         'enabled' => false,
//     ];

//     $response = $this->patchToTenant('api.notifications.update', $formData, $preference->id);
//     $response->assertStatus(200);

//     $formData = [
//         'asset_type' => 'asset',
//         'notification_type' => 'depreciation_end_date',
//         'notification_delay_days' => $preference->notification_delay_days,
//         'enabled' => true,
//     ];

//     $response = $this->patchToTenant('api.notifications.update', $formData, $preference->id);
//     $response->assertStatus(200);

//     assertDatabaseHas(
//         'scheduled_notifications',
//         [
//             'recipient_name' => $this->admin->fullName,
//             'recipient_email' => $this->admin->email,
//             'notification_type' => 'depreciation_end_date',
//             'scheduled_at' => Carbon::now()->addYears(3)->subDays(7)->toDateString(),
//             'notifiable_type' => 'App\Models\Tenants\Asset',
//             'notifiable_id' => 1,
//         ]
//     );
// });

// it('creates notifications when notification preference next_maintenance_date of user is enabled', function () {

//     $formData = [
//         ...$this->basicAssetData,
//         'maintenance_manager_id' => $this->manager->id,
//         'maintenance_frequency' => 'annual',
//         'need_maintenance' => true,
//         'next_maintenance_date' => Carbon::now()->addYear(),
//         'last_maintenance_date' => Carbon::now()->toDateString(),
//     ];

//     $response = $this->postToTenant('api.assets.store', $formData);

//     $preference = $this->admin->notification_preferences()->where('notification_type', 'next_maintenance_date')->first();

//     $formData = [
//         'asset_type' => 'maintenance',
//         'notification_type' => 'next_maintenance_date',
//         'notification_delay_days' => $preference->notification_delay_days,
//         'enabled' => false,
//     ];

//     $response = $this->patchToTenant('api.notifications.update', $formData, $preference->id);
//     $response->assertStatus(200);

//     $formData = [
//         'asset_type' => 'maintenance',
//         'notification_type' => 'next_maintenance_date',
//         'notification_delay_days' => $preference->notification_delay_days,
//         'enabled' => true,
//     ];

//     $response = $this->patchToTenant('api.notifications.update', $formData, $preference->id);
//     $response->assertStatus(200);

//     assertDatabaseHas(
//         'scheduled_notifications',
//         [
//             'recipient_name' => $this->admin->fullName,
//             'recipient_email' => $this->admin->email,
//             'notification_type' => 'next_maintenance_date',
//             'scheduled_at' => Carbon::now()->addYear()->subDays(7)->toDateString(),
//             'notifiable_type' => 'App\Models\Tenants\Asset',
//             'notifiable_id' => 1,
//         ]
//     );
// });

// it('creates notification for new maintenance manager for the asset', function () {

//     $asset = Asset::factory()->forLocation($this->room)->create();

//     $formData = [
//         ...$this->basicAssetData,
//         'maintenance_manager_id' => $this->manager->id,
//         'maintenance_frequency' => 'annual',
//         'need_maintenance' => true,
//         'next_maintenance_date' => Carbon::now()->addYear(),
//         'last_maintenance_date' => Carbon::now()->toDateString(),
//     ];

//     $response = $this->patchToTenant('api.assets.update', $formData, $asset->reference_code);
//     $response->assertSessionHasNoErrors();
//     $response->assertStatus(200);

//     assertDatabaseHas(
//         'scheduled_notifications',
//         [
//             'recipient_name' => $this->manager->fullName,
//             'recipient_email' => $this->manager->email,
//             'notification_type' => 'next_maintenance_date',
//             'scheduled_at' => Carbon::now()->addYear()->subDays(7)->toDateString(),
//             'notifiable_type' => 'App\Models\Tenants\Asset',
//             'notifiable_id' => $asset->id,
//         ]
//     );
// });

it('updates notification when updating next_maintenance_date of the asset', function () {

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
    dump($this->admin->email, $this->manager->email);
    dump(ScheduledNotification::select('id', 'recipient_email', 'notification_type', 'scheduled_at')->get());
    assertDatabaseCount('scheduled_notifications', 2);



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
