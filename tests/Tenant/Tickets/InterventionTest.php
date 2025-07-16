<?php

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
    $this->categoryType = CategoryType::factory()->create(['category' => 'intervention']);
    $this->site = Site::factory()->create();
    $this->building = Building::factory()->create();
    $this->floor = Floor::factory()->create();

    $this->room = Room::factory()
        ->for(LocationType::where('level', 'room')->first())
        ->for(Floor::first())
        ->create();

    $this->asset =  Asset::factory()->forLocation($this->room)->create();
    $this->user = User::factory()->create();
    $this->actingAs($this->user, 'tenant');
    $this->ticket = Ticket::factory()->forLocation($this->asset)->create();
});

it('can factory intervention', function () {
    Intervention::factory()->forLocation($this->asset)->create();
    assertDatabaseCount('interventions', 1);
});

it('can render interventions in the ticket page', function () {
    Intervention::factory()->forLocation($this->asset)->count(2)->create();
    // Intervention::factory()->forLocation($this->asset)->create();
    assertDatabaseCount('interventions', 2);

    $response = $this->getFromTenant('tenant.tickets.show', $this->ticket);
    $response->assertOk();

    $response->assertInertia(
        fn($page) =>
        $page->component('tenants/tickets/show')
            ->has('ticket')
            ->has('ticket.interventions', 2)
    );
});


it('sum intervention costs automatically based on actions', function () {});
