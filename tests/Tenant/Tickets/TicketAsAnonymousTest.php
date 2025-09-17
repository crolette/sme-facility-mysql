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
    $this->site = Site::factory()->create();
    $this->building = Building::factory()->create();
    $this->floor = Floor::factory()->create();

    $this->room = Room::factory()
        ->for(LocationType::where('level', 'room')->first())
        ->for(Floor::first())
        ->create();

    $this->asset =  Asset::factory()->forLocation($this->room)->create();
    
});

it('can render the index tickets page', function () {

    Ticket::factory()->forLocation($this->asset)->create();
    Ticket::factory()->forLocation($this->asset)->ongoing()->create();
    Ticket::factory()->forLocation($this->asset)->closed()->create();

    $response = $this->getFromTenant('tenant.tickets.index');
    $response->assertOk();

    $response->assertInertia(
        fn($page) =>
        $page->component('tenants/tickets/index')
    );
});


it('can render the show ticket page', function () {

    $ticket = Ticket::factory()->forLocation($this->asset)->create();

    $response = $this->getFromTenant('tenant.tickets.show', $ticket);
    $response->assertOk();

    $response->assertInertia(
        fn($page) => $page->component('tenants/tickets/show')
            ->has('ticket')
    );
    $response->assertOk();
});

it('fails when creating a new ticket with a wrong asset code', function () {

    $formData = [
        'location_type' => 'assets',
        'location_code' => 'AB01',
        'status' => TicketStatus::OPEN->value,
        'description' => 'A nice description for this new ticket',
        'being_notified' => false,
        'reported_by' => $this->user->id
    ];

    $response = $this->postToTenant('api.tickets.store', $formData);
    $response->assertSessionHasErrors(
        ['location_code' => "The selected location code is invalid."]
    );

    assertDatabaseEmpty('tickets');
});

it('fails when creating a new ticket with a wrong location type', function () {

    $formData = [
        'location_type' => 'toilets',
        'location_code' => 'AB01',
        'status' => TicketStatus::OPEN->value,
        'description' => 'A nice description for this new ticket',
        'being_notified' => true,
        'reported_by' => $this->user->id
    ];

    $response = $this->postToTenant('api.tickets.store', $formData);
    $response->assertSessionHasErrors(
        ['location_type' => "The selected location type is invalid."]
    );

    assertDatabaseEmpty('tickets');
});

it('fails when creating a new ticket with a wrong status', function () {

    $formData = [
        'location_type' => 'assets',
        'location_code' => $this->asset->code,
        'status' => 'test',
        'description' => 'A nice description for this new ticket',
        'being_notified' => false,
        'reported_by' => $this->user->id
    ];

    $response = $this->postToTenant('api.tickets.store', $formData);
    $response->assertSessionHasErrors(
        [
            'status' => 'The selected status is invalid.'
        ]
    );

    assertDatabaseEmpty('tickets');
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
    $response->assertOk(302);
    // dump($response);
    $response->assertSessionHasNoErrors();

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

it('can create a new ticket to an ASSET with "anonymous" user', function () {

    $formData = [
        'location_type' => 'assets',
        'location_code' => $this->asset->reference_code,
        'status' => TicketStatus::OPEN->value,
        'description' => 'A nice description for this new ticket',
        'being_notified' => true,
        'reporter_email' => 'test@test.com'
    ];

    $response = $this->postToTenant('api.tickets.store', $formData);
    $response->assertOk(302);
    // dump($response);
    $response->assertSessionHasNoErrors();

    assertDatabaseCount('tickets', 1);

    assertDatabaseHas('tickets', [
        'status' => 'open',
        'reporter_email' => 'test@test.com',
        'being_notified' => 1,
        'description' => 'A nice description for this new ticket',
        'ticketable_type' => get_class($this->asset),
        'ticketable_id' => 1,
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

    $response->assertOk(302);
    $response->assertSessionHasNoErrors();

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
    $response->assertOk(302);
    // dump($response);
    $response->assertSessionHasNoErrors();

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
    $response->assertOk(302);
    // dump($response);
    $response->assertSessionHasNoErrors();

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
    $response->assertOk(302);
    // dump($response);
    $response->assertSessionHasNoErrors();

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
    $response->assertOk(302);
    // dump($response);
    $response->assertSessionHasNoErrors();

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

    assertDatabaseHas('tickets', [
        'status' => 'ongoing',
        'being_notified' => !$ticket->being_notified,
        'description' => 'A nice description for this new ticket',
        'ticketable_type' => get_class($this->site),
        'ticketable_id' => $this->site->id,
    ]);
});

it('can close an existing ticket', function () {

    $ticket = Ticket::factory()->forLocation($this->asset)->create(['reported_by' => $this->user->id]);

    $response = $this->patchToTenant('api.tickets.status', ['status' => 'closed'], $ticket);
    $response->assertSessionHasNoErrors();

    assertDatabaseHas('tickets', [
        'id' => $ticket->id,
        'status' => 'closed',
        'closed_by' => $this->user->id,
    ]);
    $this->assertNotNull($ticket->fresh()->closed_at);
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
