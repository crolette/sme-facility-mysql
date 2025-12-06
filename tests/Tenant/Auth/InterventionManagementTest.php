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
use App\Enums\InterventionStatus;
use App\Models\Tenants\Intervention;
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

    $this->interventionType = CategoryType::factory()->create(['category' => 'intervention']);
    $this->interventionActionType = CategoryType::factory()->create(['category' => 'action']);

    $this->categoryType = CategoryType::factory()->create(['category' => 'provider']);
    $this->site = Site::factory()->create();
    $this->building = Building::factory()->create();
    $this->floor = Floor::factory()->create();
    $this->room = Room::factory()->create();
    $this->asset = Asset::factory()->forLocation($this->room)->create();
    $this->asset->refresh();
    $this->provider = Provider::factory()->create();
    $this->contract = Contract::factory()->create();
    $this->ticket = Ticket::factory()->forLocation($this->asset)->create();

    $this->interventionAssetData = [
        'intervention_type_id' => $this->interventionType->id,
        'priority' => 'medium',
        'status' => 'planned',
        'planned_at' => Carbon::now()->add('day', 7),
        'description' => fake()->paragraph(),
        'repair_delay' => Carbon::now()->add('month', 1),
        'locationId' => $this->asset->reference_code,
        'locationType' => 'asset'
    ];

    $this->interventionTicketData = [
        'intervention_type_id' => $this->interventionType->id,
        'priority' => 'medium',
        'status' => 'planned',
        'planned_at' => Carbon::now()->add('day', 7),
        'description' => fake()->paragraph(),
        'repair_delay' => Carbon::now()->add('month', 1),
        'ticket_id' => $this->ticket->id,
    ];
});


test('test access roles to interventions index page', function (string $role, int $expectedStatus) {
    $user = User::factory()->withRole($role)->create();
    $this->actingAs($user, 'tenant');

    $response = $this->getFromTenant('tenant.interventions.index');
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 200],
]);

test('test access roles to view intervention for an asset without maintenance manager', function (string $role, int $expectedStatus) {
    $user = User::factory()->withRole($role)->create();
    $this->actingAs($user, 'tenant');

    $ticket = Intervention::factory()->forLocation($this->asset)->create();

    $response = $this->getFromTenant('tenant.interventions.show', $ticket->id);
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 403],
    // ['Provider', 403],
    ['', 403]
]);

test('test access roles to view intervention for an asset with maintenance manager', function (string $role, int $expectedStatus) {
    $user = User::factory()->withRole($role)->create();
    $this->actingAs($user, 'tenant');
    $this->asset->maintainable->manager()->associate($user)->save();

    $ticket = Intervention::factory()->forLocation($this->asset)->create();

    $response = $this->getFromTenant('tenant.interventions.show', $ticket->id);
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 200],
    // ['Provider', 403],
]);

test('test access roles to store an intervention for an asset without maintenance manager', function (string $role, int $expectedStatus) {
    $user = User::factory()->withRole($role)->create();
    $this->actingAs($user, 'tenant');

    $response = $this->postToTenant('api.interventions.store', $this->interventionAssetData);
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 403],
    // ['Provider', 200],
    ['', 403]
]);

test('test access roles to store an intervention for an asset with maintenance manager', function (string $role, int $expectedStatus) {
    $user = User::factory()->withRole($role)->create();
    $this->actingAs($user, 'tenant');
    $this->asset->maintainable->manager()->associate($user)->save();

    $response = $this->postToTenant('api.interventions.store', $this->interventionAssetData);
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 200],
    // ['Provider', 200],
    // ['', 403]
]);

test('test access roles to update any intervention', function (string $role, int $expectedStatus) {
    $user = User::factory()->withRole($role)->create();
    $this->actingAs($user, 'tenant');

    $intervention = Intervention::factory()->forLocation($this->asset)->create();

    $response = $this->patchToTenant('api.interventions.update', [...$this->interventionAssetData, 'locationType' => Asset::class], $intervention);
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 403],
    // ['Provider', 403],
    ['', 403]
]);

test('test access roles to update intervention with maintenance manager', function (string $role, int $expectedStatus) {
    $user = User::factory()->withRole($role)->create();
    $this->actingAs($user, 'tenant');

    $this->asset->maintainable->manager()->associate($user)->save();

    $intervention = Intervention::factory()->forLocation($this->asset)->create();

    $response = $this->patchToTenant('api.interventions.update', [...$this->interventionAssetData, 'locationType' => Asset::class], $intervention);
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 200],
    // ['Provider', 403],
    // ['', 403]
]);

test('test access roles to update the status of any intervention', function (string $role, int $expectedStatus) {
    $user = User::factory()->withRole($role)->create();
    $this->actingAs($user, 'tenant');

    $intervention = Intervention::factory()->forLocation($this->asset)->create();

    $response = $this->patchToTenant('api.interventions.status', ['status' => InterventionStatus::COMPLETED->value], $intervention);

    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 403],
    // ['Provider', 403],
    ['', 403]
]);

test('test access roles to update the status of intervention with maintenance manager', function (string $role, int $expectedStatus) {
    $user = User::factory()->withRole($role)->create();
    $this->actingAs($user, 'tenant');

    $this->asset->maintainable->manager()->associate($user)->save();

    $intervention = Intervention::factory()->forLocation($this->asset)->create();

    $response = $this->patchToTenant('api.interventions.status', ['status' => InterventionStatus::COMPLETED->value], $intervention);

    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 200],
    // ['Provider', 403],
    // ['', 403]
]);

test('test access roles to delete any intervention', function (string $role, int $expectedStatus) {
    $user = User::factory()->withRole($role)->create();
    $this->actingAs($user, 'tenant');

    $intervention = Intervention::factory()->forLocation($this->asset)->create();

    $response = $this->deleteFromTenant('api.interventions.destroy', $intervention);
    $response->assertStatus($expectedStatus);
})->with([
    ['Admin', 200],
    ['Maintenance Manager', 403],
    // ['Provider', 403],
    // ['', 403]
]);
