<?php

use Carbon\Carbon;
use App\Models\LocationType;
use App\Models\Tenants\Room;
use App\Models\Tenants\Site;
use App\Models\Tenants\User;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Floor;
use App\Models\Tenants\Building;
use App\Models\Tenants\Contract;

use App\Models\Tenants\Provider;
use App\Enums\ContractStatusEnum;
use Illuminate\Http\UploadedFile;
use App\Models\Central\CategoryType;

use App\Enums\ContractRenewalTypesEnum;
use function PHPUnit\Framework\assertCount;
use function Pest\Laravel\assertDatabaseHas;
use function PHPUnit\Framework\assertEquals;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseMissing;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->user->assignRole('Admin');
    $this->actingAs($this->user, 'tenant');

    LocationType::factory()->create(['level' => 'site']);
    LocationType::factory()->create(['level' => 'building']);
    LocationType::factory()->create(['level' => 'floor']);
    LocationType::factory()->create(['level' => 'room']);
    CategoryType::factory()->count(2)->create(['category' => 'document']);
    $this->categoryType = CategoryType::factory()->create(['category' => 'asset']);
    CategoryType::factory()->count(2)->create(['category' => 'provider']);
    CategoryType::factory()->count(2)->create(['category' => 'asset']);
    $this->site = Site::factory()->create();
    $this->building = Building::factory()->create();
    $this->floor = Floor::factory()->create();
    $this->provider = Provider::factory()->create();

    $this->room = Room::factory()
        ->for(LocationType::where('level', 'room')->first())
        ->for(Floor::first())
        ->create();

    $this->asset = Asset::factory()->forLocation(Room::first())->create();
    $this->contractOneData = [
        'provider_id' => $this->provider->id,
        'name' => 'Contrat de bail',
        'type' => 'Bail',
        'notes' => 'Nouveau contrat de bail 2025',
        'internal_reference' => 'Bail Site 2025',
        'provider_reference' => 'Provider reference 2025',
        'start_date' => Carbon::now()->toDateString(),
        'end_date' => Carbon::now()->addYear()->toDateString(),
        'renewal_type' => ContractRenewalTypesEnum::AUTOMATIC->value,
        'status' => ContractStatusEnum::ACTIVE->value
    ];

    $this->contractTwoData = [
        'provider_id' => $this->provider->id,
        'name' => 'Contrat de sécurité',
        'type' => 'Sécurité',
        'notes' => 'Nouveau contrat de Sécurité 2025',
        'internal_reference' => 'Sécurité Site 2025',
        'provider_reference' => 'Provider reference 2025',
        'start_date' => Carbon::now()->toDateString(),
        'end_date' => Carbon::now()->addYear()->toDateString(),
        'renewal_type' => ContractRenewalTypesEnum::AUTOMATIC->value,
        'status' => ContractStatusEnum::ACTIVE->value
    ];
});

it('can factory a contract', function () {

    // Contract::factory()
    //     ->hasAttached(
    //         Asset::factory()->count(2), // Attache 2 assets au contrat
    //         [],
    //         'contractables' // Nom de la relation pivot
    //     )
    //     ->create();

    Contract::factory()->forLocation($this->asset)->create();
    assertDatabaseCount('contracts', 1);
    assertDatabaseCount('contractables', 1);
    assertEquals(1, $this->asset->contracts()->count());
});

it('can store a contract with asset and locations', function () {

    $formData = [
        'provider_id' => $this->provider->id,
        'name' => 'Contrat de bail',
        'type' => 'Bail',
        'notes' => 'Nouveau contrat de bail 2025',
        'internal_reference' => 'Bail Site 2025',
        'provider_reference' => 'Provider reference 2025',
        'start_date' => Carbon::now()->toDateString(),
        'end_date' => Carbon::now()->addYear()->toDateString(),
        'renewal_type' => ContractRenewalTypesEnum::AUTOMATIC->value,
        'status' => ContractStatusEnum::ACTIVE->value,
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

    assertDatabaseCount('contracts', 1);
    assertDatabaseCount('contractables', 5);
});

it('can store an asset with contracts', function () {

    $formData = [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'locationId' => $this->site->id,
        'locationType' => 'site',
        'locationReference' => $this->site->reference_code,
        'categoryId' => $this->categoryType->id,

        'contracts' => [
            $this->contractOneData,
            $this->contractTwoData
        ]

    ];

    $response = $this->postToTenant('api.assets.store', $formData);
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

    $asset = Asset::find(2);
    assertEquals(2, $asset->contracts()->count());
});

it('can update an existing contract', function () {
    $contract = Contract::factory()->forLocation($this->asset)->create();
    $provider = Provider::factory()->create();

    $formData =
        [
            'provider_id' => $provider->id,
            'name' => 'Contrat de bail',
            'type' => 'Bail',
            'notes' => 'Nouveau contrat de bail 2025',
            'internal_reference' => 'Bail Site 2025',
            'provider_reference' => 'Provider reference 2025',
            'start_date' => Carbon::now()->toDateString(),
            'end_date' => Carbon::now()->addYear()->toDateString(),
            'renewal_type' => ContractRenewalTypesEnum::MANUAL->value,
            'status' => ContractStatusEnum::CANCELLED->value

        ];

    $response = $this->patchToTenant('api.contracts.update', $formData, $contract->id);
    $response->assertStatus(200)->assertJson(['status' => 'success']);

    assertDatabaseHas(
        'contracts',
        [
            'id' => $contract->id,
            'provider_id' => $provider->id,
            'name' => 'Contrat de bail',
            'type' => 'Bail',
            'notes' => 'Nouveau contrat de bail 2025',
            'internal_reference' => 'Bail Site 2025',
            'provider_reference' => 'Provider reference 2025',
            'start_date' => Carbon::now()->toDateString(),
            'end_date' => Carbon::now()->addYear()->toDateString(),
            'renewal_type' => ContractRenewalTypesEnum::MANUAL->value,
            'status' => ContractStatusEnum::CANCELLED->value
        ]
    );
});

it('can delete a contract', function () {

    $contract = Contract::factory()->forLocation($this->asset)->create();

    $response = $this->deleteFromTenant('api.contracts.destroy', $contract->id);
    $response->assertStatus(200)->assertJson(['status' => 'success']);

    assertDatabaseEmpty('contracts');
    assertDatabaseEmpty('contractables');
});

it('can render the index page with all contracts', function () {

    $statuses = array_column(ContractStatusEnum::cases(), 'value');
    $renewalTypes = array_column(ContractRenewalTypesEnum::cases(), 'value');

    Contract::factory()->forLocation($this->asset)->count(2)->create();
    Contract::factory()->forLocation($this->site)->count(2)->create();

    $response = $this->getFromTenant('tenant.contracts.index');

    $response->assertInertia(
        fn($page) =>
        $page->component('tenants/contracts/index')
            ->has('items', 4)
            ->has('statuses', count($statuses))
            ->has('renewalTypes', count($renewalTypes))
    );
});

it('can render the show contract page', function () {

    $contract = Contract::factory()->forLocation($this->asset)->create();

    $response = $this->getFromTenant('tenant.contracts.show', $contract->id);

    $response->assertInertia(
        fn($page) =>
        $page->component('tenants/contracts/show')
            ->has('item')
            ->where('item.id', $contract->id)
    );
});

it('can render the create contract page', function () {

    $response = $this->getFromTenant('tenant.contracts.create');

    $response->assertInertia(
        fn($page) =>
        $page->component('tenants/contracts/create')
    );
});

it('can render the edit contract page', function () {
    $contract = Contract::factory()->forLocation($this->asset)->create();
    $response = $this->getFromTenant('tenant.contracts.edit', $contract->id);

    $response->assertInertia(
        fn($page) =>
        $page->component('tenants/contracts/create')
            ->has('contract')
    );
});
