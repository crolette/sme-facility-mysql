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

use App\Models\Tenants\Provider;
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
    CategoryType::factory()->create(['category' => 'provider']);

    $this->user = User::factory()->withRole('Admin')->create();
    $this->actingAs($this->user, 'tenant');
    $this->interventionType = CategoryType::factory()->create(['category' => 'intervention']);
    $this->interventionActionType = CategoryType::factory()->create(['category' => 'action']);
    $this->site = Site::factory()->withMaintainableData()->create();
    $this->building = Building::factory()->withMaintainableData()->create();
    $this->floor = Floor::factory()->withMaintainableData()->create();

    $this->room = Room::factory()->withMaintainableData()->create();

    $this->provider = Provider::factory()->create();

    $this->asset =  Asset::factory()->withMaintainableData()->forLocation($this->room)->create();
    $this->asset->refresh();
    $this->ticket = Ticket::factory()->forLocation($this->asset)->create();
});

it('can get an intervention', function () {
    $intervention = Intervention::factory()->withAction()->forLocation($this->asset)->create();
    $response = $this->getFromTenant('api.interventions.show', $intervention->id);
    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
            'data' => ['id' => $intervention->id, 'interventionable' => ['id' => $this->asset->id]]
        ]);
});


it('can retrieve providers linked to an intervention (asset) to select to which one to send', function () {

    $providers = Provider::factory()->count(2)->create();
    $this->asset->maintainable->providers()->sync($providers->pluck('id'));

    $intervention = Intervention::factory()->withAction()->forLocation($this->asset)->create();

    $response = $this->getFromTenant('api.interventions.providers', $intervention->id);
    $response->assertOk();

    $response->assertJson(['status' => 'success']);
    $response->assertJsonCount(2, 'data');
});

it('can get an intervention with ticket', function () {
    $intervention = Intervention::factory()->withAction()->forTicket($this->ticket)->create();
    $response = $this->getFromTenant('api.interventions.show', $intervention->id);
    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
            'data' => ['id' => $intervention->id, 'ticket' => ['id' => $this->ticket->id]]
        ]);
});

it('can get all interventions for an ASSET', function () {
    Intervention::factory()->withAction()->forLocation($this->asset)->count(2)->create();
    $response = $this->getFromTenant('api.assets.interventions', $this->asset->reference_code);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
        ])
        ->assertJsonCount(2, 'data');
});



it('can get all interventions for a SITE', function () {
    Intervention::factory()->withAction()->forLocation($this->site)->count(2)->create();
    $response = $this->getFromTenant('api.sites.interventions', $this->site);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
        ])
        ->assertJsonCount(2, 'data');
});

it('can get all interventions for a BUILDING', function () {
    Intervention::factory()->withAction()->forLocation($this->building)->count(2)->create();
    $response = $this->getFromTenant('api.buildings.interventions', $this->building);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
        ])
        ->assertJsonCount(2, 'data');
});


it('can get all interventions for a FLOOR', function () {
    Intervention::factory()->withAction()->forLocation($this->floor)->count(2)->create();
    $response = $this->getFromTenant('api.floors.interventions', $this->floor);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
        ])
        ->assertJsonCount(2, 'data');
});

it('can get all interventions for a ROOM', function () {
    Intervention::factory()->withAction()->forLocation($this->room)->count(2)->create();
    $response = $this->getFromTenant('api.rooms.interventions', $this->room->reference_code);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
        ])
        ->assertJsonCount(2, 'data');
});

it('can get all interventions directly linked to a PROVIDER', function () {
    Intervention::factory()->withAction()->forProvider($this->provider)->count(2)->create();
    $response = $this->getFromTenant('api.providers.interventions', $this->provider->id);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
        ])
        ->assertJsonCount(2, 'data');
});

it('can get all interventions directly linked to and assigned to a PROVIDER ', function () {
    Intervention::factory()->withAction()->forProvider($this->provider)->count(2)->create();
    $intervention = Intervention::factory()->withAction()->forProvider($this->room)->create();
    $intervention->assignable()->associate($this->provider)->save();

    $response = $this->getFromTenant('api.providers.interventions', $this->provider->id);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
        ])
        ->assertJsonCount(3, 'data');
});
