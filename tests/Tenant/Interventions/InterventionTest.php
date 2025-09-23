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

it('can factory intervention', function () {
    Intervention::factory()->forLocation($this->asset)->create();
    Intervention::factory()->forTicket($this->ticket)->create();
    assertDatabaseCount('interventions', 2);
    assertDatabaseCount('intervention_actions', 2);
});

it('shows the create intervention page for a ticket', function () {

    $response = $this->getFromTenant('tenant.interventions.create', $this->ticket);
    $response->assertOk();

    $response->assertInertia(
        fn($page) => $page->component('tenants/tickets/interventions/create')
            ->has('ticket')
            ->where('ticket.id', $this->ticket->id)
    );
});

it('shows an intervention page', function () {
    $intervention = Intervention::factory()->forLocation($this->asset)->create(['ticket_id' => $this->ticket->id]);

    $response = $this->getFromTenant('tenant.interventions.show', $intervention);
    $response->assertOk();

    $response->assertInertia(
        fn($page) => $page->component('tenants/tickets/interventions/show')
            ->has('intervention')
            ->where('intervention.id', $intervention->id)
            ->where('intervention.ticket.id', $this->ticket->id)
    );
});

it('shows the index interventions page', function () {
    Intervention::factory()->forLocation($this->asset)->count(2)->create();

    $response = $this->getFromTenant('tenant.interventions.index');
    $response->assertOk();

    $response->assertInertia(
        fn($page) => $page->component('tenants/tickets/interventions/index')
            ->has('interventions', 2)
    );
});

it('can create a new intervention for a TICKET', function () {

    $formData = [
        'intervention_type_id' => $this->interventionType->id,
        'priority' => 'medium',
        'status' => 'planned',
        'planned_at' => Carbon::now()->add('day', 7),
        'description' => fake()->paragraph(),
        'repair_delay' => Carbon::now()->add('month', 1),
        'ticket_id' => $this->ticket->id,
    ];

    $response = $this->postToTenant('api.interventions.store', $formData);
    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
        ]);

    assertDatabaseCount('interventions', 1);
    assertDatabaseHas('interventions', [
        'ticket_id' => $this->ticket->id,
        'status' => 'planned',
        'priority' => 'medium',
        'maintainable_id' => $this->ticket->ticketable->maintainable->id,
        'interventionable_id' => $this->ticket->ticketable->id,
        'interventionable_type' => get_class($this->ticket->ticketable)
    ]);
});

it('can create a new intervention for an ASSET', function () {

    $formData = [
        'intervention_type_id' => $this->interventionType->id,
        'priority' => 'medium',
        'status' => 'planned',
        'planned_at' => Carbon::now()->add('day', 7),
        'description' => fake()->paragraph(),
        'repair_delay' => Carbon::now()->add('month', 1),
        'locationId' => $this->asset->reference_code,
        'locationType' => 'asset'
    ];

    $response = $this->postToTenant('api.interventions.store', $formData);
    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
        ]);

    assertDatabaseCount('interventions', 1);
    assertDatabaseHas('interventions', [
        'priority' => 'medium',
        'status' => 'planned',
        'maintainable_id' => $this->asset->maintainable->id,
        'interventionable_type' => get_class($this->asset),
        'interventionable_id' => $this->asset->id
    ]);
});

it('can create a new intervention for a SITE', function () {

    $formData = [
        'intervention_type_id' => $this->interventionType->id,
        'priority' => 'high',
        'status' => 'planned',
        'planned_at' => Carbon::now()->add('day', 7),
        'description' => fake()->paragraph(),
        'repair_delay' => Carbon::now()->add('month', 1),
        'locationId' => $this->site->reference_code,
        'locationType' => 'sites'
    ];

    $response = $this->postToTenant('api.interventions.store', $formData);
    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
        ]);

    assertDatabaseCount('interventions', 1);
    assertDatabaseHas('interventions', [
        'priority' => 'high',
        'status' => 'planned',
        'maintainable_id' => $this->site->maintainable->id,
        'interventionable_type' => get_class($this->site),
        'interventionable_id' => $this->site->id
    ]);
});

it('can create a new intervention for a BUILDING', function () {

    $formData = [
        'intervention_type_id' => $this->interventionType->id,
        'priority' => 'high',
        'status' => 'planned',
        'planned_at' => Carbon::now()->add('day', 7),
        'description' => fake()->paragraph(),
        'repair_delay' => Carbon::now()->add('month', 1),
        'locationId' => $this->building->reference_code,
        'locationType' => 'buildings'
    ];

    $response = $this->postToTenant('api.interventions.store', $formData);
    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
        ]);

    assertDatabaseCount('interventions', 1);
    assertDatabaseHas('interventions', [
        'priority' => 'high',
        'status' => 'planned',
        'maintainable_id' => $this->building->maintainable->id,
        'interventionable_type' => get_class($this->building),
        'interventionable_id' => $this->building->id
    ]);
});

it('can create a new intervention for a FLOOR', function () {

    $formData = [
        'intervention_type_id' => $this->interventionType->id,
        'priority' => 'high',
        'status' => 'planned',
        'planned_at' => Carbon::now()->add('day', 7),
        'description' => fake()->paragraph(),
        'repair_delay' => Carbon::now()->add('month', 1),
        'locationId' => $this->floor->reference_code,
        'locationType' => 'floors'
    ];

    $response = $this->postToTenant('api.interventions.store', $formData);
    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
        ]);

    assertDatabaseCount('interventions', 1);
    assertDatabaseHas('interventions', [
        'priority' => 'high',
        'status' => 'planned',
        'maintainable_id' => $this->floor->maintainable->id,
        'interventionable_type' => get_class($this->floor),
        'interventionable_id' => $this->floor->id
    ]);
});

it('can create a new intervention for a ROOM', function () {

    $formData = [
        'intervention_type_id' => $this->interventionType->id,
        'priority' => 'high',
        'status' => 'planned',
        'planned_at' => Carbon::now()->add('day', 7),
        'description' => fake()->paragraph(),
        'repair_delay' => Carbon::now()->add('month', 1),
        'locationId' => $this->room->reference_code,
        'locationType' => 'rooms'
    ];

    $response = $this->postToTenant('api.interventions.store', $formData);
    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
        ]);

    assertDatabaseCount('interventions', 1);
    assertDatabaseHas('interventions', [
        'priority' => 'high',
        'status' => 'planned',
        'maintainable_id' => $this->room->maintainable->id,
        'interventionable_type' => get_class($this->room),
        'interventionable_id' => $this->room->id
    ]);
});

it('can update an existing intervention', function () {

    $intervention = Intervention::factory()->forLocation($this->room)->create(['status' => 'draft']);

    $formData = [
        'intervention_type_id' => $this->interventionType->id,
        'priority' => 'high',
        'status' => 'planned',
        'planned_at' => Carbon::now()->add('day', 20),
        'description' => 'New intervention description',
        'repair_delay' => Carbon::now()->add('month', 5),
        'locationId' => $this->room->reference_code,
        'locationType' => 'rooms'
    ];

    $response = $this->patchToTenant('api.interventions.update', $formData, $intervention);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
        ]);

    assertDatabaseHas('interventions', [
        'priority' => 'high',
        'status' => 'planned',
        'planned_at' => Carbon::now()->add('day', 20)->toDateString(),
        'description' => 'New intervention description',
        'repair_delay' => Carbon::now()->add('month', 5)->toDateString(),
    ]);
});

it('can delete an intervention', function () {

    $intervention = Intervention::factory()->forLocation($this->site)->create();

    $response = $this->deleteFromTenant('api.interventions.destroy', $intervention);
    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
        ]);

    assertDatabaseEmpty('interventions');
});

