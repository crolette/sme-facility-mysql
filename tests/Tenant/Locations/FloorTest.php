<?php

use App\Models\Tenants\User;
use App\Models\LocationType;
use App\Models\Tenants\Site;
use App\Models\Tenants\Building;
use App\Models\Tenants\Floor;

use function Pest\Laravel\assertDatabaseHas;
use function PHPUnit\Framework\assertEquals;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\assertDatabaseEmpty;


it('can render the index floors page', function () {
    $this->actingAs($user = User::factory()->create());

    LocationType::factory()->count(1)->create(['level' => 'site']);
    LocationType::factory()->count(1)->create(['level' => 'building']);
    LocationType::factory()->count(1)->create(['level' => 'floor']);
    Site::factory()->create();
    Building::factory()->create();
    Floor::factory()->count(3)->create();
    $response = $this->getFromTenant('tenant.floors.index');
    $response->assertOk();


    $response->assertInertia(
        fn($page) =>
        $page->component('tenants/locations/index')
            ->has('locations', 3)
            ->has('locations.0.maintainable')
    );
});


it('can render the create floor page', function () {

    $this->actingAs($user = User::factory()->create());

    LocationType::factory()->count(1)->create(['level' => 'site']);
    LocationType::factory()->count(1)->create(['level' => 'building']);
    LocationType::factory()->count(3)->create(['level' => 'floor']);

    Site::factory()->create();
    Building::factory()->count(2)->create();


    $response = $this->getFromTenant('tenant.floors.create');
    $response->assertOk();


    $response->assertInertia(
        fn($page) => $page->component('tenants/locations/create')
            ->has('levelTypes', 2)
            ->has('locationTypes', 3)
    );
});


it('can create a new floor', function () {

    $this->actingAs($user = User::factory()->create());

    LocationType::factory()->create(['level' => 'site']);
    $siteType = LocationType::factory()->create(['level' => 'building']);
    $location = LocationType::factory()->create(['level' => 'floor']);
    Site::factory()->create();
    $building = Building::factory()->create();

    $formData = [
        'name' => 'New floor',
        'description' => 'Description new floor',
        'levelType' => $building->id,
        'locationType' => $location->id
    ];

    $response = $this->postToTenant('tenant.floors.store', $formData);
    $response->assertStatus(302);
    $response->assertSessionHasNoErrors();

    $floor = Floor::first();

    assertDatabaseCount('sites', 1);
    assertDatabaseCount('buildings', 1);
    assertDatabaseCount('floors', 1);
    assertDatabaseCount('maintainables', 3);


    assertDatabaseHas('floors', [
        'location_type_id' => $location->id,
        'code' => $location->prefix . '01',
        'reference_code' => $building->reference_code . '-' . $location->prefix . '01',
        'level_id' => $building->id
    ]);

    assertDatabaseHas('maintainables', [
        'maintainable_type' => get_class($floor),
        'maintainable_id' => $floor->id,
        'name' => 'New floor',
        'description' => 'Description new floor',
    ]);
});

it('can render the show floor page', function () {

    $this->actingAs($user = User::factory()->create());

    LocationType::factory()->create(['level' => 'site']);
    LocationType::factory()->create(['level' => 'building']);
    LocationType::factory()->create(['level' => 'floor']);
    Site::factory()->create();
    Building::factory()->create();
    $floor = Floor::factory()->create();

    $response = $this->getFromTenant('tenant.floors.show', $floor);
    $response->assertOk();

    $response->assertInertia(
        fn($page) => $page->component('tenants/locations/show')
            ->has('location')
            ->where('location.location_type.level', $floor->locationType->level)
            ->where('location.maintainable.description', $floor->maintainable->description)
            ->where('location.code', $floor->code)
            ->where('location.reference_code', $floor->reference_code)
            ->where('location.location_type.level', 'floor')
    );
});


it('can render the update floor page', function () {

    $this->actingAs($user = User::factory()->create());

    LocationType::factory()->create(['level' => 'site']);
    LocationType::factory()->count(3)->create(['level' => 'building']);
    LocationType::factory()->count(3)->create(['level' => 'floor']);
    Site::factory()->create();
    Building::factory()->count(3)->create();
    $floor = Floor::factory()->create();

    $response = $this->getFromTenant('tenant.floors.edit', $floor);
    $response->assertOk();


    $response->assertInertia(
        fn($page) => $page->component('tenants/locations/create')
            ->has('location')
            ->has('location.building')
            ->has('levelTypes', 3)
            ->has('locationTypes', 3)
            ->where('location.reference_code', $floor->reference_code)
    );
});


it('can update a floor maintainable', function () {

    $this->actingAs($user = User::factory()->create());

    LocationType::factory()->create(['level' => 'site']);
    $buildingType = LocationType::factory()->create(['level' => 'building']);
    $locationType = LocationType::factory()->create(['level' => 'floor']);
    $site = Site::factory()->create();
    $building = Building::factory()->create();

    $floor = Floor::factory()->create();

    $oldName = $floor->maintainable->name;
    $oldDescription = $floor->maintainable->description;

    $formData = [
        'name' => 'New floor',
        'description' => 'Description new floor',
        'levelType' => $building->id,
        'locationType' => $locationType->id
    ];

    $response = $this->patchToTenant('tenant.floors.update', $formData, $floor);
    $response->assertStatus(302);
    $response->assertSessionHasNoErrors();

    assertDatabaseCount('sites', 1);
    assertDatabaseCount('buildings', 1);
    assertDatabaseCount('floors', 1);
    assertDatabaseCount('maintainables', 3);

    assertDatabaseHas('floors', [
        'location_type_id' => $locationType->id,
        'level_id' => $building->id,
        'code' => $locationType->prefix . '01',
        'reference_code' => $building->reference_code . '-' . $locationType->prefix . '01',
    ]);

    assertDatabaseHas('maintainables', [
        'name' => 'New floor',
        'description' => 'Description new floor',
    ]);

    assertDatabaseMissing('maintainables', [
        'name' => $oldName,
        'description' => $oldDescription,
    ]);
});

it('cannot update a floor type of an existing floor', function () {

    $this->actingAs($user = User::factory()->create());

    LocationType::factory()->create(['level' => 'site']);
    LocationType::factory()->count(2)->create(['level' => 'building']);
    LocationType::factory()->count(3)->create(['level' => 'floor']);
    Site::factory()->create();
    $building = Building::factory()->create();
    $floor = Floor::factory()->create();

    $formData = [
        'name' => 'New site',
        'description' => 'Description new site',
        'levelType' => $building->level_id,
        'locationType' => 5
    ];

    $response = $this->patchToTenant('tenant.floors.update', $formData, $floor);
    $response->assertRedirect();
    $response->assertSessionHasErrors([
        'locationType' => 'You cannot change the floor type of a location',
    ]);
});


it('can delete a floor and his maintainable', function () {

    $this->actingAs($user = User::factory()->create());

    LocationType::factory()->create(['level' => 'site']);
    LocationType::factory()->create(['level' => 'building']);
    LocationType::factory()->create(['level' => 'floor']);
    $site = Site::factory()->create();
    $building = Building::factory()->create();
    $floor = Floor::factory()->create();

    assertDatabaseHas('floors', [
        'level_id' => $building->id,
        'code' => $floor->code
    ]);

    $response = $this->deleteFromTenant('tenant.floors.destroy', $floor->id);
    $response->assertStatus(302);
    assertDatabaseMissing('floors', [
        'reference_code' => $floor->reference_code
    ]);

    assertDatabaseMissing('maintainables', [
        'maintainable_type' => get_class($floor),
        'maintainable_id' => $floor->id
    ]);

    assertDatabaseCount('sites', 1);
    assertDatabaseCount('buildings', 1);
    assertDatabaseEmpty('floors');
    assertDatabaseCount('maintainables', 2);
});
