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
use App\Models\Tenants\Document;
use App\Models\Tenants\Provider;
use App\Enums\ContractStatusEnum;
use Illuminate\Http\UploadedFile;
use App\Enums\ContractDurationEnum;
use App\Enums\MaintenanceFrequency;
use App\Models\Central\CategoryType;
use App\Enums\ContractRenewalTypesEnum;
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

    $this->provider = Provider::factory()->create();
    $this->site = Site::factory()->withMaintainableData()->create();
    $this->building = Building::factory()->withMaintainableData()->create();
    $this->floor = Floor::factory()->withMaintainableData()->create();
    $this->room = Room::factory()->withMaintainableData()->create();
    $this->asset = Asset::factory()->withMaintainableData()->forLocation($this->room)->create();

    $this->basicContractData = [
        'provider_id' => $this->provider->id,
        'name' => 'Contrat de bail',
        'type' => ContractTypesEnum::MAINTENANCE->value,
        'notes' => 'Nouveau contrat de bail 2025',
        'internal_reference' => 'Bail Site 2025',
        'provider_reference' => 'Provider reference 2025',
        'start_date' => Carbon::now()->toDateString(),
        'contract_duration' => ContractDurationEnum::ONE_MONTH->value,
        'notice_period' => NoticePeriodEnum::FOURTEEN_DAYS->value,
        'renewal_type' => ContractRenewalTypesEnum::AUTOMATIC->value,
        'status' => ContractStatusEnum::ACTIVE->value,
        'contractables' => [
            ['locationType' => 'asset', 'locationCode' => $this->asset->code, 'locationId' => $this->asset->id],
            ['locationType' => 'site', 'locationCode' => $this->site->code, 'locationId' => $this->site->id],
        ]
    ];
});

it('creates the notice_date notification for a maintenance manager when a new contract is created', function ($duration, $period) {

    $this->asset->maintainable()->update(['maintenance_manager_id' => $this->manager->id]);

    $endDate = ContractDurationEnum::from($duration)->addTo(Carbon::now());
    $noticeDate = NoticePeriodEnum::from($period)->subFrom($endDate)->toDateString();

    $formData = [
        ...$this->basicContractData,
        'contract_duration' => $duration,
        'notice_period' => $period,
    ];

    $this->postToTenant('api.contracts.store', $formData);

    if ($noticeDate <= Carbon::now()->toDateString()) {
        assertDatabaseEmpty('scheduled_notifications');
    } else {

        $preference = $this->manager->notification_preferences()->where('notification_type', 'notice_date')->first();

        $contract = Contract::find(1);

        assertDatabaseHas(
            'scheduled_notifications',
            [
                'user_id' => $this->manager->id,
                'recipient_name' => $this->manager->fullName,
                'recipient_email' => $this->manager->email,
                'notification_type' => 'notice_date',
                'scheduled_at' => $contract->notice_date->subDays($preference->notification_delay_days)->toDateString(),
                'notifiable_type' => get_class($contract),
                'notifiable_id' => $contract->id,
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

it('deletes the notice_date notification for maintenance manager when he is removed from an asset', function ($duration, $period) {
    $endDate = ContractDurationEnum::from($duration)->addTo(Carbon::now());
    $noticeDate = NoticePeriodEnum::from($period)->subFrom($endDate)->toDateString();

    $this->asset->maintainable()->update(['maintenance_manager_id' => $this->manager->id]);

    $formData = [
        ...$this->basicContractData,
        'contract_duration' => $duration,
        'notice_period' => $period,
        'contractables' => [
            ['locationType' => 'asset', 'locationCode' => $this->asset->code, 'locationId' => $this->asset->id],
        ]
    ];

    $this->postToTenant('api.contracts.store', $formData);

    if ($noticeDate <= Carbon::now()->toDateString()) {
        assertDatabaseCount('scheduled_notifications', 0);
    } else {

        $preference = $this->manager->notification_preferences()->where('notification_type', 'notice_date')->first();

        $contract = Contract::find(1);

        assertDatabaseHas(
            'scheduled_notifications',
            [
                'user_id' => $this->manager->id,
                'recipient_name' => $this->manager->fullName,
                'recipient_email' => $this->manager->email,
                'notification_type' => 'notice_date',
                'scheduled_at' => $contract->notice_date->subDays($preference->notification_delay_days)->toDateString(),
                'notifiable_type' => get_class($contract),
                'notifiable_id' => $contract->id,
            ]
        );

        $this->asset->refresh();

        $formData = [
            'name' => $this->asset->name,
            'description' => $this->asset->description,
            'locationId' => $this->room->id,
            'locationType' => 'room',
            'locationReference' => $this->room->reference_code,
            'categoryId' => CategoryType::where('category', 'asset')->first()->id,
            'maintainable_manager_id' => null
        ];

        $response = $this->patchToTenant('api.assets.update', $formData, $this->asset->reference_code);
        $response->assertSessionHasNoErrors();

        assertDatabaseMissing(
            'scheduled_notifications',
            [
                'user_id' => $this->manager->id,
                'recipient_name' => $this->manager->fullName,
                'recipient_email' => $this->manager->email,
                'notification_type' => 'notice_date',
                'scheduled_at' => $contract->notice_date->subDays($preference->notification_delay_days)->toDateString(),
                'notifiable_type' => get_class($contract),
                'notifiable_id' => $contract->id,
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

it('does not delete the notice_date notification for maintenance manager when he is managing an asset and a location linked to a contract but only removed from the asset', function ($duration, $period) {
    $endDate = ContractDurationEnum::from($duration)->addTo(Carbon::now());
    $noticeDate = NoticePeriodEnum::from($period)->subFrom($endDate)->toDateString();
    $this->site->maintainable()->update(['maintenance_manager_id' => $this->manager->id]);
    $this->asset->maintainable()->update(['maintenance_manager_id' => $this->manager->id]);

    $formData = [
        ...$this->basicContractData,
        'contract_duration' => $duration,
        'notice_period' => $period
    ];

    $this->postToTenant('api.contracts.store', $formData);

    if ($noticeDate <= Carbon::now()->toDateString()) {
        assertDatabaseCount('scheduled_notifications', 0);
    } else {

        $preference = $this->manager->notification_preferences()->where('notification_type', 'notice_date')->first();

        $contract = Contract::find(1);

        assertDatabaseHas(
            'scheduled_notifications',
            [
                'user_id' => $this->manager->id,
                'recipient_name' => $this->manager->fullName,
                'recipient_email' => $this->manager->email,
                'notification_type' => 'notice_date',
                'scheduled_at' => $contract->notice_date->subDays($preference->notification_delay_days)->toDateString(),
                'notifiable_type' => get_class($contract),
                'notifiable_id' => $contract->id,
            ]
        );

        $this->asset->refresh();

        $formData = [
            'name' => $this->asset->name,
            'description' => $this->asset->description,
            'locationId' => $this->room->id,
            'locationType' => 'room',
            'locationReference' => $this->room->reference_code,
            'categoryId' => CategoryType::where('category', 'asset')->first()->id,
            'maintainable_manager_id' => null
        ];

        $this->patchToTenant('api.assets.update', $formData, $this->asset->reference_code);

        assertDatabaseHas(
            'scheduled_notifications',
            [
                'user_id' => $this->manager->id,
                'recipient_name' => $this->manager->fullName,
                'recipient_email' => $this->manager->email,
                'notification_type' => 'notice_date',
                'scheduled_at' => $contract->notice_date->subDays($preference->notification_delay_days)->toDateString(),
                'notifiable_type' => get_class($contract),
                'notifiable_id' => $contract->id,
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

it('creates notification for maintenance manager if status changes from `expired/cancelled` to `active`', function ($status) {

    $formData = [
        ...$this->basicContractData,
        'status' => $status
    ];

    $this->postToTenant('api.contracts.store', $formData);

    assertDatabaseCount('scheduled_notifications', 0);

    $this->asset->maintainable()->update(['maintenance_manager_id' => $this->manager->id]);

    $contract = Contract::find(1);

    $formData = [
        ...$this->basicContractData,
        'status' => 'active'
    ];

    $this->patchToTenant('api.contracts.update', $formData, $contract->id);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'notice_date',
            'notifiable_type' => get_class($contract),
            'notifiable_id' => $contract->id,
        ]
    );
})->with(['expired', 'cancelled']);




it('creates notice_date notifications when the role of a maintenance manager changes to admin', function ($duration, $period) {

    $endDate = ContractDurationEnum::from($duration)->addTo(Carbon::now());
    $noticeDate = NoticePeriodEnum::from($period)->subFrom($endDate)->toDateString();

    $contractOne = Contract::factory()->forLocation($this->asset)->create([
        'contract_duration' => $duration,
        'start_date' => Carbon::now(),
        'end_date' => $endDate,
        'notice_period' => $period,
        'notice_date' =>  $noticeDate
    ]);

    $formData = [
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'janedoe@facilitywebxp.be',
        'can_login' => true,
        'role' => 'Maintenance Manager',
        'job_position' => 'Manager',
    ];

    $this->postToTenant('api.users.store', $formData);

    $createdUser = User::where('email', 'janedoe@facilitywebxp.be')->first();
    $preference = $createdUser->notification_preferences()->where('notification_type', 'notice_date')->first();

    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'recipient_name' => $createdUser->fullName,
            'recipient_email' => $createdUser->email,
            'notification_type' => 'notice_date',
            'scheduled_at' => $contractOne->notice_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Contract',
            'notifiable_id' => $contractOne->id,
        ]
    );

    $formData = [
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'janedoe@facilitywebxp.be',
        'can_login' => true,
        'role' => 'Admin',
        'job_position' => 'Manager',
    ];

    $this->patchToTenant('api.users.update', $formData, $createdUser->id);

    if ($noticeDate  > Carbon::now()->toDateString()) {
        assertDatabaseHas(
            'scheduled_notifications',
            [
                'user_id' => $createdUser->id,
                'recipient_name' => $createdUser->fullName,
                'recipient_email' => $createdUser->email,
                'notification_type' => 'notice_date',
                'scheduled_at' => $contractOne->notice_date->subDays($preference->notification_delay_days)->toDateString(),
                'notifiable_type' => 'App\Models\Tenants\Contract',
                'notifiable_id' => $contractOne->id,
            ]
        );
    } else {
        assertDatabaseMissing(
            'scheduled_notifications',
            [
                'user_id' => $createdUser->id,
                'recipient_name' => $createdUser->fullName,
                'recipient_email' => $createdUser->email,
                'notification_type' => 'notice_date',
                'scheduled_at' => $contractOne->notice_date->subDays($preference->notification_delay_days)->toDateString(),
                'notifiable_type' => 'App\Models\Tenants\Contract',
                'notifiable_id' => $contractOne->id,
            ]
        );
    }
})->with(
    function () {
        $notices = array_column(NoticePeriodEnum::cases(), 'value');
        $durations = array_column(ContractDurationEnum::cases(), 'value');

        $combinations = [];
        foreach ($durations as $d) {
            foreach ($notices as $n) {
                $combinations[] = [$d, $n];
            }
        }
        return $combinations;
    }
);

it('deletes notice_date notifications when the role of an admin changes to maintenance manager for assets only where he is not maintenance manager', function ($duration, $period) {
    $endDate = ContractDurationEnum::from($duration)->addTo(Carbon::now());
    $noticeDate = NoticePeriodEnum::from($period)->subFrom($endDate)->toDateString();

    $contractOne = Contract::factory()->forLocation($this->asset)->create([
        'contract_duration' => $duration,
        'start_date' => Carbon::now(),
        'end_date' => $endDate,
        'notice_period' => $period,
        'notice_date' =>  $noticeDate
    ]);

    $contractTwo = Contract::factory()->forLocation($this->room)->create([
        'contract_duration' => $duration,
        'start_date' => Carbon::now(),
        'end_date' => $endDate,
        'notice_period' => $period,
        'notice_date' =>  $noticeDate
    ]);

    $formData = [
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'janedoe@facilitywebxp.be',
        'can_login' => true,
        'role' => 'Admin',
        'job_position' => 'Manager',
    ];

    $this->postToTenant('api.users.store', $formData);

    $createdUser = User::where('email', 'janedoe@facilitywebxp.be')->first();

    $this->room->refresh();
    $this->room->maintainable()->update(['maintenance_manager_id' => $createdUser->id]);

    $preference = $createdUser->notification_preferences()->where('notification_type', 'end_date')->first();

    if ($noticeDate  > Carbon::now()->toDateString()) {
        assertDatabaseHas(
            'scheduled_notifications',
            [
                'recipient_name' => $createdUser->fullName,
                'recipient_email' => $createdUser->email,
                'notification_type' => 'notice_date',
                'scheduled_at' => $contractOne->notice_date->subDays($preference->notification_delay_days)->toDateString(),
                'notifiable_type' => 'App\Models\Tenants\Contract',
                'notifiable_id' => $contractOne->id,
            ]
        );
        assertDatabaseHas(
            'scheduled_notifications',
            [
                'recipient_name' => $createdUser->fullName,
                'recipient_email' => $createdUser->email,
                'notification_type' => 'notice_date',
                'scheduled_at' => $contractTwo->notice_date->subDays($preference->notification_delay_days)->toDateString(),
                'notifiable_type' => 'App\Models\Tenants\Contract',
                'notifiable_id' => $contractTwo->id,
            ]
        );
    } else {
        assertDatabaseMissing(
            'scheduled_notifications',
            [
                'recipient_name' => $createdUser->fullName,
                'recipient_email' => $createdUser->email,
                'notification_type' => 'notice_date',
            ]
        );
    }

    $formData = [
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'janedoe@facilitywebxp.be',
        'can_login' => true,
        'role' => 'Maintenance Manager',
        'job_position' => 'Manager',
    ];

    $this->patchToTenant('api.users.update', $formData, $createdUser->id);

    if ($noticeDate  > Carbon::now()->toDateString()) {

        assertDatabaseMissing(
            'scheduled_notifications',
            [
                'recipient_name' => $createdUser->fullName,
                'recipient_email' => $createdUser->email,
                'notification_type' => 'notice_date',
                'scheduled_at' => $contractOne->notice_date->subDays($preference->notification_delay_days)->toDateString(),
                'notifiable_type' => 'App\Models\Tenants\Contract',
                'notifiable_id' => $contractOne->id,
            ]
        );

        assertDatabaseHas(
            'scheduled_notifications',
            [
                'recipient_name' => $createdUser->fullName,
                'recipient_email' => $createdUser->email,
                'notification_type' => 'notice_date',
                'scheduled_at' => $contractTwo->notice_date->subDays($preference->notification_delay_days)->toDateString(),
                'notifiable_type' => 'App\Models\Tenants\Contract',
                'notifiable_id' => $contractTwo->id,
            ]
        );
    } else {
        assertDatabaseMissing(
            'scheduled_notifications',
            [
                'recipient_name' => $createdUser->fullName,
                'recipient_email' => $createdUser->email,
                'notification_type' => 'notice_date',
            ]
        );
    }
})->with(
    function () {
        $notices = array_column(NoticePeriodEnum::cases(), 'value');
        $durations = array_column(ContractDurationEnum::cases(), 'value');

        $combinations = [];
        foreach ($durations as $d) {
            foreach ($notices as $n) {
                $combinations[] = [$d, $n];
            }
        }
        return $combinations;
    }
);
