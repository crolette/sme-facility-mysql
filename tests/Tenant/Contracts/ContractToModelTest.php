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
use App\Models\Tenants\Contractable;

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

// it('can factory a contract and attach a model', function () {

//     Contract::factory()->forLocation($this->asset)->create();
//     assertDatabaseCount('contracts', 1);
//     assertDatabaseCount('contractables', 1);
//     assertEquals(1, $this->asset->contracts()->count());
// });

// it('can create a contract and attach asset and locations', function () {

//     $formData = [
//         ...$this->contractOneData,
//         'contractables' => [
//             ['locationType' => 'site', 'locationCode' => $this->site->code, 'locationId' => $this->site->id],
//             ['locationType' => 'asset', 'locationCode' => $this->asset->code, 'locationId' => $this->asset->id],
//             ['locationType' => 'building', 'locationCode' => $this->building->code, 'locationId' => $this->building->id],
//             ['locationType' => 'floor', 'locationCode' => $this->floor->code, 'locationId' => $this->floor->id],
//             ['locationType' => 'room', 'locationCode' => $this->room->code, 'locationId' => $this->room->id]
//         ]
//     ];

//     $response = $this->postToTenant('api.contracts.store', $formData);
//     $response->assertSessionHasNoErrors();

//     $response->assertStatus(200)
//         ->assertJson(['status' => 'success']);

//     assertDatabaseHas('contracts', [
//         ...$this->contractOneData,
//     ]);

//     $contract = Contract::find(1);
//     expect(count($contract->contractables()))->toBe(5);

//     assertDatabaseCount('contractables', 5);
//     expect(count($this->asset->contracts))->toBe(1);
//     expect(count($this->site->contracts))->toBe(1);
//     expect(count($this->building->contracts))->toBe(1);
//     expect(count($this->floor->contracts))->toBe(1);
//     expect(count($this->room->contracts))->toBe(1);
// });

// it('can update a contract and sync assets and locations to add', function () {
//     $contract =  Contract::factory()->forLocation($this->asset)->create();

//     $contract = Contract::find(1);
//     expect(count($contract->contractables()))->toBe(1);

//     assertDatabaseCount('contractables', 1);
//     expect(count($this->asset->contracts))->toBe(1);

//     $formData = [
//         ...$this->contractOneData,
//         'contractables' => [
//             ['locationType' => 'site', 'locationCode' => $this->site->code, 'locationId' => $this->site->id],
//             ['locationType' => 'asset', 'locationCode' => $this->asset->code, 'locationId' => $this->asset->id],
//             ['locationType' => 'building', 'locationCode' => $this->building->code, 'locationId' => $this->building->id],
//             ['locationType' => 'floor', 'locationCode' => $this->floor->code, 'locationId' => $this->floor->id],
//             ['locationType' => 'room', 'locationCode' => $this->room->code, 'locationId' => $this->room->id]
//         ]
//     ];

//     $response = $this->patchToTenant('api.contracts.update', $formData, $contract->id);
//     $response->assertSessionHasNoErrors();

//     $response->assertStatus(200)
//         ->assertJson(['status' => 'success']);

//     assertDatabaseHas('contracts', [
//         ...$this->contractOneData,
//     ]);

//     $contract = Contract::find(1);
//     expect(count($contract->contractables()))->toBe(5);

//     assertDatabaseCount('contractables', 5);
//     expect(count($this->asset->contracts))->toBe(1);
//     expect(count($this->site->contracts))->toBe(1);
//     expect(count($this->building->contracts))->toBe(1);
//     expect(count($this->floor->contracts))->toBe(1);
//     expect(count($this->room->contracts))->toBe(1);
// });

it('can update a contract and sync assets and locations to detach', function () {
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
        ...$this->contractOneData,
    ]);

    $contract = Contract::find(1);

    $formData = [
        ...$this->contractOneData,
        'contractables' => [
            ['locationType' => 'asset', 'locationCode' => $this->asset->code, 'locationId' => $this->asset->id],
        ]
    ];

    $response = $this->patchToTenant('api.contracts.update', $formData, $contract->id);
    $response->assertSessionHasNoErrors();

    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);

    assertDatabaseCount('contractables', 1);
    expect(count($this->asset->contracts))->toBe(1);
    expect(count($this->site->contracts))->toBe(0);
    expect(count($this->building->contracts))->toBe(0);
    expect(count($this->floor->contracts))->toBe(0);
    expect(count($this->room->contracts))->toBe(0);
});

it('can store a site with new contracts', function () {

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

it('can detach a contract from a site', function () {

    $contract = Contract::factory()->forLocation($this->site)->create();
    $contractToDetach = Contract::factory()->forLocation($this->site)->create();
    assertDatabaseCount('contracts', 2);
    assertDatabaseCount('contractables', 2);
    assertEquals(2, $this->site->contracts()->count());

    $formData = ['contract_id' => $contractToDetach->id];


    $response = $this->deleteFromTenant('api.sites.contracts.delete', $this->site, $formData);
    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);

    assertDatabaseCount('contracts', 2);
    assertDatabaseCount('contractables', 1);
    assertDatabaseMissing(
        'contractables',
        [
            'contract_id' => $contractToDetach->id,
            'contractable_type' => get_class($this->site),
            'contractable_id' => $this->site->id
        ]
    );

    assertEquals(1, $this->site->contracts()->count());
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

it('can detach a contract from a building', function () {

    $contract = Contract::factory()->forLocation($this->building)->create();
    $contractToDetach = Contract::factory()->forLocation($this->building)->create();
    assertDatabaseCount('contracts', 2);
    assertDatabaseCount('contractables', 2);
    assertEquals(2, $this->building->contracts()->count());

    $formData = ['contract_id' => $contractToDetach->id];


    $response = $this->deleteFromTenant('api.buildings.contracts.delete', $this->building, $formData);
    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);

    assertDatabaseCount('contracts', 2);
    assertDatabaseCount('contractables', 1);
    assertDatabaseMissing(
        'contractables',
        [
            'contract_id' => $contractToDetach->id,
            'contractable_type' => get_class($this->building),
            'contractable_id' => $this->building->id
        ]
    );

    assertEquals(1, $this->building->contracts()->count());
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

it('can detach a contract from a floor', function () {

    $contract = Contract::factory()->forLocation($this->floor)->create();
    $contractToDetach = Contract::factory()->forLocation($this->floor)->create();
    assertDatabaseCount('contracts', 2);
    assertDatabaseCount('contractables', 2);
    assertEquals(2, $this->floor->contracts()->count());

    $formData = ['contract_id' => $contractToDetach->id];


    $response = $this->deleteFromTenant('api.floors.contracts.delete', $this->floor, $formData);
    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);

    assertDatabaseCount('contracts', 2);
    assertDatabaseCount('contractables', 1);
    assertDatabaseMissing(
        'contractables',
        [
            'contract_id' => $contractToDetach->id,
            'contractable_type' => get_class($this->floor),
            'contractable_id' => $this->floor->id
        ]
    );

    assertEquals(1, $this->floor->contracts()->count());
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

it('can detach a contract from a room', function () {

    $contract = Contract::factory()->forLocation($this->room)->create();
    $contractToDetach = Contract::factory()->forLocation($this->room)->create();
    assertDatabaseCount('contracts', 2);
    assertDatabaseCount('contractables', 2);
    assertEquals(2, $this->room->contracts()->count());

    $formData = ['contract_id' => $contractToDetach->id];


    $response = $this->deleteFromTenant('api.rooms.contracts.delete', $this->room, $formData);
    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);

    assertDatabaseCount('contracts', 2);
    assertDatabaseCount('contractables', 1);
    assertDatabaseMissing(
        'contractables',
        [
            'contract_id' => $contractToDetach->id,
            'contractable_type' => get_class($this->room),
            'contractable_id' => $this->room->id
        ]
    );

    assertEquals(1, $this->room->contracts()->count());
});

it('can store an asset with contracts', function () {

    $formData = [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'locationId' => $this->room->id,
        'locationType' => 'room',
        'locationReference' => $this->room->reference_code,
        'categoryId' => $this->assetType->id,

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

it('can detach a contract from an asset', function () {

    $contract = Contract::factory()->forLocation($this->asset)->create();
    $contractToDetach = Contract::factory()->forLocation($this->asset)->create();
    assertDatabaseCount('contracts', 2);
    assertDatabaseCount('contractables', 2);
    assertEquals(2, $this->asset->contracts()->count());

    $formData = ['contract_id' => $contractToDetach->id];


    $response = $this->deleteFromTenant('api.assets.contracts.delete', $this->asset, $formData);
    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);

    assertDatabaseCount('contracts', 2);
    assertDatabaseCount('contractables', 1);
    assertDatabaseMissing(
        'contractables',
        [
            'contract_id' => $contractToDetach->id,
            'contractable_type' => get_class($this->asset),
            'contractable_id' => $this->asset->id
        ]
    );

    assertEquals(1, $this->asset->contracts()->count());
});
