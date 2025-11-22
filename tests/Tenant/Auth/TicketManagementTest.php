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
use App\Enums\NoticePeriodEnum;
use App\Models\Tenants\Building;
use App\Models\Tenants\Contract;
use App\Models\Tenants\Document;
use App\Models\Tenants\Provider;
use App\Enums\ContractStatusEnum;
use Illuminate\Http\UploadedFile;
use App\Enums\ContractDurationEnum;
use App\Models\Central\CategoryType;
use App\Enums\ContractRenewalTypesEnum;
use Illuminate\Support\Facades\Storage;
use function PHPUnit\Framework\assertNull;
use function PHPUnit\Framework\assertTrue;
use function Pest\Laravel\assertDatabaseHas;
use function PHPUnit\Framework\assertNotNull;
use Illuminate\Testing\Fluent\AssertableJson;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseMissing;


beforeEach(function () {
    $this->user = User::factory()->withRole('Admin')->create();
    LocationType::factory()->create(['level' => 'site']);
    LocationType::factory()->create(['level' => 'building']);
    LocationType::factory()->create(['level' => 'floor']);
    LocationType::factory()->create(['level' => 'room']);
    CategoryType::factory()->create(['category' => 'document']);
    CategoryType::factory()->create(['category' => 'asset']);
    $this->categoryType = CategoryType::factory()->create(['category' => 'provider']);
    $this->site = Site::factory()->create();
    $this->building = Building::factory()->create();
    $this->floor = Floor::factory()->create();

    $this->room = Room::factory()->create();

    $this->asset = Asset::factory()->forLocation($this->room)->create();
    $this->provider = Provider::factory()->create();
    $this->contract = Contract::factory()->create();

    $file1 = UploadedFile::fake()->image('logo.png');


    $this->formData = [
        'location_type' => 'assets',
        'location_code' => $this->asset->reference_code,
        'status' => TicketStatus::OPEN->value,
        'description' => 'A nice description for this new ticket',
        'being_notified' => false,
        'reported_by' => $this->user->id
    ];
});

test('test access roles to tickets index page', function (string $role, int $expectedStatus) {
    $user = User::factory()->withRole($role)->create();
    $this->actingAs($user, 'tenant');

    $response = $this->getFromTenant('tenant.tickets.index');
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 403],
    // ['Provider', 403],
    // ['', 403]
]);

test('test access roles to view tickets for an asset without maintenance manager', function (string $role, int $expectedStatus) {
    $user = User::factory()->withRole($role)->create();
    $this->actingAs($user, 'tenant');

    $ticket = Ticket::factory()->forLocation($this->asset)->create();

    $response = $this->getFromTenant('tenant.tickets.show', $ticket->id);
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 403],
    // ['Provider', 403],
    ['', 403]
]);

test('test access roles to view tickets for an asset with maintenance manager', function (string $role, int $expectedStatus) {
    $user = User::factory()->withRole($role)->create();
    $this->actingAs($user, 'tenant');
    $this->asset->maintainable()->update(['maintenance_manager_id' => $user->id]);

    $ticket = Ticket::factory()->forLocation($this->asset)->create();

    $response = $this->getFromTenant('tenant.tickets.show', $ticket->id);
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 200],
    // ['Provider', 403],
    ['', 403]
]);

test('test access roles to create tickets page for asset', function (string $role, int $expectedStatus) {
    $user = User::factory()->withRole($role)->create();
    $this->actingAs($user, 'tenant');

    $this->asset->update([
        'qr_hash' => generateQRCodeHash($this->asset)
    ]);

    $this->asset->refresh();

    $response = $this->getFromTenant('tenant.assets.tickets.create', $this->asset->qr_hash);

    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 200],
    // ['Provider', 200],
    ['', 200]
]);

test('test access roles to create tickets page for site', function (string $role, int $expectedStatus) {
    $user = User::factory()->withRole($role)->create();
    $this->actingAs($user, 'tenant');

    $this->site->update([
        'qr_hash' => generateQRCodeHash($this->site)
    ]);

    $this->site->refresh();

    $response = $this->getFromTenant('tenant.sites.tickets.create', $this->site->qr_hash);

    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 200],
    // ['Provider', 200],
    ['', 200]
]);

test('test access roles to create tickets page for building', function (string $role, int $expectedStatus) {
    $user = User::factory()->withRole($role)->create();
    $this->actingAs($user, 'tenant');

    $this->building->update([
        'qr_hash' => generateQRCodeHash($this->building)
    ]);

    $this->building->refresh();

    $response = $this->getFromTenant('tenant.buildings.tickets.create', $this->building->qr_hash);
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 200],
    // ['Provider', 200],
    ['', 200]
]);

test('test access roles to create tickets page for floor', function (string $role, int $expectedStatus) {
    $user = User::factory()->withRole($role)->create();
    $this->actingAs($user, 'tenant');

    $this->floor->update([
        'qr_hash' => generateQRCodeHash($this->floor)
    ]);

    $this->floor->refresh();

    $response = $this->getFromTenant('tenant.floors.tickets.create', $this->floor->qr_hash);
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 200],
    // ['Provider', 200],
    ['', 200]
]);

test('test access roles to create tickets page for room', function (string $role, int $expectedStatus) {
    $user = User::factory()->withRole($role)->create();
    $this->actingAs($user, 'tenant');

    $this->room->update([
        'qr_hash' => generateQRCodeHash($this->room)
    ]);

    $this->room->refresh();

    $response = $this->getFromTenant('tenant.rooms.tickets.create', $this->room->qr_hash);
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 200],
    ['Provider', 200],
    ['', 200]
]);


test('test access roles to store a ticket', function (string $role, int $expectedStatus) {
    $user = User::factory()->withRole($role)->create();
    $this->actingAs($user, 'tenant');

    $response = $this->postToTenant('api.tickets.store', $this->formData);
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 200],
    // ['Provider', 200],
    ['', 200]
]);

test('test access roles to update any ticket', function (string $role, int $expectedStatus) {
    $user = User::factory()->withRole($role)->create();
    $this->actingAs($user, 'tenant');

    $ticket = Ticket::factory()->forLocation($this->asset)->create();

    $response = $this->patchToTenant('api.tickets.update', $this->formData, $ticket);
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 403],
    // ['Provider', 403],
    ['', 403]
]);

test('test access roles to update ticket with maintenance manager', function (string $role, int $expectedStatus) {
    $user = User::factory()->withRole($role)->create();
    $this->actingAs($user, 'tenant');

    $this->asset->maintainable()->update(['maintenance_manager_id' => $user->id]);

    $ticket = Ticket::factory()->forLocation($this->asset)->create();

    $response = $this->patchToTenant('api.tickets.update', $this->formData, $ticket);
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 200],
    // ['Provider', 403],
    ['', 403]
]);

test('test access roles to update the status of any ticket', function (string $role, int $expectedStatus) {
    $user = User::factory()->withRole($role)->create();
    $this->actingAs($user, 'tenant');

    $ticket = Ticket::factory()->forLocation($this->asset)->create();

    $response = $this->patchToTenant('api.tickets.status', ['status' => TicketStatus::ONGOING->value], $ticket);

    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 403],
    // ['Provider', 403],
    ['', 403]
]);

test('test access roles to update the status of ticket with maintenance manager', function (string $role, int $expectedStatus) {
    $user = User::factory()->withRole($role)->create();
    $this->actingAs($user, 'tenant');

    $this->site->maintainable()->update(['maintenance_manager_id' => $user->id]);

    $ticket = Ticket::factory()->forLocation($this->site)->create();

    $response = $this->patchToTenant('api.tickets.status', ['status' => TicketStatus::ONGOING->value], $ticket);

    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 200],
    // ['Provider', 403],
    ['', 403]
]);

test('test access roles to delete any ticket', function (string $role, int $expectedStatus) {
    $user = User::factory()->withRole($role)->create();
    $this->actingAs($user, 'tenant');

    $ticket = Ticket::factory()->forLocation($this->asset)->create();

    $response = $this->deleteFromTenant('api.tickets.destroy', $ticket);
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 403],
    // ['Provider', 403],
    ['', 403]
]);
