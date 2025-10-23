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

it('creates all notifications (warranty, depreciable, maintenance) for a new created asset', function () {

    $formData = [
        ...$this->basicAssetData,
        'under_warranty' => true,
        'end_warranty_date' => Carbon::now()->addMonths(10),
        'maintenance_manager_id' => $this->manager->id,
        'depreciable' => true,
        'depreciation_start_date' => Carbon::now()->toDateString(),
        'depreciation_end_date' => Carbon::now()->addYear(3)->toDateString(),
        'depreciation_duration' => 3,
        'residual_value' => 1250.69,
        'maintenance_frequency' => 'annual',
        'need_maintenance' => true,
        'next_maintenance_date' => Carbon::now()->addYear(),
        'last_maintenance_date' => Carbon::now()->toDateString(),
    ];

    $response = $this->postToTenant('api.assets.store', $formData);
    $response->assertSessionHasNoErrors();
    $response->assertStatus(200);

    assertDatabaseCount('scheduled_notifications', 6);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'end_warranty_date',
            'scheduled_at' => Carbon::now()->addMonths(10)->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => 1,
        ]
    );

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_warranty_date',
            'scheduled_at' => Carbon::now()->addMonths(10)->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => 1,
        ]
    );

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

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addYear()->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => 1,
        ]
    );
});

it(
    'deletes notifications when asset is soft deleted',
    function () {
        $formData = [
            ...$this->basicAssetData,
            'under_warranty' => true,
            'end_warranty_date' => Carbon::now()->addMonths(10),
            'maintenance_manager_id' => $this->manager->id,
            'depreciable' => true,
            'depreciation_start_date' => Carbon::now()->toDateString(),
            'depreciation_end_date' => Carbon::now()->addYear(3)->toDateString(),
            'depreciation_duration' => 3,
            'residual_value' => 1250.69,
            'maintenance_frequency' => 'annual',
            'need_maintenance' => true,
            'next_maintenance_date' => Carbon::now()->addYear(),
            'last_maintenance_date' => Carbon::now()->toDateString(),
        ];

        $response = $this->postToTenant('api.assets.store', $formData);
        $response->assertSessionHasNoErrors();
        $response->assertStatus(200);

        assertDatabaseCount('scheduled_notifications', 6);

        $asset = Asset::find(1);

        $response = $this->deleteFromTenant('api.assets.destroy', $asset->reference_code);

        assertDatabaseEmpty('scheduled_notifications');
    }
);

it('creates notifications when asset is restored', function () {

    $formData = [
        ...$this->basicAssetData,
        'under_warranty' => true,
        'end_warranty_date' => Carbon::now()->addMonths(10),
        'maintenance_manager_id' => $this->manager->id,
        'depreciable' => true,
        'depreciation_start_date' => Carbon::now()->toDateString(),
        'depreciation_end_date' => Carbon::now()->addYear(3)->toDateString(),
        'depreciation_duration' => 3,
        'residual_value' => 1250.69,
        'maintenance_frequency' => 'annual',
        'need_maintenance' => true,
        'next_maintenance_date' => Carbon::now()->addYear(),
        'last_maintenance_date' => Carbon::now()->toDateString(),
    ];

    $response = $this->postToTenant('api.assets.store', $formData);
    $response->assertSessionHasNoErrors();
    $response->assertStatus(200);

    assertDatabaseCount('scheduled_notifications', 6);

    $asset = Asset::find(1);

    $response = $this->deleteFromTenant('api.assets.destroy', $asset->reference_code);

    assertDatabaseEmpty('scheduled_notifications');

    $response = $this->postToTenant('api.assets.restore', [], $asset->reference_code);

    assertDatabaseCount('scheduled_notifications', 6);
});
