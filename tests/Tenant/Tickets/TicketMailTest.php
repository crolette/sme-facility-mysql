<?php

use App\Enums\TicketStatus;
use App\Mail\TicketClosedMail;
use App\Mail\TicketCreatedMail;
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
use Illuminate\Support\Facades\Mail;
use function Pest\Laravel\assertDatabaseHas;
use function PHPUnit\Framework\assertEquals;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseMissing;

beforeEach(function () {
    Mail::fake();
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

it('sends an email when a new ticket is created for location without maintenance manager', function() {

    // $this->floor->maintainable->manager()

    $formData = [
        'location_type' => 'floors',
        'location_code' => $this->floor->reference_code,
        'status' => TicketStatus::OPEN->value,
        'description' => 'A nice description for this new ticket',
        'being_notified' => false,
        'reported_by' => $this->user->id
    ];

    $response = $this->postToTenant('api.tickets.store', $formData);
    $response->assertSessionHasNoErrors();

    Mail::assertSent(TicketCreatedMail::class, function ($mail) {
        return $mail->hasTo($this->user->email);
    });
});

it('sends an email when a new ticket is created for location with maintenance manager', function () {

    $manager = User::factory()->withRole('Maintenance Manager')->create();
    $this->floor->maintainable->manager()->associate($manager)->save();

    $formData = [
        'location_type' => 'floors',
        'location_code' => $this->floor->reference_code,
        'status' => TicketStatus::OPEN->value,
        'description' => 'A nice description for this new ticket',
        'being_notified' => false,
        'reported_by' => $this->user->id
    ];

    $response = $this->postToTenant('api.tickets.store', $formData);
    $response->assertSessionHasNoErrors();

    Mail::assertSent(TicketCreatedMail::class, function ($mail) {
        return $mail->hasTo($this->user->email);
    });

    Mail::assertSent(TicketCreatedMail::class, function ($mail) use ($manager) {
        return $mail->hasTo($manager->email);
    });
});

it('send an email to the notifier when a ticket is closed and the notifier wanted to be notified', function() {

    $formData = [
        'status' => TicketStatus::OPEN->value,
        'description' => 'A nice description for this new ticket',
        'being_notified' => true,
        'reporter_email' => 'test@test.com',
    ];

    $ticket = Ticket::factory()->forLocation($this->floor)->create([...$formData]);

    $response = $this->patchToTenant('api.tickets.status', ['status' => TicketStatus::CLOSED->value], $ticket);
    $response->assertSessionHasNoErrors();

    Mail::assertSent(TicketClosedMail::class, function ($mail) {
        return $mail->hasTo('test@test.com');
    });

});