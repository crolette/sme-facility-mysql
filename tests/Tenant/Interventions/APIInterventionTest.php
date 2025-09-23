<?php

use Carbon\Carbon;
use App\Models\LocationType;
use App\Models\Tenants\Room;
use App\Models\Tenants\Site;
use App\Models\Tenants\User;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Floor;
use App\Models\Tenants\Ticket;
use App\Models\Tenants\Building;

use App\Models\Central\CategoryType;
use App\Models\Tenants\Intervention;
use function Pest\Laravel\assertDatabaseHas;
use function PHPUnit\Framework\assertEquals;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseMissing;

beforeEach(function () {
    LocationType::factory()->create(['level' => 'site']);
    LocationType::factory()->create(['level' => 'building']);
    LocationType::factory()->create(['level' => 'floor']);
    LocationType::factory()->create(['level' => 'room']);
    CategoryType::factory()->create(['category' => 'asset']);

    $this->user = User::factory()->withRole('Admin')->create();
    $this->actingAs($this->user, 'tenant');
    $this->interventionType = CategoryType::factory()->create(['category' => 'intervention']);
    $this->interventionActionType = CategoryType::factory()->create(['category' => 'action']);
    $this->site = Site::factory()->create();
    $this->building = Building::factory()->create();
    $this->floor = Floor::factory()->create();

    $this->room = Room::factory()
        ->for(LocationType::where('level', 'room')->first())
        ->for(Floor::first())
        ->create();

    $this->asset =  Asset::factory()->forLocation($this->room)->create();
    $this->asset->refresh();
    $this->ticket = Ticket::factory()->forLocation($this->asset)->create();
});

it('can get an intervention', function() {
    $intervention = Intervention::factory()->forLocation($this->asset)->create();
    $response = $this->getFromTenant('api.interventions.show', $intervention->id);
    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
            'data' => ['id' => $intervention->id, 'interventionable' => ['id' => $this->asset->id]]
        ]);

});

it('can get an intervention with ticket', function () {
    $intervention = Intervention::factory()->forTicket($this->ticket)->create();
    $response = $this->getFromTenant('api.interventions.show', $intervention->id);
    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
            'data' => ['id' => $intervention->id, 'ticket' => ['id' => $this->ticket->id]]
        ]);
});

it('can get all interventions for an ASSET', function () {
    Intervention::factory()->forLocation($this->asset)->count(2)->create();
    $response = $this->getFromTenant('api.assets.interventions', $this->asset->reference_code);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
        ])
        ->assertJsonCount(2, 'data');
});

it('can get all interventions for a SITE', function () {
    Intervention::factory()->forLocation($this->site)->count(2)->create();
    $response = $this->getFromTenant('api.sites.interventions', $this->site);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
        ])
        ->assertJsonCount(2, 'data');
});

it('can get all interventions for a BUILDING', function () {
    Intervention::factory()->forLocation($this->building)->count(2)->create();
    $response = $this->getFromTenant('api.buildings.interventions', $this->building);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
        ])
        ->assertJsonCount(2, 'data');
});


it('can get all interventions for a FLOOR', function () {
    Intervention::factory()->forLocation($this->floor)->count(2)->create();
    $response = $this->getFromTenant('api.floors.interventions', $this->floor);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
        ])
        ->assertJsonCount(2, 'data');
});

it('can get all interventions for a ROOM', function () {
    Intervention::factory()->forLocation($this->room)->count(2)->create();
    $response = $this->getFromTenant('api.rooms.interventions', $this->room->reference_code);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
        ])
        ->assertJsonCount(2, 'data');
});


