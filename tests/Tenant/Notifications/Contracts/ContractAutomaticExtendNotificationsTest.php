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
use Illuminate\Support\Facades\Queue;
use App\Enums\ContractRenewalTypesEnum;
use App\Jobs\ProcessExpiredContractsJob;
use App\Models\Tenants\ScheduledNotification;

use function PHPUnit\Framework\assertCount;
use function Pest\Laravel\assertDatabaseHas;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertNotNull;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseMissing;

beforeEach(function () {

    $this->admin = User::factory()->withRole('Admin')->create();
    $this->actingAs($this->admin, 'tenant');

    $this->manager = User::factory()->withRole('Maintenance Manager')->create();

    $this->site = Site::factory()->create();
    $this->building = Building::factory()->create();
    $this->floor = Floor::factory()->create();
    $this->provider = Provider::factory()->create();

    $this->room = Room::factory()->create();

    $this->asset = Asset::factory()->forLocation($this->room)->create();
    $this->asset->refresh();

    $this->basicContractData = [
        'provider_id' => $this->provider->id,
        'name' => 'Contrat de bail',
        'type' => ContractTypesEnum::ALLIN->value,
        'notes' => 'Nouveau contrat de bail 2025',
        'internal_reference' => 'Bail Site 2025',
        'provider_reference' => 'Provider reference 2025',
        'start_date' => Carbon::now()->toDateString(),
        'contract_duration' => ContractDurationEnum::ONE_MONTH->value,
        // 'notice_period' => NoticePeriodEnum::FOURTEEN_DAYS->value,
        'renewal_type' => ContractRenewalTypesEnum::AUTOMATIC->value,
        'status' => ContractStatusEnum::ACTIVE->value,
        'contractables' => [
            ['locationType' => 'asset', 'locationCode' => $this->asset->code, 'locationId' => $this->asset->id],
        ]
    ];

    $this->basicAssetData = [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'locationId' => $this->site->id,
        'locationType' => 'site',
        'locationReference' => $this->site->reference_code,
        'categoryId' => CategoryType::where('category', 'asset')->first()->id,
    ];
});

it('creates the end_date notification for the admin for an extended automatic contract of an asset', function ($duration) {

    $preference = $this->admin->notification_preferences()->where('notification_type', 'end_date')->first();

    $contractTwo = Contract::factory()->forLocation($this->asset)->create([
        'contract_duration' => $duration,
        'renewal_type' => ContractRenewalTypesEnum::AUTOMATIC->value,
        'status' => ContractStatusEnum::ACTIVE->value,
        'start_date' => Carbon::now()->subYears(2)->subDays(1),
        'end_date' => ContractDurationEnum::from($duration)->addTo(Carbon::now()->subYears(2)->subDays(1))
    ]);

    $contractTwo = Contract::first();

    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'user_id' => $this->admin->id,
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'end_date',
            'scheduled_at' => $contractTwo->end_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($contractTwo),
            'notifiable_id' => $contractTwo->id,
        ]
    );

    $tenant = tenancy()->tenant;
    Queue::fake();
    $job = new ProcessExpiredContractsJob($tenant);
    dispatch($job);
    $job->handle();

    $contractTwo->refresh();

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->admin->id,
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'end_date',
            'scheduled_at' => $contractTwo->end_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($contractTwo),
            'notifiable_id' => $contractTwo->id,
        ]
    );
})->with(array_column(ContractDurationEnum::cases(), 'value'));

it('creates the end_date notification for the maintenance manager for an automatic extended contract', function ($duration) {

    $this->asset->maintainable()->update(['maintenance_manager_id' => $this->manager->id]);

    $preference = $this->manager->notification_preferences()->where('notification_type', 'end_date')->first();

    $contract = Contract::factory()->forLocation($this->asset)->create([
        'contract_duration' => $duration,
        'renewal_type' => ContractRenewalTypesEnum::AUTOMATIC->value,
        'status' => ContractStatusEnum::ACTIVE->value,
        'start_date' => Carbon::now()->subYears(2)->subDays(1),
        'end_date' => ContractDurationEnum::from($duration)->addTo(Carbon::now()->subYears(2)->subDays(1))
    ]);

    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_date',
            'scheduled_at' => $contract->end_date?->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($contract),
            'notifiable_id' => $contract->id,
        ]
    );

    $tenant = tenancy()->tenant;
    Queue::fake();
    $job = new ProcessExpiredContractsJob($tenant);
    dispatch($job);
    $job->handle();

    $contract->refresh();

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_date',
            'scheduled_at' => $contract->end_date?->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($contract),
            'notifiable_id' => $contract->id,
        ]
    );
})->with(array_column(ContractDurationEnum::cases(), 'value'));

it('creates new end_date notification for the admin for an extended automatic contract of an asset and does not update sent notifications', function ($duration) {

    $preference = $this->admin->notification_preferences()->where('notification_type', 'end_date')->first();

    $contractOne = Contract::factory()->forLocation($this->asset)->create([
        'contract_duration' => $duration,
        'start_date' => Carbon::now(),
        'end_date' => ContractDurationEnum::from($duration)->addTo(Carbon::now())
    ]);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->admin->id,
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'end_date',
            'scheduled_at' => $contractOne->end_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($contractOne),
            'notifiable_id' => $contractOne->id,
            'status' => 'pending'
        ]
    );

    $notification = ScheduledNotification::where('notification_type', 'end_date')->where('notifiable_type', get_class($contractOne))->where('notifiable_id', $contractOne->id)->where('user_id', $this->admin->id)->first();

    $contractOne->update(['end_date' => Carbon::yesterday()]);
    $notification->update(['status' => 'sent']);


    $tenant = tenancy()->tenant;
    Queue::fake();
    $job = new ProcessExpiredContractsJob($tenant);
    dispatch($job);
    $job->handle();



    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->admin->id,
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'end_date',
            // 'scheduled_at' => $contractOne->end_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($contractOne),
            'notifiable_id' => $contractOne->id,
            'status' => 'sent'
        ]
    );

    // $updatedContractOne = Contract::first();

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->admin->id,
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'end_date',
            // 'scheduled_at' => $contractOne->end_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($contractOne),
            'notifiable_id' => $contractOne->id,
            'status' => 'pending'
        ]
    );
})->with(array_column(ContractDurationEnum::cases(), 'value'));

it('creates the notice_date notification for the admin for an extended automatic contract of an asset', function ($duration, $period) {

    $preference = $this->admin->notification_preferences()->where('notification_type', 'end_date')->first();


    $endDate = ContractDurationEnum::from($duration)->addTo(Carbon::now()->subYears(2)->subDays(1));
    $noticeDate = NoticePeriodEnum::from($period)->subFrom($endDate);

    $contractTwo = Contract::factory()->forLocation($this->asset)->create([
        'contract_duration' => $duration,
        'renewal_type' => ContractRenewalTypesEnum::AUTOMATIC->value,
        'status' => ContractStatusEnum::ACTIVE->value,
        'notice_period' => $period,
        'notice_date' => $noticeDate,
        'start_date' => Carbon::now()->subYears(2)->subDays(1),
        'end_date' => $endDate
    ]);

    $contractTwo = Contract::first();

    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'user_id' => $this->admin->id,
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'notice_date',
            'scheduled_at' => $contractTwo->notice_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($contractTwo),
            'notifiable_id' => $contractTwo->id,
        ]
    );

    $tenant = tenancy()->tenant;
    Queue::fake();
    $job = new ProcessExpiredContractsJob($tenant);
    dispatch($job);
    $job->handle();

    $contractTwo->refresh();

    if ($noticeDate >= Carbon::now()->toDateString()) {
        assertDatabaseHas(
            'scheduled_notifications',
            [
                'user_id' => $this->admin->id,
                'recipient_name' => $this->admin->fullName,
                'recipient_email' => $this->admin->email,
                'notification_type' => 'notice_date',
                'scheduled_at' => $contractTwo->notice_date->subDays($preference->notification_delay_days)->toDateString(),
                'notifiable_type' => get_class($contractTwo),
                'notifiable_id' => $contractTwo->id,
            ]
        );
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


it('creates the notice_date notification for the maintenance manager for an extended automatic contract of an asset', function ($duration, $period) {

    $this->asset->maintainable()->update(['maintenance_manager_id' => $this->manager->id]);

    $preference = $this->manager->notification_preferences()->where('notification_type', 'end_date')->first();

    $preference = $this->admin->notification_preferences()->where('notification_type', 'end_date')->first();


    $endDate = ContractDurationEnum::from($duration)->addTo(Carbon::now()->subYears(2)->subDays(1));
    $noticeDate = NoticePeriodEnum::from($period)->subFrom($endDate);

    $contractTwo = Contract::factory()->forLocation($this->asset)->create([
        'contract_duration' => $duration,
        'renewal_type' => ContractRenewalTypesEnum::AUTOMATIC->value,
        'status' => ContractStatusEnum::ACTIVE->value,
        'notice_period' => $period,
        'notice_date' => $noticeDate,
        'start_date' => Carbon::now()->subYears(2)->subDays(1),
        'end_date' => $endDate
    ]);

    $contractTwo = Contract::first();

    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'notice_date',
            'scheduled_at' => $contractTwo->notice_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($contractTwo),
            'notifiable_id' => $contractTwo->id,
        ]
    );

    $tenant = tenancy()->tenant;
    Queue::fake();
    $job = new ProcessExpiredContractsJob($tenant);
    dispatch($job);
    $job->handle();

    $contractTwo->refresh();

    if ($noticeDate >= Carbon::now()->toDateString()) {
        assertDatabaseHas(
            'scheduled_notifications',
            [
                'user_id' => $this->manager->id,
                'recipient_name' => $this->manager->fullName,
                'recipient_email' => $this->manager->email,
                'notification_type' => 'notice_date',
                'scheduled_at' => $contractTwo->notice_date->subDays($preference->notification_delay_days)->toDateString(),
                'notifiable_type' => get_class($contractTwo),
                'notifiable_id' => $contractTwo->id,
            ]
        );
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
