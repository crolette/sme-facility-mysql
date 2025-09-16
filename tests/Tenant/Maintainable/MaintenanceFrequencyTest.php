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


it('can add maintenance frequency to asset without next_maintenance_date', function () {

    $formData = [
        ...$this->basicAssetData,
        'maintenance_frequency' => 'monthly',
        'need_maintenance' => true,
        'last_maintenance_date' => '2025-05-05'
    ];

    $this->postToTenant('api.assets.store', $formData);

    assertDatabaseHas('maintainables', [
        'maintenance_frequency' => 'monthly',
        'need_maintenance' => true,
        'last_maintenance_date' => '2025-05-05',
        'next_maintenance_date' => Carbon::now()->addDays(MaintenanceFrequency::from('monthly')->days())->toDateString()
    ]);
});

it('can add maintenance frequency to asset with next_maintenance_date', function () {

    $formData = [
        ...$this->basicAssetData,
        'maintenance_frequency' => 'monthly',
        'need_maintenance' => true,
        'next_maintenance_date' => '2025-10-10',
        'last_maintenance_date' => '2025-05-05',
    ];

    $this->postToTenant('api.assets.store', $formData);

    assertDatabaseHas('maintainables', [
        'maintenance_frequency' => 'monthly',
        'need_maintenance' => true,
        'last_maintenance_date' => '2025-05-05',
        'next_maintenance_date' => '2025-10-10'
    ]);
});

it('can update maintenance frequency/date from asset', function () {

    $asset = Asset::factory()->forLocation($this->site)->create();

    $formData = [
        ...$this->basicAssetData,
        'maintenance_frequency' => 'monthly',
        'need_maintenance' => true,
        'next_maintenance_date' => '2025-10-10',
        'last_maintenance_date' => '2025-05-05',
    ];

    $this->patchToTenant('api.assets.update', $formData, $asset->reference_code);

    assertDatabaseHas('maintainables', [
        'maintenance_frequency' => 'monthly',
        'need_maintenance' => true,
        'last_maintenance_date' => '2025-05-05',
        'next_maintenance_date' => '2025-10-10'
    ]);
});
