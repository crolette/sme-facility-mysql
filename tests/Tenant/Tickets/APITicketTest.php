<?php

use App\Enums\TicketStatus;
use App\Models\LocationType;
use App\Models\Tenants\Room;
use App\Models\Tenants\Site;
use App\Models\Tenants\User;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Floor;
use App\Models\Tenants\Ticket;

use App\Models\Tenants\Building;
use Illuminate\Http\UploadedFile;
use App\Models\Central\CategoryType;
use function Pest\Laravel\assertDatabaseHas;
use function PHPUnit\Framework\assertEquals;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseMissing;

beforeEach(function () {
    $this->user = User::factory()->withRole('Admin')->create();
    $this->actingAs($this->user, 'tenant');
    LocationType::factory()->create(['level' => 'site']);
    LocationType::factory()->create(['level' => 'building']);
    LocationType::factory()->create(['level' => 'floor']);
    LocationType::factory()->create(['level' => 'room']);
    CategoryType::factory()->count(2)->create(['category' => 'document']);
    $this->categoryType = CategoryType::factory()->create(['category' => 'asset']);
    CategoryType::factory()->count(2)->create(['category' => 'asset']);
    $this->site = Site::factory()->withMaintainableData()->create();
    $this->building = Building::factory()->withMaintainableData()->create();
    $this->floor = Floor::factory()->withMaintainableData()->create();

    $this->room = Room::factory()->withMaintainableData()
        ->for(LocationType::where('level', 'room')->first())
        ->for(Floor::first())
        ->create();

    $this->asset =  Asset::factory()->withMaintainableData()->forLocation($this->room)->create();
});

it('can retrieve all tickets ', function () {
    Ticket::factory()->forLocation($this->room)->create(['reported_by' => $this->user->id]);
    Ticket::factory()->forLocation($this->site)->create(['reported_by' => $this->user->id]);
    Ticket::factory()->forLocation($this->floor)->create(['reported_by' => $this->user->id]);
    Ticket::factory()->forLocation($this->asset)->create(['reported_by' => $this->user->id]);

    $response = $this->getFromTenant('api.tickets.index');

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
        ])
        ->assertJsonCount(4, 'data');
});

it('can retrieve all tickets from a site', function () {
    Ticket::factory()->forLocation($this->site)->create(['reported_by' => $this->user->id]);
    Ticket::factory()->forLocation($this->site)->create(['reported_by' => $this->user->id]);
    Ticket::factory()->forLocation($this->site)->create(['reported_by' => $this->user->id]);

    $response = $this->getFromTenant('api.sites.tickets', $this->site->reference_code);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
        ])
        ->assertJsonCount(3, 'data');
});

it('can retrieve all tickets from a building', function () {
    Ticket::factory()->forLocation($this->building)->create(['reported_by' => $this->user->id]);
    Ticket::factory()->forLocation($this->building)->create(['reported_by' => $this->user->id]);
    Ticket::factory()->forLocation($this->building)->create(['reported_by' => $this->user->id]);

    $response = $this->getFromTenant('api.buildings.tickets', $this->building->reference_code);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
        ])
        ->assertJsonCount(3, 'data');
});

it('can retrieve all tickets from a floor', function () {
    Ticket::factory()->forLocation($this->floor)->create(['reported_by' => $this->user->id]);
    Ticket::factory()->forLocation($this->floor)->create(['reported_by' => $this->user->id]);
    Ticket::factory()->forLocation($this->floor)->create(['reported_by' => $this->user->id]);

    $response = $this->getFromTenant('api.floors.tickets', $this->floor->reference_code);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
        ])
        ->assertJsonCount(3, 'data');
});

it('can retrieve all tickets from a room', function () {
    Ticket::factory()->forLocation($this->room)->create(['reported_by' => $this->user->id]);
    Ticket::factory()->forLocation($this->room)->create(['reported_by' => $this->user->id]);
    Ticket::factory()->forLocation($this->room)->create(['reported_by' => $this->user->id]);

    $response = $this->getFromTenant('api.rooms.tickets', $this->room->reference_code);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
        ])
        ->assertJsonCount(3, 'data');
});
