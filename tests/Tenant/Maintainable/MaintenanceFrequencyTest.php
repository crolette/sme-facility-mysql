<?php


use Carbon\Carbon;
use App\Models\LocationType;
use App\Models\Tenants\Room;
use App\Models\Tenants\Site;
use App\Models\Tenants\User;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Floor;
use App\Models\Tenants\Building;
use App\Models\Tenants\Document;

use Illuminate\Http\UploadedFile;
use App\Enums\MaintenanceFrequency;
use App\Models\Central\CategoryType;
use function PHPUnit\Framework\assertCount;
use function Pest\Laravel\assertDatabaseHas;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertNotNull;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseMissing;

beforeEach(function () {

    $this->user = User::factory()->withRole('Admin')->create();
    $this->actingAs($this->user, 'tenant');

    $this->manager = User::factory()->create();

    $this->siteType = LocationType::factory()->create(['level' => 'site']);
    $this->buildingType = LocationType::factory()->create(['level' => 'building']);
    $this->floorType = LocationType::factory()->create(['level' => 'floor']);
    $this->roomType = LocationType::factory()->create(['level' => 'room']);

    $this->categoryType = CategoryType::factory()->create(['category' => 'asset']);
    $this->site = Site::factory()->create();
    $this->building = Building::factory()->create();
    $this->floor = Floor::factory()->create();

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
        'maintenance_manager_id' => $this->manager->id
    ];
});


it('can add maintenance frequency to asset without next_maintenance_date (and calculate automatically the next_date based on frequency and last_maintenance in the past)', function (string $frequency) {

    $formData = [
        ...$this->basicAssetData,
        'maintenance_frequency' => $frequency,
        'need_maintenance' => true,
        'last_maintenance_date' => Carbon::now()->subDays(7)->toDateString()
    ];

    $response = $this->postToTenant('api.assets.store', $formData);
    $response->assertSessionHasNoErrors();

    $nextMaintenanceDate = Carbon::now()->subDays(7)->addDays(MaintenanceFrequency::from($frequency)->days())->toDateString();

    if ($nextMaintenanceDate < now())
        $expectedDate = Carbon::now()->addDays(MaintenanceFrequency::from($frequency)->days())->toDateString();
    else
        $expectedDate = Carbon::now()->subDays(7)->addDays(MaintenanceFrequency::from($frequency)->days())->toDateString();


    assertDatabaseHas('maintainables', [
        'maintainable_type' => Asset::class,
        'maintainable_id' => Asset::first()->id,
        'maintenance_frequency' => $frequency,
        'need_maintenance' => true,
        'last_maintenance_date' => Carbon::now()->subDays(7)->toDateString(),
        'next_maintenance_date' => $expectedDate
    ]);
})->with(array_values(array_diff(array_column(MaintenanceFrequency::cases(), 'value'), ['on demand'])));

it('can create asset with need_maintenance but not next/last_maintenance_date and next_maintenance_date is calculated automatically', function ($frequency) {

    $formData = [
        ...$this->basicAssetData,
        'maintenance_frequency' => $frequency,
        'need_maintenance' => true,
    ];

    $response = $this->postToTenant('api.assets.store', $formData);
    $response->assertSessionHasNoErrors();

    assertDatabaseHas('maintainables', [
        'maintainable_type' => Asset::class,
        'maintainable_id' => Asset::first()->id,
        'maintenance_frequency' => $frequency,
        'need_maintenance' => true,
        'last_maintenance_date' => null,
        'next_maintenance_date' => Carbon::now()->addDays(MaintenanceFrequency::from($frequency)->days())->toDateString()
    ]);
})->with(array_values(array_diff(array_column(MaintenanceFrequency::cases(), 'value'), ['on demand'])));

it('can add maintenance frequency to asset without next_maintenance_date (and calculate automatically the next_date where the next_date should be today based on frequency and last_maintenance in the past)', function (string $frequency) {

    $formData = [
        ...$this->basicAssetData,
        'maintenance_frequency' => $frequency,
        'need_maintenance' => true,
        'last_maintenance_date' => Carbon::now()->subDays(MaintenanceFrequency::from($frequency)->days())->toDateString()
    ];

    $response = $this->postToTenant('api.assets.store', $formData);
    $response->assertSessionHasNoErrors();

    assertDatabaseHas('maintainables', [
        'maintainable_type' => Asset::class,
        'maintainable_id' => Asset::first()->id,
        'maintenance_frequency' => $frequency,
        'need_maintenance' => true,
        'last_maintenance_date' => Carbon::now()->subDays(MaintenanceFrequency::from($frequency)->days())->toDateString(),
        'next_maintenance_date' => Carbon::now()->addDays(MaintenanceFrequency::from($frequency)->days())->toDateString()
    ]);
})->with(array_values(array_diff(array_column(MaintenanceFrequency::cases(), 'value'), ['on demand'])));

it('can add maintenance frequency to asset with next_maintenance_date (and does not calculate automatically based on frequency and last_maintenance_date)', function (string $frequency) {

    $formData = [
        ...$this->basicAssetData,
        'maintenance_frequency' => $frequency,
        'need_maintenance' => true,
        'last_maintenance_date' => '2025-05-05',
        'next_maintenance_date' => Carbon::now()->addDays(2)->toDateString(),
    ];

    $response = $this->postToTenant('api.assets.store', $formData);
    $response->assertSessionHasNoErrors();

    assertDatabaseHas('maintainables', [
        'maintainable_type' => Asset::class,
        'maintainable_id' => Asset::first()->id,
        'maintenance_frequency' => $frequency,
        'need_maintenance' => true,
        'last_maintenance_date' => '2025-05-05',
        'next_maintenance_date' => Carbon::now()->addDays(2)->toDateString()
    ]);
})->with(array_column(MaintenanceFrequency::cases(), 'value'));

it('can update maintenance frequency/date from asset', function (string $frequency) {

    $asset = Asset::factory()->forLocation($this->site)->create();

    $formData = [
        ...$this->basicAssetData,
        'maintenance_frequency' => $frequency,
        'need_maintenance' => true,
        'last_maintenance_date' => '2025-05-05',
        'next_maintenance_date' => Carbon::now()->addDays(15)->toDateString(),
    ];

    $response = $this->patchToTenant('api.assets.update', $formData, $asset->reference_code);
    $response->assertSessionHasNoErrors();

    assertDatabaseHas('maintainables', [
        'maintainable_type' => Asset::class,
        'maintainable_id' => $asset->id,
        'maintenance_frequency' => $frequency,
        'need_maintenance' => true,
        'last_maintenance_date' => '2025-05-05',
        'next_maintenance_date' => Carbon::now()->addDays(15)->toDateString()
    ]);
})->with(array_column(MaintenanceFrequency::cases(), 'value'));
