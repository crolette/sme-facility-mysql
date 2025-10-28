<?php

use Carbon\Carbon;
use App\Models\LocationType;
use App\Models\Tenants\Room;
use App\Models\Tenants\Site;
use App\Models\Tenants\User;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Floor;
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
    $this->room = Room::factory()->create();
    $this->asset = Asset::factory()->forLocation(Room::first())->create();

    $this->provider = Provider::factory()->create();

    $this->contractOneData = [
        'provider_id' => $this->provider->id,
        'name' => 'Contrat de bail',
        'type' => 'Bail',
        'notes' => 'Nouveau contrat de bail 2025',
        'internal_reference' => 'Bail Site 2025',
        'provider_reference' => 'Provider reference 2025',
        'start_date' => Carbon::now()->toDateString(),
        'contract_duration' => ContractDurationEnum::ONE_MONTH->value,
        'renewal_type' => ContractRenewalTypesEnum::AUTOMATIC->value,
        'status' => ContractStatusEnum::ACTIVE->value
    ];
});

it('can add a contract without start_date and calculates automatically the end_date based on contract_duration', function ($duration) {

    $formData = [
        ...$this->contractOneData,
        'contract_duration' => $duration,
    ];

    $response = $this->postToTenant('api.contracts.store', $formData);
    $response->assertSessionHasNoErrors();
    $response->assertStatus(200);

    assertDatabaseHas('contracts', [
        'provider_id' => $this->provider->id,
        'name' => 'Contrat de bail',
        'start_date' => Carbon::now()->toDateString(),
        'contract_duration' => $duration,
        'end_date' => ContractDurationEnum::from($duration)->addTo(Carbon::now())->toDateString(),
    ]);
})->with(array_column(ContractDurationEnum::cases(), 'value'));

it('can add a contract with a start_date in the past', function ($duration) {

    $formData = [
        ...$this->contractOneData,
        'start_date' => '2020-10-27',
        'contract_duration' => $duration,
    ];

    $response = $this->postToTenant('api.contracts.store', $formData);
    $response->assertSessionHasNoErrors();
    $response->assertStatus(200);

    assertDatabaseHas('contracts', [
        'provider_id' => $this->provider->id,
        'name' => 'Contrat de bail',
        'start_date' => '2020-10-27',
        'contract_duration' => $duration,
        'end_date' => ContractDurationEnum::from($duration)->addTo(Carbon::createFromFormat('Y-m-d', '2020-10-27'))->toDateString(),
    ]);
})->with(array_column(ContractDurationEnum::cases(), 'value'));

it('can create a contract with notice_period and raise error if notice_date <= start_date', function ($duration, $period) {

    $endDate = ContractDurationEnum::from($duration)->addTo(Carbon::now());
    $noticeDate = NoticePeriodEnum::from($period)->subFrom($endDate)->toDateString();

    $formData = [
        ...$this->contractOneData,
        'contract_duration' => $duration,
        'notice_period' => $period
    ];

    $response = $this->postToTenant('api.contracts.store', $formData);

    if ($noticeDate <= Carbon::now()->toDateString()) {
        $response->assertSessionHasErrors(["notice_period" => "Wrong notice period : Should be smaller than contract duration."]);
    } else {
        $response->assertSessionHasNoErrors();
        $response->assertStatus(200);

        assertDatabaseHas('contracts', [
            'provider_id' => $this->provider->id,
            'name' => 'Contrat de bail',
            'start_date' => Carbon::now()->toDateString(),
            'contract_duration' => $duration,
            'end_date' => $endDate->toDateString(),
            'notice_period' => $period,
            'notice_date' => $noticeDate,
        ]);
    }
})->with(function () {
    $notices = array_column(NoticePeriodEnum::cases(), 'value');
    $durations = array_column(ContractDurationEnum::cases(), 'value');

    $combinations = [];
    foreach ($durations as $d) {
        foreach ($notices as $n) {
            $combinations[] = [$d, $n];
        }
    }
    return $combinations;
});

it('can create a contract with status', function ($status) {

    $formData = [
        ...$this->contractOneData,
        'status' => $status,
    ];

    $response = $this->postToTenant('api.contracts.store', $formData);
    $response->assertStatus(200);

    assertDatabaseHas('contracts', [
        'provider_id' => $this->provider->id,
        'name' => 'Contrat de bail',
        'status' => $status,
    ]);
})->with(array_column(ContractStatusEnum::cases(), 'value'));

it('cannot create a contract with a non-existing status', function () {

    $formData = [
        ...$this->contractOneData,
        'status' => 'test',
    ];

    $response = $this->postToTenant('api.contracts.store', $formData);
    $response->assertSessionHasErrors(['status' => 'The selected status is invalid.']);

    assertDatabaseMissing('contracts', [
        'name' => 'Contrat de bail',
        'status' => 'test',
    ]);
});

it('can create a contract with renewal_type', function ($renewalType) {

    $formData = [
        ...$this->contractOneData,
        'renewal_type' => $renewalType,
    ];

    $response = $this->postToTenant('api.contracts.store', $formData);
    $response->assertStatus(200);

    assertDatabaseHas('contracts', [
        'provider_id' => $this->provider->id,
        'name' => 'Contrat de bail',
        'renewal_type' => $renewalType,
    ]);
})->with(array_column(ContractRenewalTypesEnum::cases(), 'value'));

it('cannot create a contract with a non-existing renewal_type', function () {

    $formData = [
        ...$this->contractOneData,
        'renewal_type' => 'test',
    ];

    $response = $this->postToTenant('api.contracts.store', $formData);
    $response->assertSessionHasErrors(['renewal_type' => 'The selected renewal type is invalid.']);

    assertDatabaseMissing('contracts', [
        'name' => 'Contrat de bail',
        'renewal_type' => 'test',
    ]);
});
