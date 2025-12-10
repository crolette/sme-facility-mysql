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

it('can factory a contract', function () {

    Contract::factory()->create();
    assertDatabaseCount('contracts', 1);
});

it('can create a contract without link to asset/location', function () {

    $formData = [
        ...$this->contractOneData,
    ];

    $response = $this->postToTenant('api.contracts.store', $formData);
    $response->assertSessionHasNoErrors();

    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);

    assertDatabaseHas('contracts', [
        ...$this->contractOneData,
    ]);
});

it('can create a contract without type and other will be the default type', function () {

    $formData = [
        ...$this->contractTwoData,
        'type' => null,

    ];

    $response = $this->postToTenant('api.contracts.store', $formData);
    $response->assertSessionHasNoErrors();

    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);

    assertDatabaseHas('contracts', [
        ...$this->contractTwoData,
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


it('can update an existing contract', function () {
    $contract = Contract::factory()->create();
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

    $contract = Contract::factory()->create();

    $response = $this->deleteFromTenant('api.contracts.destroy', $contract->id);
    $response->assertStatus(200)->assertJson(['status' => 'success']);

    assertDatabaseEmpty('contracts');
    assertDatabaseEmpty('contractables');
    Storage::disk('tenants')->assertMissing($contract->directory);
});
