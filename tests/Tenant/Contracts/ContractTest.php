<?php

use Carbon\Carbon;
use App\Models\LocationType;
use App\Models\Tenants\Room;
use App\Models\Tenants\Site;
use App\Models\Tenants\User;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Floor;
use App\Enums\NoticePeriodEnum;
use App\Enums\ContractTypesEnum;
use App\Models\Tenants\Building;

use App\Models\Tenants\Contract;
use App\Models\Tenants\Provider;

use App\Enums\ContractStatusEnum;
use App\Enums\ContractDurationEnum;

use App\Models\Central\CategoryType;
use App\Enums\ContractRenewalTypesEnum;

use Illuminate\Support\Facades\Storage;
use function PHPUnit\Framework\assertCount;
use function Pest\Laravel\assertDatabaseHas;
use function PHPUnit\Framework\assertEquals;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseMissing;

beforeEach(function () {
    $this->user = User::factory()->withRole('Admin')->create();
    $this->actingAs($this->user, 'tenant');

    $this->siteType = LocationType::factory()->create(['level' => 'site']);
    $this->buildingType = LocationType::factory()->create(['level' => 'building']);
    $this->floorType = LocationType::factory()->create(['level' => 'floor']);
    $this->roomType = LocationType::factory()->create(['level' => 'room']);
    CategoryType::factory()->count(2)->create(['category' => 'document']);
    $this->categoryType = CategoryType::factory()->create(['category' => 'asset']);
    CategoryType::factory()->count(2)->create(['category' => 'provider']);
    CategoryType::factory()->count(2)->create(['category' => 'asset']);
    $this->site = Site::factory()->create();
    $this->building = Building::factory()->create();
    $this->floor = Floor::factory()->create();
    $this->provider = Provider::factory()->create();

    $this->room = Room::factory()->create();

    $this->asset = Asset::factory()->forLocation(Room::first())->create();
    $this->contractOneData = [
        'provider_id' => $this->provider->id,
        'name' => 'Contrat de bail',
        'type' => ContractTypesEnum::ALLIN->value,
        'notes' => 'Nouveau contrat de bail 2025',
        'internal_reference' => 'Bail Site 2025',
        'provider_reference' => 'Provider reference 2025',
        'start_date' => Carbon::now()->toDateString(),
        'contract_duration' => ContractDurationEnum::ONE_MONTH->value,
        'notice_period' => NoticePeriodEnum::FOURTEEN_DAYS->value,
        'renewal_type' => ContractRenewalTypesEnum::AUTOMATIC->value,
        'status' => ContractStatusEnum::ACTIVE->value
    ];

    $this->contractTwoData = [
        'provider_id' => $this->provider->id,
        'name' => 'Contrat de sécurité',
        'type' => ContractTypesEnum::ALLIN->value,
        'notes' => 'Nouveau contrat de Sécurité 2025',
        'internal_reference' => 'Sécurité Site 2025',
        'provider_reference' => 'Provider reference 2025',
        'start_date' => Carbon::now()->toDateString(),
        'contract_duration' => ContractDurationEnum::ONE_MONTH->value,
        'notice_period' => NoticePeriodEnum::FOURTEEN_DAYS->value,
        'renewal_type' => ContractRenewalTypesEnum::AUTOMATIC->value,
        'status' => ContractStatusEnum::ACTIVE->value
    ];
});

it('can factory a contract', function () {

    Contract::factory()->forLocation($this->asset)->create();
    assertDatabaseCount('contracts', 1);
    assertDatabaseCount('contractables', 1);
    assertEquals(1, $this->asset->contracts()->count());
});

it('can create a contract without type and other will be the default type', function () {

    $formData = [
        'provider_id' => $this->provider->id,
        'name' => 'Contrat de sécurité',
        'renewal_type' => ContractRenewalTypesEnum::AUTOMATIC->value,
        'status' => ContractStatusEnum::ACTIVE->value,
        'contract_duration' => ContractDurationEnum::ONE_MONTH->value,
        'contractables' => [
            ['locationType' => 'site', 'locationCode' => $this->site->code, 'locationId' => $this->site->id],
        ]
    ];

    $response = $this->postToTenant('api.contracts.store', $formData);
    $response->assertSessionHasNoErrors();

    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);

    assertDatabaseHas('contracts', [
        'provider_id' => $this->provider->id,
        'name' => 'Contrat de sécurité',
        'type' => ContractTypesEnum::OTHER->value,

    ]);
});

it('can create a contract with every contract type', function ($type) {

    $formData = [
        'provider_id' => $this->provider->id,
        'type' => $type,
        'name' => 'Contrat de sécurité',
        'renewal_type' => ContractRenewalTypesEnum::AUTOMATIC->value,
        'status' => ContractStatusEnum::ACTIVE->value,
        'contract_duration' => ContractDurationEnum::ONE_MONTH->value,
        'contractables' => [
            ['locationType' => 'site', 'locationCode' => $this->site->code, 'locationId' => $this->site->id],
        ]
    ];

    $response = $this->postToTenant('api.contracts.store', $formData);
    $response->assertSessionHasNoErrors();

    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);

    assertDatabaseHas('contracts', [
        'provider_id' => $this->provider->id,
        'name' => 'Contrat de sécurité',
        'type' => $type,

    ]);
})->with(array_column(ContractTypesEnum::cases(), 'value'));

it('can create a contract with every contract status', function ($status) {

    $formData = [
        'provider_id' => $this->provider->id,
        'name' => 'Contrat de sécurité',
        'renewal_type' => ContractRenewalTypesEnum::AUTOMATIC->value,
        'status' => $status,
        'contract_duration' => ContractDurationEnum::ONE_MONTH->value,
        'contractables' => [
            ['locationType' => 'site', 'locationCode' => $this->site->code, 'locationId' => $this->site->id],
        ]
    ];

    $response = $this->postToTenant('api.contracts.store', $formData);
    $response->assertSessionHasNoErrors();

    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);

    assertDatabaseHas('contracts', [
        'provider_id' => $this->provider->id,
        'name' => 'Contrat de sécurité',
        'status' => $status,

    ]);
})->with(array_column(ContractStatusEnum::cases(), 'value'));


it('can create a contract without contract start date and calculates automatically start and end_date based on duration', function ($duration) {

    $formData = [
        'provider_id' => $this->provider->id,
        'name' => 'Contrat de sécurité',
        'renewal_type' => ContractRenewalTypesEnum::AUTOMATIC->value,
        'status' => ContractStatusEnum::ACTIVE->value,
        'contract_duration' => $duration,
        'contractables' => [
            ['locationType' => 'site', 'locationCode' => $this->site->code, 'locationId' => $this->site->id],
        ]
    ];

    $response = $this->postToTenant('api.contracts.store', $formData);
    $response->assertSessionHasNoErrors();

    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);

    assertDatabaseHas('contracts', [
        'provider_id' => $this->provider->id,
        'name' => 'Contrat de sécurité',
        'contract_duration' => $duration,
        'start_date' => Carbon::now()->toDateString(),
        'end_date' => ContractDurationEnum::from($duration)->addTo(Carbon::now())->toDateString()

    ]);
})->with(array_column(ContractDurationEnum::cases(), 'value'));

it('can create a contract and attach asset and locations', function () {

    $formData = [
        ...$this->contractOneData,
        'contractables' => [
            ['locationType' => 'site', 'locationCode' => $this->site->code, 'locationId' => $this->site->id],
            ['locationType' => 'asset', 'locationCode' => $this->asset->code, 'locationId' => $this->asset->id],
            ['locationType' => 'building', 'locationCode' => $this->building->code, 'locationId' => $this->building->id],
            ['locationType' => 'floor', 'locationCode' => $this->floor->code, 'locationId' => $this->floor->id],
            ['locationType' => 'room', 'locationCode' => $this->room->code, 'locationId' => $this->room->id]
        ]
    ];

    $response = $this->postToTenant('api.contracts.store', $formData);
    $response->assertSessionHasNoErrors();

    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);

    assertDatabaseHas('contracts', [
        'provider_id' => $this->provider->id,
        'name' => 'Contrat de bail',
        'type' => ContractTypesEnum::ALLIN->value,
        'notes' => 'Nouveau contrat de bail 2025',
        'internal_reference' => 'Bail Site 2025',
        'provider_reference' => 'Provider reference 2025',
        'start_date' => Carbon::now()->toDateString(),
        'contract_duration' => ContractDurationEnum::ONE_MONTH->value,
        'end_date' => Carbon::now()->addMonth()->toDateString(),
        'notice_period' => NoticePeriodEnum::FOURTEEN_DAYS->value,
        'notice_date' => Carbon::now()->addMonth()->subDays(14)->toDateString(),
        'renewal_type' => ContractRenewalTypesEnum::AUTOMATIC->value,
        'status' => ContractStatusEnum::ACTIVE->value,
    ]);

    assertDatabaseCount('contractables', 5);
});

it('can store a site with contracts', function () {

    $formData = [
        'name' => 'New site',
        'description' => 'Description new site',
        'locationType' => $this->siteType->id,

        'contracts' => [
            $this->contractOneData,
            $this->contractTwoData
        ]
    ];

    $response = $this->postToTenant('api.sites.store', $formData);
    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);

    assertDatabaseCount('contracts', 2);
    assertDatabaseHas(
        'contracts',
        $this->contractOneData,
    );
    assertDatabaseHas(
        'contracts',
        $this->contractTwoData
    );

    $site = Site::find(2);
    assertEquals(2, $site->contracts()->count());
});

it('can store a building with contracts', function () {

    $formData = [
        'name' => 'New building',
        'description' => 'Description new building',
        'levelType' => $this->site->id,
        'locationType' => $this->buildingType->id,

        'contracts' => [
            $this->contractOneData,
            $this->contractTwoData
        ]
    ];

    $response = $this->postToTenant('api.buildings.store', $formData);
    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);

    assertDatabaseCount('contracts', 2);
    assertDatabaseHas(
        'contracts',
        $this->contractOneData,
    );
    assertDatabaseHas(
        'contracts',
        $this->contractTwoData
    );

    $building = Building::find(2);
    assertEquals(2, $building->contracts()->count());
});

it('can store a floor with contracts', function () {

    $formData = [
        'name' => 'New floor',
        'description' => 'Description new floor',
        'levelType' => $this->building->id,
        'locationType' => $this->floorType->id,

        'contracts' => [
            $this->contractOneData,
            $this->contractTwoData
        ]
    ];

    $response = $this->postToTenant('api.floors.store', $formData);
    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);

    assertDatabaseCount('contracts', 2);
    assertDatabaseHas(
        'contracts',
        $this->contractOneData,
    );
    assertDatabaseHas(
        'contracts',
        $this->contractTwoData
    );

    $floor = Floor::find(2);
    assertEquals(2, $floor->contracts()->count());
});

it('can store a room with contracts', function () {

    $formData = [
        'name' => 'New room',
        'description' => 'Description new room',
        'levelType' => $this->floor->id,
        'locationType' => $this->roomType->id,

        'contracts' => [
            $this->contractOneData,
            $this->contractTwoData
        ]
    ];

    $response = $this->postToTenant('api.rooms.store', $formData);
    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);

    assertDatabaseCount('contracts', 2);
    assertDatabaseHas(
        'contracts',
        $this->contractOneData,
    );
    assertDatabaseHas(
        'contracts',
        $this->contractTwoData
    );

    $room = Room::find(2);
    assertEquals(2, $room->contracts()->count());
});

it('can update an existing contract', function () {
    $contract = Contract::factory()->forLocation($this->asset)->create();
    $provider = Provider::factory()->create();

    $formData =
        [
            'provider_id' => $provider->id,
            'name' => 'Contrat de bail',
            'type' => ContractTypesEnum::ONDEMAND->value,
            'notes' => 'Nouveau contrat de bail 2025',
            'internal_reference' => 'Bail Site 2025',
            'provider_reference' => 'Provider reference 2025',
            'start_date' => Carbon::now()->toDateString(),
            'contract_duration' => ContractDurationEnum::ONE_YEAR->value,
            'notice_period' => NoticePeriodEnum::FOURTEEN_DAYS->value,
            'renewal_type' => ContractRenewalTypesEnum::MANUAL->value,
            'status' => ContractStatusEnum::ACTIVE->value

        ];

    $response = $this->patchToTenant('api.contracts.update', $formData, $contract->id);
    $response->assertStatus(200)->assertJson(['status' => 'success']);

    assertDatabaseHas(
        'contracts',
        [
            'id' => $contract->id,
            'provider_id' => $provider->id,
            'name' => 'Contrat de bail',
            'type' => ContractTypesEnum::ONDEMAND->value,
            'notes' => 'Nouveau contrat de bail 2025',
            'internal_reference' => 'Bail Site 2025',
            'provider_reference' => 'Provider reference 2025',
            'start_date' => Carbon::now()->toDateString(),
            'contract_duration' => ContractDurationEnum::ONE_YEAR->value,
            'end_date' => Carbon::now()->addYear()->toDateString(),
            'notice_period' => NoticePeriodEnum::FOURTEEN_DAYS->value,
            'notice_date' => Carbon::now()->addYear()->subDays(14)->toDateString(),
            'renewal_type' => ContractRenewalTypesEnum::MANUAL->value,
            'status' => ContractStatusEnum::ACTIVE->value
        ]
    );
});

it('can delete a contract and delete contract\'s directory', function () {

    $contract = Contract::factory()->forLocation($this->asset)->create();

    $response = $this->deleteFromTenant('api.contracts.destroy', $contract->id);
    $response->assertStatus(200)->assertJson(['status' => 'success']);

    assertDatabaseEmpty('contracts');
    assertDatabaseEmpty('contractables');
    Storage::disk('tenants')->assertMissing($contract->directory);
});

it('can render the index page with all contracts', function () {

    $statuses = array_column(ContractStatusEnum::cases(), 'value');
    $renewalTypes = array_column(ContractRenewalTypesEnum::cases(), 'value');

    Contract::factory()->forLocation($this->asset)->count(2)->create();
    Contract::factory()->forLocation($this->site)->count(2)->create();

    $response = $this->getFromTenant('tenant.contracts.index');

    $response->assertInertia(
        fn($page) =>
        $page->component('tenants/contracts/IndexContracts')
            ->has('items.data', 4)
            ->has('statuses', count($statuses))
            ->has('renewalTypes', count($renewalTypes))
    );
});

it('can render the show contract page', function () {

    $contract = Contract::factory()->forLocation($this->asset)->create();

    $response = $this->getFromTenant('tenant.contracts.show', $contract->id);

    $response->assertInertia(
        fn($page) =>
        $page->component('tenants/contracts/ShowContract')
            ->has('item')
            ->where('item.id', $contract->id)
    );
});

it('can render the create contract page', function () {

    $response = $this->getFromTenant('tenant.contracts.create');

    $response->assertInertia(
        fn($page) =>
        $page->component('tenants/contracts/CreateUpdateContract')
    );
});

it('can render the edit contract page', function () {
    $contract = Contract::factory()->forLocation($this->asset)->create();
    $response = $this->getFromTenant('tenant.contracts.edit', $contract->id);

    $response->assertInertia(
        fn($page) =>
        $page->component('tenants/contracts/CreateUpdateContract')
            ->has('contract')
    );
});
