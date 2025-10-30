<?php

use App\Enums\MaintenanceFrequency;
use Carbon\Carbon;
use App\Models\LocationType;
use App\Models\Tenants\Room;
use App\Models\Tenants\Site;
use App\Models\Tenants\User;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Floor;
use App\Models\Tenants\Building;
use App\Models\Central\CategoryType;
use App\Models\Central\AssetCategory;
use App\Models\Tenants\Provider;

use function Pest\Laravel\assertDatabaseHas;
use function PHPUnit\Framework\assertEquals;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseMissing;
use function PHPUnit\Framework\assertCount;

beforeEach(function () {
    $this->user = User::factory()->withRole('Admin')->create();
    $this->actingAs($this->user, 'tenant');

    $this->siteType = LocationType::factory()->create(['level' => 'site']);
    $this->buildingType = LocationType::factory()->create(['level' => 'building']);
    $this->floorType = LocationType::factory()->create(['level' => 'floor']);
    $this->roomType = LocationType::factory()->create(['level' => 'room']);
    $this->category = CategoryType::factory()->create(['category' => 'asset']);

    $this->site = Site::factory()->create();
    $this->building = Building::factory()->create();
    $this->floor = Floor::factory()->create();

    $this->room = Room::factory()
        ->for(LocationType::where('level', 'room')->first())
        ->for(Floor::first())
        ->create();
});

it('can create with need_maintenance without last_maintenance_date and calculates next_maintenance_date', function () {
    $formData = [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'locationId' => $this->site->id,
        'locationReference' => $this->site->reference_code,
        'locationType' => 'site',
        'categoryId' => $this->category->id,
        'need_maintenance' => true,
        'maintenance_frequency' => MaintenanceFrequency::ANNUAL->value
    ];

    $response = $this->postToTenant('api.assets.store', $formData);
    $response->assertStatus(200);

    $asset = Asset::first();

    assertDatabaseHas('maintainables', [
        'maintainable_type' => get_class($asset),
        'maintainable_id' => $asset->id,
        'name' => 'New asset',
        'description' => 'Description new asset',
        'need_maintenance' => true,
        'maintenance_frequency' => 'annual',
        'next_maintenance_date' => Carbon::now()->addYear()->toDateString()
    ]);
});

it('can create with need_maintenance with last_maintenance_date and calculates next_maintenance_date based on it', function () {
    $formData = [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'locationId' => $this->site->id,
        'locationReference' => $this->site->reference_code,
        'locationType' => 'site',
        'categoryId' => $this->category->id,
        'need_maintenance' => true,

        'last_maintenance_date' => Carbon::now()->subMonths(2)->toDateString(),
        'maintenance_frequency' => MaintenanceFrequency::ANNUAL->value
    ];

    $response = $this->postToTenant('api.assets.store', $formData);
    $response->assertStatus(200);

    $asset = Asset::first();

    assertDatabaseHas('maintainables', [
        'maintainable_type' => get_class($asset),
        'maintainable_id' => $asset->id,
        'name' => 'New asset',
        'description' => 'Description new asset',
        'need_maintenance' => true,
        'maintenance_frequency' => 'annual',
        'last_maintenance_date' => Carbon::now()->subMonths(2)->toDateString(),
        'next_maintenance_date' => Carbon::now()->addYear()->subMonths(2)->toDateString()
    ]);
});

it('can create with need_maintenance with last_maintenance_date and next_maintenance_date', function () {
    $formData = [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'locationId' => $this->site->id,
        'locationReference' => $this->site->reference_code,
        'locationType' => 'site',
        'categoryId' => $this->category->id,
        'need_maintenance' => true,
        'last_maintenance_date' => Carbon::now()->subMonths(2)->toDateString(),
        'next_maintenance_date' => Carbon::now()->addMonths(2)->toDateString(),
        'maintenance_frequency' => MaintenanceFrequency::ANNUAL->value
    ];

    $response = $this->postToTenant('api.assets.store', $formData);
    $response->assertStatus(200);

    $asset = Asset::first();

    assertDatabaseHas('maintainables', [
        'maintainable_type' => get_class($asset),
        'maintainable_id' => $asset->id,
        'name' => 'New asset',
        'description' => 'Description new asset',
        'need_maintenance' => true,
        'maintenance_frequency' => 'annual',
        'last_maintenance_date' => Carbon::now()->subMonths(2)->toDateString(),
        'next_maintenance_date' => Carbon::now()->addMonths(2)->toDateString(),
    ]);
});

it('can update the maintenance frequency and change the next_maintenance_date accordingly', function () {
    $formData = [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'locationId' => $this->site->id,
        'locationReference' => $this->site->reference_code,
        'locationType' => 'site',
        'categoryId' => $this->category->id,
        'need_maintenance' => true,
        'maintenance_frequency' => MaintenanceFrequency::ANNUAL->value,
        'next_maintenance_date' => Carbon::now()->addMonths(2)->toDateString(),
    ];

    $response = $this->postToTenant('api.assets.store', $formData);
    $response->assertStatus(200);

    $asset = Asset::first();

    $formData = [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'locationId' => $this->site->id,
        'locationReference' => $this->site->reference_code,
        'locationType' => 'site',
        'categoryId' => $this->category->id,
        'need_maintenance' => true,
        'maintenance_frequency' => MaintenanceFrequency::BIANNUAL->value,
        'next_maintenance_date' => Carbon::now()->addMonths(2)->toDateString(),
    ];

    $response = $this->patchToTenant('api.assets.update', $formData, $asset->reference_code);
    $response->assertStatus(200);

    assertDatabaseHas('maintainables', [
        'maintainable_type' => get_class($asset),
        'maintainable_id' => $asset->id,
        'name' => 'New asset',
        'description' => 'Description new asset',
        'need_maintenance' => true,
        'last_maintenance_date' => null,
        'maintenance_frequency' => 'biannual',
        'next_maintenance_date' => Carbon::now()->addDays(MaintenanceFrequency::from(MaintenanceFrequency::BIANNUAL->value)->days())->toDateString(),
    ]);
});

it('can update the maintenance_next_date manually', function () {
    $formData = [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'locationId' => $this->site->id,
        'locationReference' => $this->site->reference_code,
        'locationType' => 'site',
        'categoryId' => $this->category->id,
        'need_maintenance' => true,
        'maintenance_frequency' => MaintenanceFrequency::ANNUAL->value,
        'next_maintenance_date' => Carbon::now()->addMonths(2)->toDateString(),
    ];

    $response = $this->postToTenant('api.assets.store', $formData);
    $response->assertStatus(200);

    $asset = Asset::first();

    $formData = [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'locationId' => $this->site->id,
        'locationReference' => $this->site->reference_code,
        'locationType' => 'site',
        'categoryId' => $this->category->id,
        'need_maintenance' => true,
        'maintenance_frequency' => MaintenanceFrequency::ANNUAL->value,
        'next_maintenance_date' => Carbon::now(),
    ];

    $response = $this->patchToTenant('api.assets.update', $formData, $asset->reference_code);
    $response->assertStatus(200);

    assertDatabaseHas('maintainables', [
        'maintainable_type' => get_class($asset),
        'maintainable_id' => $asset->id,
        'name' => 'New asset',
        'description' => 'Description new asset',
        'need_maintenance' => true,
        'maintenance_frequency' => 'annual',
        'next_maintenance_date' => Carbon::now()->toDateString(),
    ]);
});

it('updates the next_maintenance_date when maintenance marked as done', function ($frequency) {
    $formData = [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'locationId' => $this->site->id,
        'locationReference' => $this->site->reference_code,
        'locationType' => 'site',
        'categoryId' => $this->category->id,
        'need_maintenance' => true,
        'maintenance_frequency' => $frequency,
        'next_maintenance_date' => Carbon::now()->addMonths(2)->toDateString(),
    ];

    $response = $this->postToTenant('api.assets.store', $formData);
    $response->assertStatus(200);

    $asset = Asset::first();

    $response = $this->patchToTenant('api.maintenance.done', [], $asset->maintainable);

    assertDatabaseHas('maintainables', [
        'maintainable_type' => get_class($asset),
        'maintainable_id' => $asset->id,
        'name' => 'New asset',
        'description' => 'Description new asset',
        'need_maintenance' => true,
        'last_maintenance_date' => Carbon::now()->toDateString(),
        'maintenance_frequency' => $frequency,
        'next_maintenance_date' => Carbon::now()->addDays(MaintenanceFrequency::from($frequency)->days())->toDateString(),
    ]);
})->with(array_values(array_diff(array_column(MaintenanceFrequency::cases(), 'value'), ['on demand'])));


it('clears the next_maintenance_date when maintenance marked as done and frequency ON DEMAND', function () {
    $formData = [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'locationId' => $this->site->id,
        'locationReference' => $this->site->reference_code,
        'locationType' => 'site',
        'categoryId' => $this->category->id,
        'need_maintenance' => true,
        'maintenance_frequency' => 'on demand',
        'next_maintenance_date' => Carbon::now()->addMonths(2)->toDateString(),
    ];

    $response = $this->postToTenant('api.assets.store', $formData);
    $response->assertStatus(200);

    $asset = Asset::first();

    $response = $this->patchToTenant('api.maintenance.done', [], $asset->maintainable);

    assertDatabaseHas('maintainables', [
        'maintainable_type' => get_class($asset),
        'maintainable_id' => $asset->id,
        'name' => 'New asset',
        'description' => 'Description new asset',
        'need_maintenance' => true,
        'last_maintenance_date' => Carbon::now()->toDateString(),
        'maintenance_frequency' => 'on demand',
        'next_maintenance_date' => null,
    ]);
});
