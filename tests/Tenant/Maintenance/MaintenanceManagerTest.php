<?php


use Carbon\Carbon;
use App\Models\LocationType;
use App\Models\Central\CategoryType;
use App\Models\Tenants\Room;
use App\Models\Tenants\Site;
use App\Models\Tenants\User;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Floor;
use App\Models\Tenants\Building;

use App\Models\Tenants\Document;
use Illuminate\Http\UploadedFile;
use function Pest\Laravel\assertDatabaseHas;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertCount;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseMissing;
use function PHPUnit\Framework\assertNotNull;

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
});


it('can add a maintenance manager when creating asset', function () {

    $formData = [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'locationId' => $this->site->id,
        'locationType' => 'site',
        'locationReference' => $this->site->reference_code,
        'surface' => 12,
        'categoryId' => $this->categoryType->id,
        'maintenance_manager_id' => $this->manager->id
    ];

    $response = $this->postToTenant('tenant.assets.store', $formData);
    $response->assertSessionHasNoErrors();

    $asset = Asset::latest('id')->first();
    assertNotNull($asset->maintainable->manager);
    assertEquals($this->manager->id, $asset->maintainable->manager->id);
    assertDatabaseHas('maintainables', [
        'id' => $asset->maintainable->id,
        'maintenance_manager_id' => $this->manager->id
    ]);
});

it('can update a maintenance manager on existing asset', function () {

    $asset = Asset::factory()->forLocation($this->room)->create();

    $formData = [
        'name' => 'New asset',
        'categoryId' => $asset->assetCategory->id,
        'maintenance_manager_id' => $this->manager->id
    ];

    $response = $this->patchToTenant('tenant.assets.update', $formData, $asset->reference_code);
    $response->assertSessionHasNoErrors();

    $asset = Asset::find($asset->id);
    assertNotNull($asset->maintainable->manager);
    assertEquals($this->manager->id, $asset->maintainable->manager->id);
    assertDatabaseHas('maintainables', [
        'id' => $asset->maintainable->id,
        'maintenance_manager_id' => $this->manager->id
    ]);
});

it('can add a maintenance manager when creating site', function () {

    $formData = [
        'name' => 'New site',
        'description' => 'Description new site',
        'locationType' => $this->siteType->id,
        'maintenance_manager_id' => $this->manager->id
    ];

    $response = $this->postToTenant('tenant.sites.store', $formData);
    $response->assertSessionHasNoErrors();

    $location = Site::latest('id')->first();
    assertNotNull($location->maintainable->manager);
    assertEquals($this->manager->id, $location->maintainable->manager->id);
    assertDatabaseHas('maintainables', [
        'id' => $location->maintainable->id,
        'maintenance_manager_id' => $this->manager->id
    ]);
});

it('can update a maintenance manager of an existing site', function () {

    $formData = [
        'name' => 'New site',
        'locationType' => $this->siteType->id,
        'maintenance_manager_id' => $this->manager->id
    ];

    $response = $this->patchToTenant('tenant.sites.update', $formData,  $this->site->reference_code);
    $response->assertSessionHasNoErrors();

    $location = Site::latest('id')->first();
    assertNotNull($location->maintainable->manager);
    assertEquals($this->manager->id, $location->maintainable->manager->id);
    assertDatabaseHas('maintainables', [
        'id' => $location->maintainable->id,
        'maintenance_manager_id' => $this->manager->id
    ]);
});


it('can add a maintenance manager when creating building', function () {

    $formData = [
        'name' => 'New building',
        'surface_floor' => 2569.12,
        'surface_walls' => 256.9,
        'description' => 'Description new building',
        'levelType' => $this->site->id,
        'locationType' => $this->buildingType->id,
        'maintenance_manager_id' => $this->manager->id
    ];

    $response = $this->postToTenant('tenant.buildings.store', $formData);
    $response->assertSessionHasNoErrors();

    $location = Building::latest('id')->first();
    assertNotNull($location->maintainable->manager);
    assertEquals($this->manager->id, $location->maintainable->manager->id);
    assertDatabaseHas('maintainables', [
        'id' => $location->maintainable->id,
        'maintenance_manager_id' => $this->manager->id
    ]);
});


it('can update a maintenance manager of existing building', function () {

    $formData = [
        'name' => $this->building->name,
        'levelType' => $this->site->id,
        'locationType' => $this->buildingType->id,
        'maintenance_manager_id' => $this->manager->id
    ];

    $response = $this->patchToTenant('tenant.buildings.update', $formData, $this->building->reference_code);
    $response->assertSessionHasNoErrors();

    $location = Building::latest('id')->first();
    assertNotNull($location->maintainable->manager);
    assertEquals($this->manager->id, $location->maintainable->manager->id);
    assertDatabaseHas('maintainables', [
        'id' => $location->maintainable->id,
        'maintenance_manager_id' => $this->manager->id
    ]);
});


it('can add a maintenance manager when creating floor', function () {
    $formData = [
        'name' => 'New floor',
        'description' => 'Description new floor',
        'levelType' => $this->building->id,
        'locationType' => $this->floorType->id,
        'maintenance_manager_id' => $this->manager->id
    ];

    $response = $this->postToTenant('tenant.floors.store', $formData);

    $response->assertSessionHasNoErrors();

    $location = Floor::latest('id')->first();
    assertNotNull($location->maintainable->manager);
    assertEquals($this->manager->id, $location->maintainable->manager->id);
    assertDatabaseHas('maintainables', [
        'id' => $location->maintainable->id,
        'maintenance_manager_id' => $this->manager->id
    ]);
});


it('can update a maintenance manager of existing floor', function () {

    $formData = [
        'name' => $this->building->name,
        'levelType' => $this->building->id,
        'locationType' => $this->floorType->id,
        'maintenance_manager_id' => $this->manager->id
    ];

    $response = $this->patchToTenant('tenant.floors.update', $formData, $this->floor->reference_code);
    $response->assertSessionHasNoErrors();

    $location = Floor::latest('id')->first();
    assertNotNull($location->maintainable->manager);
    assertEquals($this->manager->id, $location->maintainable->manager->id);
    assertDatabaseHas('maintainables', [
        'id' => $location->maintainable->id,
        'maintenance_manager_id' => $this->manager->id
    ]);
});


it('can add a maintenance manager when creating room', function () {
    $formData = [
        'name' => 'New room',
        'description' => 'Description new room',
        'levelType' => $this->floor->id,
        'locationType' => $this->roomType->id,
        'maintenance_manager_id' => $this->manager->id
    ];

    $response = $this->postToTenant('tenant.rooms.store', $formData);

    $response->assertSessionHasNoErrors();

    $location = Room::latest('id')->first();
    assertNotNull($location->maintainable->manager);
    assertEquals($this->manager->id, $location->maintainable->manager->id);
    assertDatabaseHas('maintainables', [
        'id' => $location->maintainable->id,
        'maintenance_manager_id' => $this->manager->id
    ]);
});


it('can update a maintenance manager of existing room', function () {

    $formData = [
        'name' => $this->room->name,
        'levelType' => $this->room->id,
        'locationType' => $this->roomType->id,
        'maintenance_manager_id' => $this->manager->id
    ];

    $response = $this->patchToTenant('tenant.rooms.update', $formData, $this->room->reference_code);
    $response->assertSessionHasNoErrors();

    $location = Room::latest('id')->first();

    assertNotNull($location->maintainable->manager);
    assertEquals($this->manager->id, $location->maintainable->manager->id);
    assertDatabaseHas('maintainables', [
        'id' => $location->maintainable->id,
        'maintenance_manager_id' => $this->manager->id
    ]);
});
