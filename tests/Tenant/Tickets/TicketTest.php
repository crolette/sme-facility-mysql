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
use App\Services\TenantLimits;
use App\Models\Tenants\Building;
use Illuminate\Http\UploadedFile;
use App\Models\Central\CategoryType;
use App\Models\Tenants\Intervention;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
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
    $this->building = Building::factory()->withMaintainableData()->create();
    $this->floor = Floor::factory()->withMaintainableData()->create();

    $this->room = Room::factory()->withMaintainableData()->create();

    $this->asset =  Asset::factory()->withMaintainableData()->forLocation($this->room)->create();

    $tenant = tenant();

    if ($tenant) {
        Cache::remember(
            "tenant:{$tenant->id}:limits",
            now()->addDay(),
            fn() => TenantLimits::loadLimitsFromDatabase($tenant)
        );
    }
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
    Queue::fake();
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

it('updates ticket status and handled_at columns when intervention is created for a ticket', function () {

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
        'handled_at' => Carbon::now()->toDateTimeString(),
    ]);
});

it('updates ticket status to closed and closed_at columns when intervention is completed for a ticket', function () {
    Carbon::setTestNow(Carbon::now());
    $ticket = Ticket::factory()->forLocation($this->asset)->create();
    $intervention = Intervention::factory()->forTicket($ticket)->create();

    $formData = [
        'intervention_type_id' => $this->interventionType->id,
        'priority' => 'medium',
        'status' => 'completed',
        'description' => fake()->paragraph(),
        'ticket_id' => $ticket->id,
    ];

    $response = $this->patchToTenant('api.interventions.update', $formData, $intervention->id);
    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
        ]);

    assertDatabaseHas('tickets', [
        'id' => $ticket->id,
        'status' => 'closed',
        'handled_at' => Carbon::now()->toDateTimeString(),
        'closed_at' => Carbon::now()->toDateTimeString(),
    ]);
});

it('can update an existing ticket', function () {
    Carbon::setTestNow(Carbon::now());
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
        'handled_at' => Carbon::now()->toDateTimeString()
    ]);
});

it('can update the status of an existing ticket', function () {
    Carbon::setTestNow(Carbon::now());
    $ticket = Ticket::factory()->forLocation($this->asset)->create(['reported_by' => $this->user->id]);

    $response = $this->patchToTenant('api.tickets.status', ['status' => TicketStatus::ONGOING->value], $ticket);
    $response->assertSessionHasNoErrors();

    assertDatabaseHas('tickets', [
        'id' => $ticket->id,
        'status' => TicketStatus::ONGOING->value,
        'closed_by' => null,
        'handled_at' => Carbon::now()->toDateTimeString()
    ]);
});

it('can close an existing ticket and update handled_at', function () {
    Carbon::setTestNow(Carbon::now());
    $ticket = Ticket::factory()->forLocation($this->asset)->create(['reported_by' => $this->user->id]);

    $response = $this->patchToTenant('api.tickets.status', ['status' => 'closed'], $ticket);
    $response->assertSessionHasNoErrors();

    assertDatabaseHas('tickets', [
        'id' => $ticket->id,
        'status' => 'closed',
        'closed_by' => $this->user->id,
        'handled_at' => Carbon::now()->toDateTimeString()
    ]);
    $this->assertNotNull($ticket->fresh()->closed_at);
});

it('can delete an existing ticket', function () {

    $ticket = Ticket::factory()->forLocation($this->asset)->create(['reported_by' => $this->user->id]);

    $response = $this->deleteFromTenant('api.tickets.destroy', $ticket);
    $response->assertSessionHasNoErrors();

    assertDatabaseEmpty('tickets');
});
