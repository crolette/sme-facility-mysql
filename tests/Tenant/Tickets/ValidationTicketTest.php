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
use App\Models\Tenants\Picture;

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
    $this->building = Building::factory()->create();
    $this->floor = Floor::factory()->withMaintainableData()->create();

    $this->room = Room::factory()->withMaintainableData()
        ->for(LocationType::where('level', 'room')->first())
        ->for(Floor::first())
        ->create();

    $this->asset =  Asset::factory()->withMaintainableData()->forLocation($this->room)->create();
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

it('fails when not uploading an image', function () {

    $file1 = UploadedFile::fake()->image('avatar.pdf');

    $formData = [
        'location_type' => 'assets',
        'location_code' => $this->asset->reference_code,
        'description' => 'A nice description for this new ticket',
        'being_notified' => true,
        'reporter_email' => 'test@test.com',
        'pictures' => [
            $file1,
        ]
    ];

    $response = $this->postToTenant('api.tickets.store', $formData);
    $response->assertSessionHasErrors(
        [
            'pictures.0' => 'The pictures.0 field must be a file of type: jpg, jpeg, png.'
        ]
    );

    assertDatabaseEmpty('tickets');
});


it('fails when uploading more than 3 pictures', function () {

    $file1 = UploadedFile::fake()->image('avatar.png');
    $file2 = UploadedFile::fake()->image('test.jpg');
    $file3 = UploadedFile::fake()->image('ticket.jpg');
    $file4 = UploadedFile::fake()->image('test.jpg');

    $formData = [
        'location_type' => 'assets',
        'location_code' => $this->asset->reference_code,
        'description' => 'A nice description for this new ticket',
        'being_notified' => true,
        'reporter_email' => 'test@test.com',
        'pictures' => [
            $file1,
            $file2,
            $file3,
            $file4
        ]
    ];

    $response = $this->postToTenant('api.tickets.store', $formData);
    $response->assertSessionHasErrors(
        [
            'pictures' => 'The pictures field must not have more than 3 items.'
        ]
    );

    assertDatabaseEmpty('tickets');
});

it('fails when uploading a picture > Max MB', function () {

    $file1 = UploadedFile::fake()->image('avatar.png')->size('10000');
    $formData = [
        'location_type' => 'assets',
        'location_code' => $this->asset->reference_code,
        'description' => 'A nice description for this new ticket',
        'being_notified' => true,
        'reporter_email' => 'test@test.com',
        'pictures' => [
            $file1,
        ]
    ];

    $maxKB = Picture::MAX_UPLOAD_SIZE_MB * 1024;

    $response = $this->postToTenant('api.tickets.store', $formData);
    $response->assertSessionHasErrors(
        [
            'pictures.0' => 'The pictures.0 field must not be greater than ' . $maxKB . ' kilobytes.'
        ]
    );
});

it('fails when creating ticket with description less than 10 characters', function () {
    $formData = [
        'location_type' => 'assets',
        'location_code' => $this->asset->reference_code,
        'description' => 'Short',
        'being_notified' => true,
        'reporter_email' => 'test@test.com',
    ];

    $response = $this->postToTenant('api.tickets.store', $formData);
    $response->assertSessionHasErrors(
        [
            'description' => 'The description field must be at least 10 characters.'
        ]
    );
});
