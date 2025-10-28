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

// it('creates depreciation notification for admin & maintenance manager for a new created asset', function () {

//     $formData = [
//         ...$this->basicAssetData,
//         'maintenance_manager_id' => $this->manager->id,
//         'depreciable' => true,
//         'depreciation_start_date' => Carbon::now()->toDateString(),
//         'depreciation_end_date' => Carbon::now()->addYear(3)->toDateString(),
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

// it('creates a depreciation notification for admin & maintenance manager if scheduled_at if depreciation_end_date > today', function () {

//     $formData = [
//         ...$this->basicAssetData,
//         'maintenance_manager_id' => $this->manager->id,
//         'depreciable' => true,
//         'depreciation_start_date' => Carbon::now()->toDateString(),
//         'depreciation_end_date' => Carbon::tomorrow()->toDateString(),
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
//             'scheduled_at' => Carbon::tomorrow()->subDays(7)->toDateString(),
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
//             'scheduled_at' => Carbon::tomorrow()->subDays(7)->toDateString(),
//             'notifiable_type' => 'App\Models\Tenants\Asset',
//             'notifiable_id' => 1,
//         ]
//     );
// });

// it('does not create a depreciation notification if depreciation_end_date is today or in the past', function () {

//     $formData = [
//         ...$this->basicAssetData,
//         'maintenance_manager_id' => $this->manager->id,
//         'depreciable' => true,
//         'depreciation_start_date' => Carbon::now()->subYears(2)->toDateString(),
//         'depreciation_end_date' => Carbon::now()->toDateString(),
//         'depreciation_duration' => 3,
//         'residual_value' => 1250.69,
//     ];

//     $this->postToTenant('api.assets.store', $formData);

//     assertDatabaseCount('scheduled_notifications', 0);
// });

// it('creates depreciation notification when depreciable passes from false to true', function () {

//     $formData = [
//         ...$this->basicAssetData,
//         'maintenance_manager_id' => $this->manager->id,
//         'depreciable' => false,
//     ];

//     $this->postToTenant('api.assets.store', $formData);

//     assertDatabaseCount('scheduled_notifications', 0);


//     $formData = [
//         ...$this->basicAssetData,
//         'maintenance_manager_id' => $this->manager->id,
//         'depreciable' => true,
//         'depreciation_start_date' => Carbon::now()->toDateString(),
//         'depreciation_end_date' => Carbon::now()->addYear(3)->toDateString(),
//         'depreciation_duration' => 3,
//         'residual_value' => 1250.69,
//     ];

//     $asset = Asset::find(1);
//     $response = $this->patchToTenant('api.assets.update', $formData, $asset->reference_code);
//     $response->assertSessionHasNoErrors();
//     $response->assertStatus(200);

//     $asset->refresh();

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

// it('updates depreciation notification when depreciable_end_date changes', function () {
//     $formData = [
//         ...$this->basicAssetData,
//         'maintenance_manager_id' => $this->manager->id,
//         'depreciable' => true,
//         'depreciation_start_date' => Carbon::now()->toDateString(),
//         'depreciation_end_date' => Carbon::now()->addYear()->toDateString(),
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
//             'notification_type' => 'depreciation_end_date',
//             'scheduled_at' => Carbon::now()->addYear()->subDays(7)->toDateString(),
//             'notifiable_type' => 'App\Models\Tenants\Asset',
//             'notifiable_id' => 1,
//         ]
//     );

//     $formData = [
//         ...$this->basicAssetData,
//         'maintenance_manager_id' => $this->manager->id,
//         'depreciable' => true,
//         'depreciation_start_date' => Carbon::now()->toDateString(),
//         'depreciation_end_date' => Carbon::now()->addYears(3)->toDateString(),
//         'depreciation_duration' => 3,
//         'residual_value' => 1250.69,
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
//             'notification_type' => 'depreciation_end_date',
//             'scheduled_at' => Carbon::now()->addYears(3)->subDays(7)->toDateString(),
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
//             'scheduled_at' => Carbon::now()->addYears(3)->subDays(7)->toDateString(),
//             'notifiable_type' => 'App\Models\Tenants\Asset',
//             'notifiable_id' => 1,
//         ]
//     );
// });

// it('deletes depreciation notification when depreciable passes from true to false and status is `pending`', function () {

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
//             'status' => 'pending',
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

//     $formData = [
//         ...$this->basicAssetData,
//         'maintenance_manager_id' => $this->manager->id,
//         'depreciable' => false,
//     ];

//     $asset = Asset::find(1);
//     $response = $this->patchToTenant('api.assets.update', $formData, $asset->reference_code);
//     $response->assertSessionHasNoErrors();
//     $response->assertStatus(200);

//     assertDatabaseCount('scheduled_notifications', 0);
// });

// it('does not delete depreciation notification when depreciable passes from true to false and notification has status `sent`', function () {

//     $formData = [
//         ...$this->basicAssetData,
//         'depreciable' => true,
//         'depreciation_start_date' => Carbon::now()->toDateString(),
//         'depreciation_end_date' => Carbon::now()->addYear(3)->toDateString(),
//         'depreciation_duration' => 3,
//         'residual_value' => 1250.69,
//     ];

//     $this->postToTenant('api.assets.store', $formData);

//     assertDatabaseCount('scheduled_notifications', 1);
//     assertDatabaseHas(
//         'scheduled_notifications',
//         [
//             'recipient_name' => $this->admin->fullName,
//             'recipient_email' => $this->admin->email,
//             'notification_type' => 'depreciation_end_date',
//             'status' => 'pending',
//             'scheduled_at' => Carbon::now()->addYear(3)->subDays(7)->toDateString(),
//             'notifiable_type' => 'App\Models\Tenants\Asset',
//             'notifiable_id' => 1,
//         ]
//     );

//     $notification = ScheduledNotification::first();
//     $notification->update(['status' => 'sent']);

//     $formData = [
//         ...$this->basicAssetData,
//         'maintenance_manager_id' => $this->manager->id,
//         'depreciable' => false,
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
//             'notification_type' => 'depreciation_end_date',
//             'status' => 'sent',
//             'scheduled_at' => Carbon::now()->addYear(3)->subDays(7)->toDateString(),
//             'notifiable_type' => 'App\Models\Tenants\Asset',
//             'notifiable_id' => 1,
//         ]
//     );
// });

// it('creates depreciation notification when maintenance manager is defined when creating asset', function () {

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

// it('creates notification for maintenance manager when maintenance manager is added to an asset', function () {

//     $formData = [
//         ...$this->basicAssetData,
//         'depreciable' => true,
//         'depreciation_start_date' => Carbon::now()->toDateString(),
//         'depreciation_end_date' => Carbon::now()->addYear(3)->toDateString(),
//         'depreciation_duration' => 3,
//         'residual_value' => 1250.69,
//     ];

//     $this->postToTenant('api.assets.store', $formData);

//     assertDatabaseMissing(
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

//     $formData = [
//         ...$this->basicAssetData,
//         'maintenance_manager_id' => $this->manager->id,
//         'depreciable' => true,
//         'depreciation_start_date' => Carbon::now()->toDateString(),
//         'depreciation_end_date' => Carbon::now()->addYear(3)->toDateString(),
//         'depreciation_duration' => 3,
//         'residual_value' => 1250.69,
//     ];

//     $asset = Asset::first();

//     $response = $this->patchToTenant('api.assets.update', $formData, $asset->reference_code);

//     $asset->refresh();

//     assertDatabaseCount('scheduled_notifications', 2);

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

// it('deletes depreciation notification when maintenance manager is removed from the asset if status is `pending`', function () {

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
//             'recipient_name' => $this->manager->fullName,
//             'recipient_email' => $this->manager->email,
//             'notification_type' => 'depreciation_end_date',
//             'status' => 'pending',
//             'scheduled_at' => Carbon::now()->addYear(3)->subDays(7)->toDateString(),
//             'notifiable_type' => 'App\Models\Tenants\Asset',
//             'notifiable_id' => 1,
//         ]
//     );

//     $formData = [
//         ...$this->basicAssetData,
//         'depreciable' => true,
//         'depreciation_start_date' => Carbon::now()->toDateString(),
//         'depreciation_end_date' => Carbon::now()->addYear(3)->toDateString(),
//         'depreciation_duration' => 3,
//         'residual_value' => 1250.69,
//     ];

//     $asset = Asset::first();

//     $response = $this->patchToTenant('api.assets.update', $formData, $asset->reference_code);

//     assertDatabaseMissing(
//         'scheduled_notifications',
//         [
//             'recipient_name' => $this->manager->fullName,
//             'recipient_email' => $this->manager->email,
//             'notification_type' => 'depreciation_end_date',
//             'status' => 'pending',
//             'scheduled_at' => Carbon::now()->addYear(3)->subDays(7)->toDateString(),
//             'notifiable_type' => 'App\Models\Tenants\Asset',
//             'notifiable_id' => 1,
//         ]
//     );
// });

// it('does not delete depreciation notification when maintenance manager is removed from the asset if status is `sent`', function () {

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
//             'recipient_name' => $this->manager->fullName,
//             'recipient_email' => $this->manager->email,
//             'status' => 'pending',
//             'notification_type' => 'depreciation_end_date',
//             'scheduled_at' => Carbon::now()->addYear(3)->subDays(7)->toDateString(),
//             'notifiable_type' => 'App\Models\Tenants\Asset',
//             'notifiable_id' => 1,
//         ]
//     );

//     $notification = ScheduledNotification::where('notifiable_type', 'App\Models\Tenants\Asset')->where('notifiable_id', 1)->where('user_id', $this->manager->id)->first();
//     $notification->update(['status' => 'sent']);

//     $formData = [
//         ...$this->basicAssetData,
//         'depreciable' => true,
//         'depreciation_start_date' => Carbon::now()->toDateString(),
//         'depreciation_end_date' => Carbon::now()->addYear(3)->toDateString(),
//         'depreciation_duration' => 3,
//         'residual_value' => 1250.69,
//     ];

//     $asset = Asset::first();

//     $this->patchToTenant('api.assets.update', $formData, $asset->reference_code);

//     assertDatabaseHas(
//         'scheduled_notifications',
//         [
//             'recipient_name' => $this->manager->fullName,
//             'recipient_email' => $this->manager->email,
//             'notification_type' => 'depreciation_end_date',
//             'status' => 'sent',
//             'scheduled_at' => Carbon::now()->addYear(3)->subDays(7)->toDateString(),
//             'notifiable_type' => 'App\Models\Tenants\Asset',
//             'notifiable_id' => 1,
//         ]
//     );
// });

// it('removes old maintenance manager depreciation notification when new one is added', function () {

//     $tempManager = User::factory()->withRole('Maintenance Manager')->create();

//     $formData = [
//         ...$this->basicAssetData,
//         'maintenance_manager_id' => $tempManager->id,
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
//             'recipient_name' => $tempManager->fullName,
//             'recipient_email' => $tempManager->email,
//             'notification_type' => 'depreciation_end_date',
//             'scheduled_at' => Carbon::now()->addYear(3)->subDays(7)->toDateString(),
//             'notifiable_type' => 'App\Models\Tenants\Asset',
//             'notifiable_id' => 1,
//         ]
//     );

//     $formData = [
//         ...$this->basicAssetData,
//         'maintenance_manager_id' => $this->manager->id,
//         'depreciable' => true,
//         'depreciation_start_date' => Carbon::now()->toDateString(),
//         'depreciation_end_date' => Carbon::now()->addYear(3)->toDateString(),
//         'depreciation_duration' => 3,
//         'residual_value' => 1250.69,
//     ];

//     $asset = Asset::first();

//     $response = $this->patchToTenant('api.assets.update', $formData, $asset->reference_code);

//     assertDatabaseMissing(
//         'scheduled_notifications',
//         [
//             'recipient_name' => $tempManager->fullName,
//             'recipient_email' => $tempManager->email,
//             'notification_type' => 'depreciation_end_date',
//             'scheduled_at' => Carbon::now()->addYear(3)->subDays(7)->toDateString(),
//             'notifiable_type' => 'App\Models\Tenants\Asset',
//             'notifiable_id' => 1,
//         ]
//     );
// });

// it('creates depreciation notification when maintenance manager is replaced in an asset', function () {
//     $tempManager = User::factory()->withRole('Maintenance Manager')->create();

//     $formData = [
//         ...$this->basicAssetData,
//         'maintenance_manager_id' => $tempManager->id,
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
//             'recipient_name' => $tempManager->fullName,
//             'recipient_email' => $tempManager->email,
//             'notification_type' => 'depreciation_end_date',
//             'scheduled_at' => Carbon::now()->addYear(3)->subDays(7)->toDateString(),
//             'notifiable_type' => 'App\Models\Tenants\Asset',
//             'notifiable_id' => 1,
//         ]
//     );

//     $formData = [
//         ...$this->basicAssetData,
//         'maintenance_manager_id' => $this->manager->id,
//         'depreciable' => true,
//         'depreciation_start_date' => Carbon::now()->toDateString(),
//         'depreciation_end_date' => Carbon::now()->addYear(3)->toDateString(),
//         'depreciation_duration' => 3,
//         'residual_value' => 1250.69,
//     ];

//     $asset = Asset::first();

//     $response = $this->patchToTenant('api.assets.update', $formData, $asset->reference_code);

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

// it('updates notifications when notification preference depreciation_end_date of user changes', function () {

//     $formData = [
//         ...$this->basicAssetData,
//         'depreciable' => true,
//         'depreciation_start_date' => Carbon::now()->toDateString(),
//         'depreciation_end_date' => Carbon::now()->addYears(3)->toDateString(),
//         'depreciation_duration' => 3,
//         'residual_value' => 1250.69,

//     ];

//     $response = $this->postToTenant('api.assets.store', $formData);
//     assertDatabaseCount('scheduled_notifications', 1);
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

// it('deletes notifications when notification preference depreciation_end_date of user is disabled and status is `pending`', function () {

//     $formData = [
//         ...$this->basicAssetData,
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

// it('does not delete depreciation notifications when notification preference depreciation_end_date of user is disabled and status is `sent`', function () {

//     $formData = [
//         ...$this->basicAssetData,
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
//             'status' => 'pending',
//             'scheduled_at' => Carbon::now()->addYears(3)->subDays(7)->toDateString(),
//             'notifiable_type' => 'App\Models\Tenants\Asset',
//             'notifiable_id' => 1,
//         ]
//     );

//     $notification = ScheduledNotification::where('notifiable_type', 'App\Models\Tenants\Asset')->where('notifiable_id', 1)->where('notification_type', 'depreciation_end_date')->first();
//     $notification->update(['status' => 'sent']);

//     $preference = $this->admin->notification_preferences()->where('notification_type', 'depreciation_end_date')->first();

//     $formData = [
//         'asset_type' => 'asset',
//         'notification_type' => 'depreciation_end_date',
//         'notification_delay_days' => $preference->notification_delay_days,
//         'enabled' => false,
//     ];

//     $response = $this->patchToTenant('api.notifications.update', $formData, $preference->id);
//     $response->assertStatus(200);

//     assertDatabaseHas(
//         'scheduled_notifications',
//         [
//             'recipient_name' => $this->admin->fullName,
//             'recipient_email' => $this->admin->email,
//             'notification_type' => 'depreciation_end_date',
//             'status' => 'sent',
//             'scheduled_at' => Carbon::now()->addYears(3)->subDays(7)->toDateString(),
//             'notifiable_type' => 'App\Models\Tenants\Asset',
//             'notifiable_id' => 1,
//         ]
//     );
// });

// it('creates depreciation notifications for admin when notification preference depreciation_end_date of user is enabled for depreciation_end_date > today', function () {

//     $preference = $this->admin->notification_preferences()->where('notification_type', 'depreciation_end_date')->first();
//     $preference->update(['enabled' => false,]);

//     $formData = [
//         ...$this->basicAssetData,
//         'depreciable' => true,
//         'depreciation_start_date' => Carbon::now()->subYear()->toDateString(),
//         'depreciation_end_date' => Carbon::now()->toDateString(),
//         'depreciation_duration' => 3,
//     ];

//     $this->postToTenant('api.assets.store', $formData);

//     $formData = [
//         ...$this->basicAssetData,
//         'depreciable' => true,
//         'depreciation_start_date' => Carbon::now()->subYear()->toDateString(),
//         'depreciation_end_date' => Carbon::tomorrow()->toDateString(),
//         'depreciation_duration' => 3,
//     ];

//     $this->postToTenant('api.assets.store', $formData);

//     assertDatabaseCount('scheduled_notifications', 0);

//     $formData = [
//         'asset_type' => 'asset',
//         'notification_type' => 'depreciation_end_date',
//         'notification_delay_days' => $preference->notification_delay_days,
//         'enabled' => true,
//     ];

//     $response = $this->patchToTenant('api.notifications.update', $formData, $preference->id);
//     $response->assertStatus(200);

//     assertDatabaseMissing(
//         'scheduled_notifications',
//         [
//             'recipient_name' => $this->admin->fullName,
//             'recipient_email' => $this->admin->email,
//             'notification_type' => 'depreciation_end_date',
//             'scheduled_at' => Carbon::now()->addYears(3)->subDays($preference->notification_delay_days)->toDateString(),
//             'notifiable_type' => 'App\Models\Tenants\Asset',
//             'notifiable_id' => 1,
//         ]
//     );

//     assertDatabaseHas(
//         'scheduled_notifications',
//         [
//             'recipient_name' => $this->admin->fullName,
//             'recipient_email' => $this->admin->email,
//             'notification_type' => 'depreciation_end_date',
//             'scheduled_at' => Carbon::tomorrow()->subDays($preference->notification_delay_days)->toDateString(),
//             'notifiable_type' => 'App\Models\Tenants\Asset',
//             'notifiable_id' => 2,
//         ]
//     );
// });

// it('creates depreciation notifications for maintenance manager when notification preference depreciation_end_date of user is enabled for depreciation_end_date > today', function () {

//     $preference = $this->manager->notification_preferences()->where('notification_type', 'depreciation_end_date')->first();
//     $preference->update(['enabled' => false,]);

//     $formData = [
//         ...$this->basicAssetData,
//         'depreciable' => true,
//         'depreciation_start_date' => Carbon::now()->subYear()->toDateString(),
//         'depreciation_end_date' => Carbon::now()->toDateString(),
//         'depreciation_duration' => 3,
//         'maintenance_manager_id' => $this->manager->id
//     ];

//     $this->postToTenant('api.assets.store', $formData);

//     $formData = [
//         ...$this->basicAssetData,
//         'depreciable' => true,
//         'depreciation_start_date' => Carbon::now()->subYear()->toDateString(),
//         'depreciation_end_date' => Carbon::tomorrow()->toDateString(),
//         'depreciation_duration' => 3,
//         'maintenance_manager_id' => $this->manager->id
//     ];

//     $this->postToTenant('api.assets.store', $formData);

//     assertDatabaseMissing(
//         'scheduled_notifications',
//         [
//             'recipient_name' => $this->manager->fullName,
//             'recipient_email' => $this->manager->email,
//             'notification_type' => 'depreciation_end_date',
//             'scheduled_at' => Carbon::now()->addYears(3)->subDays($preference->notification_delay_days)->toDateString(),
//             'notifiable_type' => 'App\Models\Tenants\Asset',
//             'notifiable_id' => 1,
//         ]
//     );

//     assertDatabaseMissing(
//         'scheduled_notifications',
//         [
//             'recipient_name' => $this->manager->fullName,
//             'recipient_email' => $this->manager->email,
//             'notification_type' => 'depreciation_end_date',
//             'scheduled_at' => Carbon::tomorrow()->subDays($preference->notification_delay_days)->toDateString(),
//             'notifiable_type' => 'App\Models\Tenants\Asset',
//             'notifiable_id' => 2,
//         ]
//     );

//     $formData = [
//         'asset_type' => 'asset',
//         'notification_type' => 'depreciation_end_date',
//         'notification_delay_days' => $preference->notification_delay_days,
//         'enabled' => true,
//     ];

//     $response = $this->patchToTenant('api.notifications.update', $formData, $preference->id);
//     $response->assertStatus(200);

//     assertDatabaseMissing(
//         'scheduled_notifications',
//         [
//             'recipient_name' => $this->manager->fullName,
//             'recipient_email' => $this->manager->email,
//             'notification_type' => 'depreciation_end_date',
//             'scheduled_at' => Carbon::now()->addYears(3)->subDays($preference->notification_delay_days)->toDateString(),
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
//             'scheduled_at' => Carbon::tomorrow()->subDays($preference->notification_delay_days)->toDateString(),
//             'notifiable_type' => 'App\Models\Tenants\Asset',
//             'notifiable_id' => 2,
//         ]
//     );
// });

it('creates depreciation notifications for a new created user with admin role and only for not soft deleted assets', function () {

    $assetActive = Asset::factory()->forLocation($this->room)->create([
        'depreciation_start_date' => Carbon::now()->toDateString(),
        'depreciation_end_date' => Carbon::now()->addYears(3)->toDateString(),
        'depreciation_duration' => 3,
    ]);

    $assetSoftDeleted = Asset::factory()->forLocation($this->room)->create([
        'depreciation_start_date' => Carbon::now()->toDateString(),
        'depreciation_end_date' => Carbon::now()->addYears(3)->toDateString(),
        'depreciation_duration' => 3,
        'deleted_at' => Carbon::now()
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
            'notification_type' => 'depreciation_end_date',
            'scheduled_at' => Carbon::now()->addYear(3)->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => $assetActive->id,
        ]
    );

    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'recipient_name' => $createdUser->fullName,
            'recipient_email' => $createdUser->email,
            'notification_type' => 'depreciation_end_date',
            'scheduled_at' => Carbon::now()->addYear(3)->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => $assetSoftDeleted->id,
        ]
    );
});

it('creates depreciation notifications when the role of a maintenance manager changes to admin', function () {

    $assetActive = Asset::factory()->forLocation($this->room)->create([
        'depreciation_start_date' => Carbon::now()->toDateString(),
        'depreciation_end_date' => Carbon::now()->addYears(3)->toDateString(),
        'depreciation_duration' => 3,
    ]);

    $assetSoftDeleted = Asset::factory()->forLocation($this->room)->create([
        'depreciation_start_date' => Carbon::now()->toDateString(),
        'depreciation_end_date' => Carbon::now()->addYears(3)->toDateString(),
        'depreciation_duration' => 3,
        'deleted_at' => Carbon::now()
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
            'notification_type' => 'depreciation_end_date',
            'scheduled_at' => Carbon::now()->addYear(3)->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => $assetActive->id,
        ]
    );

    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'recipient_name' => $createdUser->fullName,
            'recipient_email' => $createdUser->email,
            'notification_type' => 'depreciation_end_date',
            'scheduled_at' => Carbon::now()->addYear(3)->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => $assetSoftDeleted->id,
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
            'notification_type' => 'depreciation_end_date',
            'scheduled_at' => Carbon::now()->addYear(3)->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => $assetActive->id,
        ]
    );

    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'recipient_name' => $createdUser->fullName,
            'recipient_email' => $createdUser->email,
            'notification_type' => 'depreciation_end_date',
            'scheduled_at' => Carbon::now()->addYear(3)->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => $assetSoftDeleted->id,
        ]
    );
});

it('deletes depreciation notifications when the role of an admin changes to maintenance manager', function () {
    $assetActive = Asset::factory()->forLocation($this->room)->create([
        'depreciation_start_date' => Carbon::now()->toDateString(),
        'depreciation_end_date' => Carbon::now()->addYears(3)->toDateString(),
        'depreciation_duration' => 3,
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
            'notification_type' => 'depreciation_end_date',
            'scheduled_at' => Carbon::now()->addYear(3)->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => $assetActive->id,
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
            'notification_type' => 'depreciation_end_date',
            'scheduled_at' => Carbon::now()->addYear(3)->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => $assetActive->id,
        ]
    );
});

it('deletes depreciation notifications when the role of an admin changes to maintenance manager for assets only where he is not maintenance manager', function () {
    $assetActive = Asset::factory()->forLocation($this->room)->create([
        'depreciation_start_date' => Carbon::now()->toDateString(),
        'depreciation_end_date' => Carbon::now()->addYears(3)->toDateString(),
        'depreciation_duration' => 3,
    ]);

    $assetWithManager = Asset::factory()->forLocation($this->room)->create([
        'depreciation_start_date' => Carbon::now()->toDateString(),
        'depreciation_end_date' => Carbon::now()->addYears(3)->toDateString(),
        'depreciation_duration' => 3,
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
            'notification_type' => 'depreciation_end_date',
            'scheduled_at' => Carbon::now()->addYear(3)->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => $assetActive->id,
        ]
    );

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $createdUser->fullName,
            'recipient_email' => $createdUser->email,
            'notification_type' => 'depreciation_end_date',
            'scheduled_at' => Carbon::now()->addYear(3)->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => $assetWithManager->id,
        ]
    );

    $assetWithManager->refresh();
    $assetWithManager->maintainable()->update(['maintenance_manager_id' => $createdUser->id]);

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
            'notification_type' => 'depreciation_end_date',
            'scheduled_at' => Carbon::now()->addYear(3)->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => $assetActive->id,
        ]
    );

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $createdUser->fullName,
            'recipient_email' => $createdUser->email,
            'notification_type' => 'depreciation_end_date',
            'scheduled_at' => Carbon::now()->addYear(3)->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => $assetWithManager->id,
        ]
    );
});

it('deletes depreciation notifications when a user is deleted', function () {

    $assetActive = Asset::factory()->forLocation($this->room)->create([
        'depreciation_start_date' => Carbon::now()->toDateString(),
        'depreciation_end_date' => Carbon::now()->addYears(3)->toDateString(),
        'depreciation_duration' => 3,
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
            'notification_type' => 'depreciation_end_date',
            'scheduled_at' => Carbon::now()->addYear(3)->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => $assetActive->id,
        ]
    );

    $this->deleteFromTenant('api.users.destroy', $createdUser);

    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'recipient_name' => $createdUser->fullName,
            'recipient_email' => $createdUser->email,
            'notification_type' => 'depreciation_end_date',
            'scheduled_at' => Carbon::now()->addYear(3)->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => $assetActive->id,
        ]
    );
});
