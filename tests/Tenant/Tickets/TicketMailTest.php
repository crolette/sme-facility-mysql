<?php

use Carbon\Carbon;
use App\Enums\TicketStatus;
use App\Models\LocationType;
use App\Models\Tenants\Room;
use App\Models\Tenants\Site;
use App\Models\Tenants\User;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Floor;
use App\Mail\TicketClosedMail;
use App\Models\Tenants\Ticket;

use App\Mail\TicketCreatedMail;
use App\Models\Tenants\Building;
use App\Enums\InterventionStatus;
use Illuminate\Http\UploadedFile;
use App\Models\Central\CategoryType;
use App\Models\Tenants\Intervention;
use Illuminate\Support\Facades\Mail;
use function Pest\Laravel\assertDatabaseHas;
use function PHPUnit\Framework\assertEquals;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseMissing;

beforeEach(function () {
    Mail::fake();
    $this->admin = User::factory()->withRole('Admin')->create();
    $this->actingAs($this->admin, 'tenant');

    $this->manager = User::factory()->withRole('Maintenance Manager')->create();
    $this->otherManager = User::factory()->withRole('Maintenance Manager')->create();

    $this->interventionType = CategoryType::factory()->create(['category' => 'intervention']);
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

    $this->room = Room::factory()->withMaintainableData()->create();

    $this->asset =  Asset::factory()->withMaintainableData()->forLocation($this->room)->create();
    $this->asset->refresh();
});

it('sends an email to the admin when a new ticket is created', function () {

    // $this->floor->maintainable->manager()

    $formData = [
        'location_type' => 'floors',
        'location_code' => $this->floor->reference_code,
        'status' => TicketStatus::OPEN->value,
        'description' => 'A nice description for this new ticket',
        'being_notified' => false,
        'reported_by' => $this->admin->id
    ];

    $response = $this->postToTenant('api.tickets.store', $formData);
    $response->assertSessionHasNoErrors();

    Mail::assertSent(TicketCreatedMail::class, function ($mail) {
        return $mail->hasTo($this->admin->email);
    });
});

it('sends an email to the maintenance manager when a new ticket is created for managed location', function () {

    $manager = User::factory()->withRole('Maintenance Manager')->create();
    $this->floor->maintainable->manager()->associate($manager)->save();

    $formData = [
        'location_type' => 'floors',
        'location_code' => $this->floor->reference_code,
        'status' => TicketStatus::OPEN->value,
        'description' => 'A nice description for this new ticket',
        'being_notified' => false,
        'reported_by' => $this->admin->id
    ];

    $response = $this->postToTenant('api.tickets.store', $formData);
    $response->assertSessionHasNoErrors();

    Mail::assertSent(TicketCreatedMail::class, function ($mail) {
        return $mail->hasTo($this->admin->email);
    });

    Mail::assertSent(TicketCreatedMail::class, function ($mail) use ($manager) {
        return $mail->hasTo($manager->email);
    });
});

it('send an email to the anonymous notifier when a ticket is closed and the notifier wants to be notified', function () {

    $ticket = Ticket::factory()->anonymous()->forLocation($this->floor)->create(['being_notified' => true]);

    $response = $this->patchToTenant('api.tickets.status', ['status' => TicketStatus::CLOSED->value], $ticket);
    $response->assertSessionHasNoErrors();

    Mail::assertSent(TicketClosedMail::class, function ($mail) use ($ticket) {
        return $mail->hasTo($ticket->reporter_email);
    });
});

it('send an email to the loginable notifier when a ticket is closed and the notifier wants to be notified', function () {

    $ticket = Ticket::factory()->forLocation($this->floor)->create([
        'reported_by' => $this->otherManager,
        'reporter_email' => $this->otherManager->email,
        'being_notified' => true
    ]);

    $response = $this->patchToTenant('api.tickets.status', ['status' => TicketStatus::CLOSED->value], $ticket);
    $response->assertSessionHasNoErrors();

    Mail::assertSent(TicketClosedMail::class, function ($mail) use ($ticket) {
        return $mail->hasTo($this->otherManager->email);
    });
});

it('send an email to the admin when a ticket is closed', function () {

    $formData = [
        'status' => TicketStatus::OPEN->value,
        'description' => 'A nice description for this new ticket',
        'being_notified' => true,
        'reporter_email' => 'test@test.com',
    ];

    $ticket = Ticket::factory()->forLocation($this->floor)->create([...$formData]);

    $response = $this->patchToTenant('api.tickets.status', ['status' => TicketStatus::CLOSED->value], $ticket);
    // dump($response);
    $response->assertSessionHasNoErrors();

    Mail::assertSent(TicketClosedMail::class, function ($mail) {
        return $mail->hasTo($this->admin->email);
    });
});

it('send an email to the maintenance manager when a ticket is closed and if manager is linked to the ticket', function () {

    $formData = [
        'status' => TicketStatus::OPEN->value,
        'description' => 'A nice description for this new ticket',
        'being_notified' => true,
        'reporter_email' => 'test@test.com',
    ];

    $this->floor->maintainable->manager()->associate($this->manager)->save();

    $ticket = Ticket::factory()->forLocation($this->floor)->create([...$formData]);

    $response = $this->patchToTenant('api.tickets.status', ['status' => TicketStatus::CLOSED->value], $ticket);
    // dump($response);
    $response->assertSessionHasNoErrors();

    Mail::assertSent(TicketClosedMail::class, function ($mail) {
        return $mail->hasTo($this->manager->email);
    });
});

it('sends an email to the admin when intervention is updated to completed for a ticket', function () {
    $ticket = Ticket::factory()->forLocation($this->asset)->create();
    $intervention = Intervention::factory()->forTicket($ticket)->create();

    $formData = [
        'intervention_type_id' => $this->interventionType->id,
        'priority' => 'medium',
        'status' => InterventionStatus::COMPLETED->value,
        'description' => fake()->paragraph(),
        'ticket_id' => $ticket->id,
    ];

    $this->patchToTenant('api.interventions.update', $formData, $intervention->id);

    Mail::assertSent(TicketClosedMail::class, function ($mail) {
        return $mail->hasTo($this->admin->email);
    });
});

it('sends an email to the manager when intervention is updated to completed for a ticket', function () {

    $this->asset->maintainable->manager()->associate($this->manager)->save();
    $ticket = Ticket::factory()->forLocation($this->asset)->create();
    $intervention = Intervention::factory()->forTicket($ticket)->create();

    $formData = [
        'intervention_type_id' => $this->interventionType->id,
        'priority' => 'medium',
        'status' => InterventionStatus::COMPLETED->value,
        'description' => fake()->paragraph(),
        'ticket_id' => $ticket->id,
    ];

    $this->patchToTenant('api.interventions.update', $formData, $intervention->id);

    Mail::assertSent(TicketClosedMail::class, function ($mail) {
        return $mail->hasTo($this->manager->email);
    });
});


it('sends an email to the admin when intervention status change to complete for a ticket', function () {
    $ticket = Ticket::factory()->forLocation($this->asset)->create();
    $intervention = Intervention::factory()->forTicket($ticket)->create();

    $formData = [
        'status' => InterventionStatus::COMPLETED->value,
    ];

    $this->patchToTenant('api.interventions.status', $formData, $intervention);

    Mail::assertSent(TicketClosedMail::class, function ($mail) {
        return $mail->hasTo($this->admin->email);
    });
});

it('sends an email to the manager when intervention status change to complete for a ticket', function () {
    $this->asset->maintainable->manager()->associate($this->manager)->save();
    $ticket = Ticket::factory()->forLocation($this->asset)->create();
    $intervention = Intervention::factory()->forTicket($ticket)->create();

    $formData = [
        'status' => InterventionStatus::COMPLETED->value,
    ];

    $this->patchToTenant('api.interventions.status', $formData, $intervention);

    Mail::assertSent(TicketClosedMail::class, function ($mail) {
        return $mail->hasTo($this->manager->email);
    });
});
