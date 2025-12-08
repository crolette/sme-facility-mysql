<?php

use Carbon\Carbon;
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
use App\Models\Tenants\Intervention;
use function Pest\Laravel\assertDatabaseHas;
use function PHPUnit\Framework\assertEquals;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseMissing;

beforeEach(function () {
    $this->user = User::factory()->withRole('Admin')->create();
    $this->actingAs($this->user, 'tenant');
    $this->interventionType = CategoryType::factory()->create(['category' => 'intervention']);
    CategoryType::factory()->create(['category' => 'action']);
    $this->categoryType = CategoryType::factory()->create(['category' => 'asset']);
    $this->site = Site::factory()->withMaintainableData()->create();
    $this->building = Building::factory()->create();
    $this->floor = Floor::factory()->withMaintainableData()->create();

    $this->room = Room::factory()->withMaintainableData()->create();

    $this->asset =  Asset::factory()->withMaintainableData()->forLocation($this->room)->create();
});

it('can render the index tickets page', function () {

    Ticket::factory()->forLocation($this->asset)->create();
    Ticket::factory()->forLocation($this->asset)->ongoing()->create();
    Ticket::factory()->forLocation($this->asset)->closed()->create();

    $response = $this->getFromTenant('tenant.tickets.index');
    $response->assertOk();

    $response->assertInertia(
        fn($page) =>
        $page->component('tenants/tickets/IndexTickets')
    );
});


it('can render the show ticket page', function () {

    $ticket = Ticket::factory()->forLocation($this->asset)->create();

    $response = $this->getFromTenant('tenant.tickets.show', $ticket);
    $response->assertOk();

    $response->assertInertia(
        fn($page) => $page->component('tenants/tickets/ShowTicket')
            ->has('item')->where('item.code', $ticket->code)
    );
    $response->assertOk();
});

it('can render interventions in the ticket page', function () {
    $ticket = Ticket::factory()->forLocation($this->asset)->create();
    Intervention::factory()->withAction()->forTicket($ticket)->count(2)->create();

    $response = $this->getFromTenant('tenant.tickets.show', $ticket);
    $response->assertOk();

    $response->assertInertia(
        fn($page) =>
        $page->component('tenants/tickets/ShowTicket')
            ->has('item')
            ->has('item.interventions', 2)
            ->has('item.interventions.0.actions', 1)
    );
});

it('can create a new ticket to an ASSET with the logged user', function () {

    $formData = [
        'location_type' => 'assets',
        'location_code' => $this->asset->reference_code,
        'status' => TicketStatus::OPEN->value,
        'description' => 'A nice description for this new ticket',
        'being_notified' => false,
        'reported_by' => $this->user->id
    ];

    $response = $this->postToTenant('api.tickets.store', $formData);
    $response->assertStatus(200);

    assertDatabaseCount('tickets', 1);

    assertDatabaseHas('tickets', [
        'code' => 'TK0001',
        'status' => 'open',
        'reported_by' => $this->user->id,
        'being_notified' => false,
        'description' => 'A nice description for this new ticket',
        'ticketable_type' => get_class($this->asset),
        'ticketable_id' => $this->asset->id,
    ]);
});

it('can create a ticket with uploaded pictures', function () {
    $file1 = UploadedFile::fake()->image('avatar.png');
    $file2 = UploadedFile::fake()->image('test.jpg');

    $formData = [
        'location_type' => 'assets',
        'location_code' => $this->asset->reference_code,
        'status' => TicketStatus::OPEN->value,
        'description' => 'A nice description for this new ticket',
        'being_notified' => false,
        'reported_by' => $this->user->id,
        'pictures' => [
            $file1,
            $file2
        ]
    ];

    $response = $this->postToTenant('api.tickets.store', $formData);
    $response->assertSessionHasNoErrors();
    assertDatabaseCount('pictures', 2);
    assertDatabaseHas('pictures', [
        'imageable_type' => 'App\Models\Tenants\Ticket',
        'imageable_id' => 1
    ]);
});

it('can create several tickets with correct incremental code', function () {

    $formData = [
        'location_type' => 'assets',
        'location_code' => $this->asset->reference_code,
        'status' => TicketStatus::OPEN->value,
        'description' => 'A nice description for this new ticket',
        'being_notified' => false,
        'reported_by' => $this->user->id
    ];
    $response = $this->postToTenant('api.tickets.store', $formData);

    $formData = [
        'location_type' => 'sites',
        'location_code' => $this->site->reference_code,
        'status' => TicketStatus::OPEN->value,
        'description' => 'A nice description for this new ticket',
        'being_notified' => false,
        'reported_by' => $this->user->id
    ];

    $response = $this->postToTenant('api.tickets.store', $formData);

    $response->assertStatus(200);

    assertDatabaseCount('tickets', 2);

    assertDatabaseHas('tickets', [
        'code' => 'TK0001',
        'ticketable_type' => get_class($this->asset),
        'ticketable_id' => $this->asset->id,
    ]);

    assertDatabaseHas('tickets', [
        'code' => 'TK0002',
        'ticketable_type' => get_class($this->site),
        'ticketable_id' => $this->site->id,
    ]);
});

it('can create a new ticket to a ROOM', function () {

    $formData = [
        'location_type' => 'rooms',
        'location_code' => $this->room->reference_code,
        'status' => TicketStatus::OPEN->value,
        'description' => 'A nice description for this new ticket',
        'being_notified' => false,
        'reported_by' => $this->user->id
    ];

    $response = $this->postToTenant('api.tickets.store', $formData);
    $response->assertStatus(200);

    assertDatabaseCount('tickets', 1);

    assertDatabaseHas('tickets', [
        'status' => 'open',
        'reported_by' => $this->user->id,
        'being_notified' => false,
        'description' => 'A nice description for this new ticket',
        'ticketable_type' => get_class($this->room),
        'ticketable_id' => $this->room->id,
    ]);
});

it('can create a new ticket to a FLOOR', function () {

    $formData = [
        'location_type' => 'floors',
        'location_code' => $this->floor->reference_code,
        'status' => TicketStatus::OPEN->value,
        'description' => 'A nice description for this new ticket',
        'being_notified' => false,
        'reported_by' => $this->user->id
    ];

    $response = $this->postToTenant('api.tickets.store', $formData);
    $response->assertStatus(200);

    assertDatabaseCount('tickets', 1);

    assertDatabaseHas('tickets', [
        'status' => 'open',
        'reported_by' => $this->user->id,
        'being_notified' => false,
        'description' => 'A nice description for this new ticket',
        'ticketable_type' => get_class($this->floor),
        'ticketable_id' => $this->floor->id,
    ]);
});

it('can create a new ticket to a BUILDING', function () {

    $formData = [
        'location_type' => 'buildings',
        'location_code' => $this->building->reference_code,
        'status' => TicketStatus::OPEN->value,
        'description' => 'A nice description for this new ticket',
        'being_notified' => false,
        'reported_by' => $this->user->id
    ];

    $response = $this->postToTenant('api.tickets.store', $formData);
    $response->assertStatus(200);

    assertDatabaseCount('tickets', 1);

    assertDatabaseHas('tickets', [
        'status' => 'open',
        'reported_by' => $this->user->id,
        'being_notified' => false,
        'description' => 'A nice description for this new ticket',
        'ticketable_type' => get_class($this->building),
        'ticketable_id' => $this->building->id,
    ]);
});

it('can create a new ticket to a SITE', function () {

    $formData = [
        'location_type' => 'sites',
        'location_code' => $this->site->reference_code,
        'status' => TicketStatus::OPEN->value,
        'description' => 'A nice description for this new ticket',
        'being_notified' => false,
        'reported_by' => $this->user->id
    ];

    $response = $this->postToTenant('api.tickets.store', $formData);
    $response->assertStatus(200);

    assertDatabaseCount('tickets', 1);

    assertDatabaseHas('tickets', [
        'status' => 'open',
        'reported_by' => $this->user->id,
        'being_notified' => false,
        'description' => 'A nice description for this new ticket',
        'ticketable_type' => get_class($this->site),
        'ticketable_id' => $this->site->id,
    ]);
});

it('updates status to and handled_at columns when intervention is created for a ticket', function () {

    $ticket = Ticket::factory()->forLocation($this->asset)->create();

    $formData = [
        'intervention_type_id' => $this->interventionType->id,
        'priority' => 'medium',
        'status' => 'planned',
        'planned_at' => Carbon::now()->add('day', 7),
        'description' => fake()->paragraph(),
        'repair_delay' => Carbon::now()->add('month', 1),
        'ticket_id' => $ticket->id,
    ];

    $response = $this->postToTenant('api.interventions.store', $formData);
    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
        ]);

    assertDatabaseHas('tickets', [
        'id' => $ticket->id,
        'status' => 'ongoing',
        'handled_at' => Carbon::now()->toDateString(),
    ]);
});

it('can update an existing ticket', function () {

    $ticket = Ticket::factory()->forLocation($this->asset)->create(['reported_by' => $this->user->id]);

    $formData = [
        'location_type' => 'sites',
        'location_code' => $this->site->reference_code,
        'status' => TicketStatus::ONGOING->value,
        'description' => 'A nice description for this new ticket',
        'being_notified' => !$ticket->being_notified,
        'reported_by' =>  $this->user->id
    ];

    $response = $this->patchToTenant('api.tickets.update', $formData, $ticket);
    $response->assertStatus(200);

    assertDatabaseHas('tickets', [
        'status' => 'ongoing',
        'being_notified' => !$ticket->being_notified,
        'description' => 'A nice description for this new ticket',
        'ticketable_type' => get_class($this->site),
        'ticketable_id' => $this->site->id,
    ]);
});

it('can update the status of an existing ticket', function () {

    $ticket = Ticket::factory()->forLocation($this->asset)->create(['reported_by' => $this->user->id]);

    $response = $this->patchToTenant('api.tickets.status', ['status' => TicketStatus::ONGOING->value], $ticket);
    $response->assertSessionHasNoErrors();

    assertDatabaseHas('tickets', [
        'id' => $ticket->id,
        'status' => TicketStatus::ONGOING->value,
        'closed_by' => null,
    ]);
});

it('can close an existing ticket and update handled_at', function () {

    $ticket = Ticket::factory()->forLocation($this->asset)->create(['reported_by' => $this->user->id]);

    $response = $this->patchToTenant('api.tickets.status', ['status' => 'closed'], $ticket);
    $response->assertSessionHasNoErrors();

    assertDatabaseHas('tickets', [
        'id' => $ticket->id,
        'status' => 'closed',
        'closed_by' => $this->user->id,
        'handled_at' => Carbon::now()->toDateString()
    ]);
    $this->assertNotNull($ticket->fresh()->closed_at);
});

it('can delete an existing ticket', function () {

    $ticket = Ticket::factory()->forLocation($this->asset)->create(['reported_by' => $this->user->id]);

    $response = $this->deleteFromTenant('api.tickets.destroy', $ticket);
    $response->assertSessionHasNoErrors();

    assertDatabaseEmpty('tickets');
});
