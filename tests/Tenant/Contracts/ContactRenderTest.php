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
    $this->assetType = CategoryType::factory()->create(['category' => 'asset']);
    $this->site = Site::factory()->withMaintainableData()->create();
    $this->building = Building::factory()->withMaintainableData()->create();
    $this->floor = Floor::factory()->withMaintainableData()->create();
    $this->provider = Provider::factory()->create();

    $this->room = Room::factory()->withMaintainableData()->create();

    $this->asset = Asset::factory()->withMaintainableData()->forLocation(Room::first())->create();
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
