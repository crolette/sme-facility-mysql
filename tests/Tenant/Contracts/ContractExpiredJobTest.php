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

use App\Jobs\ProcessExpiredContractsJob;
use function PHPUnit\Framework\assertCount;
use function Pest\Laravel\assertDatabaseHas;
use function PHPUnit\Framework\assertEquals;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseMissing;

beforeEach(function () {
    $this->user = User::factory()->withRole('Admin')->create();
    $this->actingAs($this->user, 'tenant');

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

it('change contract status if end_date < now & status = active', function () {

    $expiredContract = Contract::factory()->create([...$this->contractOneData, 'start_date' => Carbon::now()->subYear(), 'end_date' => Carbon::yesterday()]);

    $activeContract = Contract::factory()->create([...$this->contractTwoData]);

    $tenant = tenancy()->tenant;

    Queue::fake();
    $job = new ProcessExpiredContractsJob($tenant);
    dispatch($job);
    // Assert the job was pushed to the queue
    Queue::assertPushed(ProcessExpiredContractsJob::class);

    $job->handle();

    assertDatabaseHas('contracts', [
        'id' => $expiredContract->id,
        'status' => ContractStatusEnum::EXPIRED
    ]);

    assertDatabaseHas('contracts', [
        'id' => $activeContract->id,
        'status' => ContractStatusEnum::ACTIVE
    ]);
});

it('does not change contract status if end_date >= now', function () {

    $contractNow = Contract::factory()->create([...$this->contractOneData, 'start_date' => Carbon::now()->subYear(), 'end_date' => Carbon::now()->toDateString()]);

    assertDatabaseHas('contracts', [
        'id' => $contractNow->id,
        'status' => ContractStatusEnum::ACTIVE
    ]);

    $activeContract = Contract::factory()->create([...$this->contractTwoData]);

    $tenant = tenancy()->tenant;

    Queue::fake();
    $job = new ProcessExpiredContractsJob($tenant);
    dispatch($job);
    // Assert the job was pushed to the queue
    Queue::assertPushed(ProcessExpiredContractsJob::class);

    $job->handle();

    assertDatabaseHas('contracts', [
        'id' => $contractNow->id,
        'status' => ContractStatusEnum::ACTIVE
    ]);

    assertDatabaseHas('contracts', [
        'id' => $activeContract->id,
        'status' => ContractStatusEnum::ACTIVE
    ]);
});

it('does not change contract status if end_date < now & status != active', function () {

    $expiredContract = Contract::factory()->create([...$this->contractOneData, 'start_date' => Carbon::now()->subYear(), 'end_date' => Carbon::yesterday(),]);
    $cancelledContract = Contract::factory()->create([...$this->contractOneData, 'start_date' => Carbon::now()->subYear(), 'end_date' => Carbon::yesterday(), 'status' => ContractStatusEnum::CANCELLED]);
    $activeContract = Contract::factory()->create([...$this->contractTwoData]);

    $tenant = tenancy()->tenant;

    Queue::fake();
    $job = new ProcessExpiredContractsJob($tenant);
    dispatch($job);
    // Assert the job was pushed to the queue
    Queue::assertPushed(ProcessExpiredContractsJob::class);

    $job->handle();

    assertDatabaseHas('contracts', [
        'id' => $expiredContract->id,
        'status' => ContractStatusEnum::EXPIRED
    ]);

    assertDatabaseHas('contracts', [
        'id' => $cancelledContract->id,
        'status' => ContractStatusEnum::CANCELLED
    ]);

    assertDatabaseHas('contracts', [
        'id' => $activeContract->id,
        'status' => ContractStatusEnum::ACTIVE
    ]);
});
