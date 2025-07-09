<?php

use App\Models\User;
use App\Models\LocationType;
use App\Models\Tenants\Site;
use App\Models\Tenants\Building;
use App\Models\Tenants\Floor;
use App\Models\Tenants\Room;

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
    LocationType::factory()->count(1)->create(['level' => 'room']);
    Site::factory()->create();
    Building::factory()->create();
    $floor = Floor::factory()->create();

    $room = Room::factory()
        ->for(LocationType::where('level', 'room')->first())
        ->for(Floor::first())
        ->create();
    Room::factory()
        ->for(LocationType::where('level', 'room')->first())
        ->for(Floor::first())
        ->create();

    $response = $this->getFromTenant('tenant.rooms.index');
    $response->assertOk();


    $response->assertInertia(
        fn($page) =>
        $page->component('tenants/locations/index')
            ->has('locations', 2)
            ->has('locations.0.maintainable')
            ->where('locations.0.floor.id', $room->floor->id)
    );
});


it('can render the create floor page', function () {

    $this->actingAs($user = User::factory()->create());

    LocationType::factory()->count(1)->create(['level' => 'site']);
    LocationType::factory()->count(1)->create(['level' => 'building']);
    LocationType::factory()->count(1)->create(['level' => 'floor']);
    LocationType::factory()->count(2)->create(['level' => 'room']);

    Site::factory()->create();
    Building::factory()->create();
    Floor::factory()->count(4)->create();


    $response = $this->getFromTenant('tenant.rooms.create');
    $response->assertOk();


    $response->assertInertia(
        fn($page) => $page->component('tenants/locations/create')
            ->has('levelTypes', 4)
            ->has('levelTypes.0.maintainable.name')
            ->has('locationTypes', 2)
    );
});


it('can create a new room', function () {

    $this->actingAs($user = User::factory()->create());

    LocationType::factory()->create(['level' => 'site']);
    LocationType::factory()->create(['level' => 'building']);
    LocationType::factory()->create(['level' => 'floor']);
    $location = LocationType::factory()->create(['level' => 'room']);
    Site::factory()->create();
    Building::factory()->create();
    $floor = Floor::factory()->create();

    $formData = [
        'name' => 'New room',
        'description' => 'Description new room',
        'levelType' => $floor->id,
        'locationType' => $location->id
    ];

    $response = $this->postToTenant('tenant.rooms.store', $formData);
    $response->assertStatus(302);
    $response->assertSessionHasNoErrors();

    $room = Room::first();

    assertDatabaseCount('sites', 1);
    assertDatabaseCount('buildings', 1);
    assertDatabaseCount('floors', 1);
    assertDatabaseCount('rooms', 1);
    assertDatabaseCount('maintainables', 4);


    assertDatabaseHas('rooms', [
        'location_type_id' => $location->id,
        'code' => $location->prefix . '001',
        'reference_code' => $floor->reference_code . '-' . $location->prefix . '001',
        'level_id' => $floor->id
    ]);

    assertDatabaseHas('maintainables', [
        'maintainable_type' => get_class($room),
        'maintainable_id' => $room->id,
        'name' => 'New room',
        'description' => 'Description new room',
    ]);
});

it('can render the show room page', function () {

    $this->actingAs($user = User::factory()->create());

    LocationType::factory()->create(['level' => 'site']);
    LocationType::factory()->create(['level' => 'building']);
    LocationType::factory()->create(['level' => 'floor']);
    LocationType::factory()->create(['level' => 'room']);
    Site::factory()->create();
    Building::factory()->create();
    Floor::factory()->create();
    $room = Room::factory()
        ->for(LocationType::where('level', 'room')->first())
        ->for(Floor::first())
        ->create();

    $response = $this->getFromTenant('tenant.rooms.show', $room);
    $response->assertOk();

    $response->assertInertia(
        fn($page) => $page->component('tenants/locations/show')
            ->has('location')
            ->where('location.location_type.level', $room->locationType->level)
            ->where('location.maintainable.description', $room->maintainable->description)
            ->where('location.code', $room->code)
            ->where('location.reference_code', $room->reference_code)
            ->where('location.location_type.level', 'room')
    );
});


it('can render the update floor page', function () {

    $this->actingAs($user = User::factory()->create());

    LocationType::factory()->create(['level' => 'site']);
    LocationType::factory()->count(1)->create(['level' => 'building']);
    LocationType::factory()->count(1)->create(['level' => 'floor']);
    LocationType::factory()->count(3)->create(['level' => 'room']);
    Site::factory()->create();
    Building::factory()->create();
    Floor::factory()->count(3)->create();
    $room = Room::factory()
        ->for(LocationType::where('level', 'room')->first())
        ->for(Floor::first())
        ->create();

    $response = $this->getFromTenant('tenant.rooms.edit', $room);
    $response->assertOk();


    $response->assertInertia(
        fn($page) => $page->component('tenants/locations/create')
            ->has('location')
            ->has('location.floor')
            ->has('levelTypes', 3)
            ->has('locationTypes', 3)
            ->where('location.reference_code', $room->reference_code)
    );
});


it('can update a room maintainable', function () {

    $this->actingAs($user = User::factory()->create());

    LocationType::factory()->create(['level' => 'site']);
    LocationType::factory()->create(['level' => 'building']);
    LocationType::factory()->create(['level' => 'floor']);
    $locationType = LocationType::factory()->create(['level' => 'room']);
    Site::factory()->create();
    Building::factory()->create();
    $floor = Floor::factory()->create();
    $room = Room::factory()
        ->for(LocationType::where('level', 'room')->first())
        ->for(Floor::first())
        ->create();

    $oldName = $room->maintainable->name;
    $oldDescription = $room->maintainable->description;

    $formData = [
        'name' => 'New room',
        'description' => 'Description new room',
        'levelType' => $floor->id,
        'locationType' => $locationType->id
    ];

    $response = $this->patchToTenant('tenant.rooms.update', $formData, $room);
    $response->assertStatus(302);
    $response->assertSessionHasNoErrors();

    assertDatabaseCount('sites', 1);
    assertDatabaseCount('buildings', 1);
    assertDatabaseCount('floors', 1);
    assertDatabaseCount('rooms', 1);
    assertDatabaseCount('maintainables', 4);

    assertDatabaseHas('rooms', [
        'location_type_id' => $locationType->id,
        'level_id' => $floor->id,
        'code' => $locationType->prefix . '001',
        'reference_code' => $floor->reference_code . '-' . $locationType->prefix . '001',
    ]);

    assertDatabaseHas('maintainables', [
        'name' => 'New room',
        'description' => 'Description new room',
    ]);

    assertDatabaseMissing('maintainables', [
        'name' => $oldName,
        'description' => $oldDescription,
    ]);
});


it('cannot update a room type of an existing room', function () {

    $this->actingAs($user = User::factory()->create());

    LocationType::factory()->create(['level' => 'site']);
    LocationType::factory()->create(['level' => 'building']);
    LocationType::factory()->create(['level' => 'floor']);
    LocationType::factory()->count(2)->create(['level' => 'room']);
    Site::factory()->create();
    Building::factory()->create();
    $floor = Floor::factory()->create();
    $room = Room::factory()
        ->for(LocationType::where('level', 'room')->first())
        ->for(Floor::first())
        ->create();

    $formData = [
        'name' => 'New site',
        'description' => 'Description new site',
        'levelType' => $floor->level_id,
        'locationType' => 5
    ];

    $response = $this->patchToTenant('tenant.rooms.update', $formData, $room);
    $response->assertRedirect();
    $response->assertSessionHasErrors([
        'locationType' => 'You cannot change the type of a location',
    ]);
});


it('can delete a room and his maintainable', function () {

    $this->actingAs($user = User::factory()->create());

    LocationType::factory()->create(['level' => 'site']);
    LocationType::factory()->create(['level' => 'building']);
    LocationType::factory()->create(['level' => 'floor']);
    LocationType::factory()->create(['level' => 'room']);
    Site::factory()->create();
    Building::factory()->create();
    $floor = Floor::factory()->create();
    $room = Room::factory()
        ->for(LocationType::where('level', 'room')->first())
        ->for(Floor::first())
        ->create();

    assertDatabaseHas('rooms', [
        'level_id' => $floor->id,
        'code' => $room->code
    ]);

    $response = $this->deleteFromTenant('tenant.rooms.destroy', $room->id);
    $response->assertStatus(302);
    assertDatabaseMissing('rooms', [
        'reference_code' => $room->reference_code
    ]);

    assertDatabaseMissing('maintainables', [
        'maintainable_type' => get_class($room),
        'maintainable_id' => $room->id
    ]);

    assertDatabaseCount('sites', 1);
    assertDatabaseCount('buildings', 1);
    assertDatabaseCount('floors', 1);
    assertDatabaseEmpty('rooms');
    assertDatabaseCount('maintainables', 3);
});
