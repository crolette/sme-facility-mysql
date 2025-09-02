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

// // END OF WARRANTY
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

// it('updates end of warranty notification when end_warranty_date changes', function () {

//     $formData = [
//         ...$this->basicAssetData,
//         'under_warranty' => true,
//         'end_warranty_date' => Carbon::now()->addMonths(10),
//         'maintenance_manager_id' => $this->manager->id,
//     ];

//     $response = $this->postToTenant('api.assets.store', $formData);
//     $response->assertSessionHasNoErrors();

//     assertDatabaseCount('scheduled_notifications', 2);

//     $formData = [
//         ...$this->basicAssetData,
//         'under_warranty' => true,
//         'end_warranty_date' => Carbon::now()->addYears(2),
//         'maintenance_manager_id' => $this->manager->id,
//     ];

//     $asset = Asset::find(1);
//     $response = $this->patchToTenant('api.assets.update', $formData, $asset->reference_code);
//     $response->assertSessionHasNoErrors();
//     $response->assertStatus(200);

//     assertDatabaseCount('scheduled_notifications', 2);
//     assertDatabaseHas(
//         'scheduled_notifications',
//         [
//             'recipient_name' => $this->admin->fullName,
//             'recipient_email' => $this->admin->email,
//             'notification_type' => 'end_warranty_date',
//             'scheduled_at' => Carbon::now()->addYears(2)->subDays(7)->toDateString(),
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
//             'scheduled_at' => Carbon::now()->addYears(2)->subDays(7)->toDateString(),
//             'notifiable_type' => 'App\Models\Tenants\Asset',
//             'notifiable_id' => 1,
//         ]
//     );
// });

// it('creates warranty notifications when under_warranty passes from false to true', function () {

//     $formData = [
//         ...$this->basicAssetData,
//     ];

//     $response = $this->postToTenant('api.assets.store', $formData);
//     assertDatabaseCount('scheduled_notifications', 0);
//     $response->assertSessionHasNoErrors();
//     $response->assertStatus(200);

//     $formData = [
//         ...$this->basicAssetData,
//         'under_warranty' => true,
//         'end_warranty_date' => Carbon::now()->addMonths(10),
//     ];
//     $asset = Asset::find(1);
//     $response = $this->patchToTenant('api.assets.update', $formData, $asset->reference_code);

//     $response->assertSessionHasNoErrors();
//     $response->assertStatus(200);

//     assertDatabaseCount('scheduled_notifications', 1);
//     assertDatabaseHas(
//         'scheduled_notifications',
//         [
//             'recipient_name' => $this->admin->fullName,
//             'recipient_email' => $this->admin->email,
//             'notification_type' => 'end_warranty_date',
//             'scheduled_at' => Carbon::now()->addMonths(10)->subDays(7)->toDateString(),
//             'notifiable_type' => 'App\Models\Tenants\Asset',
//             'notifiable_id' => $asset->id,
//         ]
//     );
// });

// it('deletes warranty notifications when under_warranty passes from true to false', function () {

//     $formData = [
//         ...$this->basicAssetData,
//         'under_warranty' => true,
//         'end_warranty_date' => Carbon::now()->addMonths(10),
//     ];

//     $response = $this->postToTenant('api.assets.store', $formData);
//     $response->assertSessionHasNoErrors();
//     $response->assertStatus(200);
//     $asset = Asset::find(1);

//     assertDatabaseCount('scheduled_notifications', 1);
//     assertDatabaseHas(
//         'scheduled_notifications',
//         [
//             'recipient_name' => $this->admin->fullName,
//             'recipient_email' => $this->admin->email,
//             'notification_type' => 'end_warranty_date',
//             'scheduled_at' => Carbon::now()->addMonths(10)->subDays(7)->toDateString(),
//             'notifiable_type' => 'App\Models\Tenants\Asset',
//             'notifiable_id' => $asset->id,
//         ]
//     );

//     $formData = [
//         ...$this->basicAssetData,
//         'under_warranty' => false,
//     ];

//     $response = $this->patchToTenant('api.assets.update', $formData, $asset->reference_code);

//     $response->assertSessionHasNoErrors();
//     $response->assertStatus(200);

//     assertDatabaseCount('scheduled_notifications', 0);
// });

// // DEPRECIATION

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

it('deletes depreciation notification when depreciables passes from true to false', function () {

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

// // NEED MAINTENANCE

// it('creates next maintenance date notification for a new created asset', function () {

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


// it('updates notification when next_maintenance_date changes', function () {

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

//     $formData = [
//         ...$this->basicAssetData,
//         'maintenance_manager_id' => $this->manager->id,
//         'maintenance_frequency' => 'annual',
//         'need_maintenance' => true,
//         'next_maintenance_date' => Carbon::now()->addMonths(6),
//         'last_maintenance_date' => Carbon::now()->toDateString(),
//     ];


//     $asset = Asset::find(1);
//     $response = $this->patchToTenant('api.assets.update', $formData, $asset->reference_code);
//     $response->assertSessionHasNoErrors();
//     $response->assertStatus(200);

//     assertDatabaseCount('scheduled_notifications', 2);

//     assertDatabaseHas(
//         'scheduled_notifications',
//         [
//             'recipient_name' => $this->admin->fullName,
//             'recipient_email' => $this->admin->email,
//             'notification_type' => 'next_maintenance_date',
//             'scheduled_at' => Carbon::now()->addMonths(6)->subDays(7)->toDateString(),
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
//             'scheduled_at' => Carbon::now()->addMonths(6)->subDays(7)->toDateString(),
//             'notifiable_type' => 'App\Models\Tenants\Asset',
//             'notifiable_id' => 1,
//         ]
//     );
// });

// it('creates notification when need_maintenance passes from false to true', function () {

//     $formData = [
//         ...$this->basicAssetData,
//     ];

//     $response = $this->postToTenant('api.assets.store', $formData);
//     assertDatabaseCount('scheduled_notifications', 0);
//     $response->assertSessionHasNoErrors();
//     $response->assertStatus(200);

//     $formData = [
//         ...$this->basicAssetData,
//         'maintenance_frequency' => 'annual',
//         'need_maintenance' => true,
//         'next_maintenance_date' => Carbon::now()->addYear(),
//         'last_maintenance_date' => Carbon::now()->toDateString(),
//     ];
//     $asset = Asset::find(1);
//     $response = $this->patchToTenant('api.assets.update', $formData, $asset->reference_code);

//     $response->assertSessionHasNoErrors();
//     $response->assertStatus(200);

//     assertDatabaseCount('scheduled_notifications', 1);
// });

// it('deletes notification when need_maintenance passes from true to false', function () {

//     $formData = [
//         ...$this->basicAssetData,
//         'maintenance_frequency' => 'annual',
//         'need_maintenance' => true,
//         'next_maintenance_date' => Carbon::now()->addYear(),
//         'last_maintenance_date' => Carbon::now()->toDateString(),
//     ];

//     $response = $this->postToTenant('api.assets.store', $formData);
//     $response->assertSessionHasNoErrors();
//     $response->assertStatus(200);

//     assertDatabaseCount('scheduled_notifications', 1);

//     $asset = Asset::find(1);

//     $formData = [
//         ...$this->basicAssetData,
//         'maintenance_manager_id' => $this->manager->id,
//         'need_maintenance' => false,
//     ];

//     $response = $this->patchToTenant('api.assets.update', $formData, $asset->reference_code);
//     $response->assertSessionHasNoErrors();
//     $response->assertStatus(200);

//     assertDatabaseCount('scheduled_notifications', 0);
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


// it('creates notification when adding maintenance manager to existing asset without maintenance manager', function () {

//     $formData = [
//         ...$this->basicAssetData,
//         'maintenance_frequency' => 'annual',
//         'need_maintenance' => true,
//         'next_maintenance_date' => Carbon::now()->addYear(),
//         'last_maintenance_date' => Carbon::now()->toDateString(),
//     ];

//     $response = $this->postToTenant('api.assets.store', $formData);
//     $response->assertSessionHasNoErrors();
//     $response->assertStatus(200);

//     assertDatabaseCount('scheduled_notifications', 1);
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

//     $asset = Asset::find(1);

//     $formData = [
//         ...$this->basicAssetData,
//         'maintenance_manager_id' => $this->manager->id,
//         'need_maintenance' => true,
//         'maintenance_frequency' => 'annual',
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
//             'notifiable_id' => 1,
//         ]
//     );
// });

// it('creates notification when replacing maintenance manager for the asset and removes notifications for old maintenance manager', function () {

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

//     assertDatabaseCount('scheduled_notifications', 2);

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

// it('deletes notification when removing maintenance_manager from existing asset', function () {
//     $formData = [
//         ...$this->basicAssetData,
//         'maintenance_frequency' => 'annual',
//         'need_maintenance' => true,
//         'next_maintenance_date' => Carbon::now()->addYear(),
//         'last_maintenance_date' => Carbon::now()->toDateString(),
//         'maintenance_manager_id' => $this->manager->id,
//     ];

//     $response = $this->postToTenant('api.assets.store', $formData);
//     $response->assertSessionHasNoErrors();
//     $response->assertStatus(200);

//     assertDatabaseCount('scheduled_notifications', 2);
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

//     $formData = [
//         ...$this->basicAssetData,
//         'maintenance_frequency' => 'annual',
//         'need_maintenance' => true,
//         'next_maintenance_date' => Carbon::now()->addYear(),
//         'last_maintenance_date' => Carbon::now()->toDateString(),
//     ];

//     $asset = Asset::find(1);
//     $response = $this->patchToTenant('api.assets.update', $formData, $asset->reference_code);

//     $response->assertSessionHasNoErrors();
//     $response->assertStatus(200);

//     assertDatabaseCount('scheduled_notifications', 1);
//     assertDatabaseMissing(
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

// it('updates notification when updating next_maintenance_date of the asset', function () {

//     $asset = Asset::factory()->forLocation($this->room)->create();

//     $formData = [
//         ...$this->basicAssetData,
//         'maintenance_manager_id' => $this->manager->id,
//         'maintenance_frequency' => 'annual',
//         'need_maintenance' => true,
//         'next_maintenance_date' => Carbon::now()->addMonth(),
//         'last_maintenance_date' => Carbon::now()->toDateString(),
//     ];

//     $response = $this->patchToTenant('api.assets.update', $formData, $asset->reference_code);

//     assertDatabaseHas(
//         'scheduled_notifications',
//         [
//             'recipient_name' => $this->manager->fullName,
//             'recipient_email' => $this->manager->email,
//             'notification_type' => 'next_maintenance_date',
//             'scheduled_at' => Carbon::now()->addMonth()->subDays(7)->toDateString(),
//             'notifiable_type' => 'App\Models\Tenants\Asset',
//             'notifiable_id' => $asset->id,
//         ]
//     );

//     $newformData = [
//         ...$this->basicAssetData,
//         'maintenance_manager_id' => $this->manager->id,
//         'maintenance_frequency' => 'annual',
//         'need_maintenance' => true,
//         'next_maintenance_date' => Carbon::now()->addYear(),
//         'last_maintenance_date' => Carbon::now()->toDateString(),
//     ];

//     $asset->refresh();
//     $response = $this->patchToTenant('api.assets.update', $newformData, $asset->reference_code);
//     $response->assertStatus(200);
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
//             'notifiable_id' => $asset->id,
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
//             'notifiable_id' => $asset->id,
//         ]
//     );
// });
