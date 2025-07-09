<?php

use App\Models\Tenants\User;
use App\Models\LocationType;
use App\Models\Tenants\Site;
use App\Models\Tenants\Floor;
use App\Models\Tenants\Building;
use function Pest\Laravel\assertDatabaseHas;
use function PHPUnit\Framework\assertEquals;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseMissing;

it('can render the index buildings page', function () {
    $this->actingAs($user = User::factory()->create());

    LocationType::factory()->count(1)->create(['level' => 'site']);
    LocationType::factory()->count(2)->create(['level' => 'building']);
    Site::factory()->create();
    Building::factory()->count(3)->create();
    $response = $this->getFromTenant('tenant.buildings.index');
    $response->assertOk();


    $response->assertInertia(
        fn($page) =>
        $page->component('tenants/locations/index')
            ->has('locations', 3)
            ->has('locations.0.maintainable')
    );
});


it('can render the create building page', function () {

    $this->actingAs($user = User::factory()->create());

    LocationType::factory()->count(1)->create(['level' => 'site']);
    LocationType::factory()->count(2)->create(['level' => 'building']);


    $response = $this->getFromTenant('tenant.buildings.create');
    $response->assertOk();


    $response->assertInertia(
        fn($page) => $page->component('tenants/locations/create')
            ->has('levelTypes')
            ->has('locationTypes', 2)
    );
});


it('can create a new building', function () {

    $this->actingAs($user = User::factory()->create());

    $siteType = LocationType::factory()->create(['level' => 'site']);
    $buildingType = LocationType::factory()->create(['level' => 'building']);
    $site = Site::factory()->create();

    $formData = [
        'name' => 'New building',
        'description' => 'Description new building',
        'levelType' => $site->id,
        'locationType' => $buildingType->id
    ];

    $response = $this->postToTenant('tenant.buildings.store', $formData);
    $response->assertStatus(302);
    $response->assertSessionHasNoErrors();

    assertDatabaseCount('buildings', 1);
    assertDatabaseCount('maintainables', 2);

    assertDatabaseHas('buildings', [
        'location_type_id' => $buildingType->id,
        'code' => $buildingType->prefix . '01',
        'reference_code' => $site->reference_code . '-' . $buildingType->prefix . '01',
        'level_id' => $siteType->id
    ]);

    assertDatabaseHas('maintainables', [
        'name' => 'New building',
        'description' => 'Description new building',
    ]);
});

it('can render the show building page', function () {

    $this->actingAs($user = User::factory()->create());

    LocationType::factory()->count(3)->create(['level' => 'site']);
    LocationType::factory()->count(3)->create(['level' => 'building']);
    Site::factory()->create();
    $building = Building::factory()->create();

    $response = $this->getFromTenant('tenant.buildings.show', $building);
    $response->assertOk();

    $response->assertInertia(
        fn($page) => $page->component('tenants/locations/show')
            ->has('location')
            ->where('location.location_type.level', $building->locationType->level)
            ->where('location.maintainable.description', $building->maintainable->description)
            ->where('location.code', $building->code)
            ->where('location.reference_code', $building->reference_code)
            ->where('location.location_type.level', 'building')
    );
});


it('can render the update building page', function () {

    $this->actingAs($user = User::factory()->create());

    LocationType::factory()->count(3)->create(['level' => 'site']);
    LocationType::factory()->count(3)->create(['level' => 'building']);
    $site = Site::factory()->create();
    $building = Building::factory()->create();

    $response = $this->getFromTenant('tenant.buildings.edit', $building);
    $response->assertOk();


    $response->assertInertia(
        fn($page) => $page->component('tenants/locations/create')
            ->has('location')
            ->has('location.site')
            ->has('levelTypes', 1)
            ->has('locationTypes', 3)
            ->where('location.reference_code', $building->reference_code)
    );
});


it('can update a building', function () {

    $this->actingAs($user = User::factory()->create());

    $level = LocationType::factory()->create(['level' => 'site']);
    $buildingType = LocationType::factory()->create(['level' => 'building']);
    $site = Site::factory()->create();
    $building = Building::factory()->create();

    $oldName = $building->maintainable->name;
    $oldDescription = $building->maintainable->description;

    $formData = [
        'name' => 'New building',
        'description' => 'Description new building',
        'levelType' => $level->id,
        'locationType' => $buildingType->id
    ];

    $response = $this->patchToTenant('tenant.buildings.update', $formData, $building);
    $response->assertStatus(302);
    $response->assertSessionHasNoErrors();


    assertDatabaseCount('sites', 1);
    assertDatabaseCount('buildings', 1);
    assertDatabaseCount('maintainables', 2);

    assertDatabaseHas('buildings', [
        'location_type_id' => $buildingType->id,
        'level_id' => $site->id,
        'code' => $buildingType->prefix . '01',
        'reference_code' => $site->code . '-' . $buildingType->prefix . '01',
    ]);

    assertDatabaseHas('maintainables', [
        'name' => 'New building',
        'description' => 'Description new building',
    ]);

    assertDatabaseMissing('maintainables', [
        'name' => $oldName,
        'description' => $oldDescription,
    ]);
});

it('cannot update a building type of an existing building', function () {

    $this->actingAs($user = User::factory()->create());

    LocationType::factory()->count(2)->create(['level' => 'site']);
    LocationType::factory()->count(2)->create(['level' => 'building']);
    Site::factory()->count(2)->create();
    $building = Building::factory()->create();

    $formData = [
        'name' => 'New building updated',
        'description' => 'Description new building updated',
        'levelType' => $building->level_id,
        'locationType' => 4
    ];

    $response = $this->patchToTenant('tenant.buildings.update', $formData, $building);
    $response->assertRedirect();
    $response->assertSessionHasErrors([
        'locationType' => 'You cannot change the building type of a location',
    ]);
});


it('can delete a building', function () {

    $this->actingAs($user = User::factory()->create());

    LocationType::factory()->create(['level' => 'site']);
    LocationType::factory()->create(['level' => 'building']);
    $site = Site::factory()->create();
    $building = Building::factory()->create();

    assertDatabaseHas('buildings', [
        'level_id' => $site->id,
        'code' => $building->code
    ]);

    $response = $this->deleteFromTenant('tenant.buildings.destroy', $building->id);
    $response->assertStatus(302);
    assertDatabaseMissing('buildings', [
        'reference_code' => $building->reference_code
    ]);

    assertDatabaseMissing('maintainables', [
        'maintainable_type' => get_class($building),
        'maintainable_id' => $building->id
    ]);

    assertDatabaseCount('sites', 1);
    assertDatabaseEmpty('buildings');
    assertDatabaseCount('maintainables', 1);
});


it('can delete a building and the related floors', function () {

    $this->actingAs($user = User::factory()->create());

    LocationType::factory()->create(['level' => 'site']);
    LocationType::factory()->create(['level' => 'building']);
    LocationType::factory()->create(['level' => 'floor']);
    $site = Site::factory()->create();
    $building = Building::factory()->create();
    Floor::factory()->count(2)->create();

    assertDatabaseHas('buildings', [
        'level_id' => $site->id,
        'code' => $building->code
    ]);

    assertDatabaseCount('sites', 1);
    assertDatabaseCount('buildings', 1);
    assertDatabaseCount('floors', 2);
    assertDatabaseCount('maintainables', 4);

    $response = $this->deleteFromTenant('tenant.buildings.destroy', $building->id);
    $response->assertStatus(302);

    assertDatabaseCount('sites', 1);
    assertDatabaseEmpty('buildings');
    assertDatabaseEmpty('floors');
    assertDatabaseCount('maintainables', 1);
});
