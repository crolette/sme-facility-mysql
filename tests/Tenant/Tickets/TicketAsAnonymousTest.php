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
    User::factory()->create();
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

it('can render a new ticket page for a guest', function (string $modelType, string $routeName) {
    $model = match ($modelType) {
        'asset' => $this->asset,
        'site' => $this->site,
        'building' => $this->building,
        'floor' => $this->floor,
        'room' => $this->room,
        default => throw new Exception('Unknown model type')
    };

$model->update([
    'qr_hash' => generateQRCodeHash('routeName', $model)
]);

    $model->refresh();


    $response = $this->getFromTenant('tenant.'.$routeName. '.tickets.create', $model->qr_hash);
    $response->assertOk();

    $response->assertInertia(
        fn($page) =>
        $page->component('tenants/tickets/CreateTicketFromQRCode')->has('item')->where('item.name', $model->name)->where('item.reference_code', $model->reference_code)
    );
})->with([
    ['asset', 'assets'],
    ['site', 'sites'],
    ['building', 'buildings'],
    ['floor', 'floors'],
    ['room', 'rooms'],
]);

it('can create a new ticket with pictures has "anonymous" user', function (string $modelType, string $locationType) {

    $model = match ($modelType) {
        'asset' => $this->asset,
        'site' => $this->site,
        'building' => $this->building,
        'floor' => $this->floor,
        'room' => $this->room,
        default => throw new Exception('Unknown model type')
    };

    $model->refresh();

    $file1 = UploadedFile::fake()->image('avatar.png');
    $file2 = UploadedFile::fake()->image('test.jpg');

    $formData = [
        'location_type' => $locationType,
        'location_code' => $model->reference_code,
        'description' => 'A nice description for this new ticket',
        'being_notified' => true,
        'reporter_email' => 'test@test.com',
        'pictures' => [
            $file1,
            $file2
        ]
    ];

    $response = $this->postToTenant('api.tickets.store', $formData);
    $response->assertStatus(200);

    assertDatabaseCount('tickets', 1);

    assertDatabaseHas('tickets', [
        'status' => 'open',
        'reporter_email' => 'test@test.com',
        'being_notified' => 1,
        'description' => 'A nice description for this new ticket',
        'ticketable_type' => get_class($model),
        'ticketable_id' => 1,
    ]);

    assertDatabaseCount('pictures', 2);
    assertDatabaseHas('pictures', [
        'imageable_type' => 'App\Models\Tenants\Ticket',
        'imageable_id' => 1
    ]);
})->with([
    ['asset', 'assets'],
    ['site', 'sites'],
    ['building', 'buildings'],
    ['floor', 'floors'],
    ['room', 'rooms'],
]);
