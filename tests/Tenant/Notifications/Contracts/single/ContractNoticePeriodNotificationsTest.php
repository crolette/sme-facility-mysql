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

    $this->provider = Provider::factory()->create();

    $this->basicContractData = [
        'provider_id' => $this->provider->id,
        'name' => 'Contrat de bail',
        'type' => ContractTypesEnum::ALLIN->value,
        'notes' => 'Nouveau contrat de bail 2025',
        'internal_reference' => 'Bail Site 2025',
        'provider_reference' => 'Provider reference 2025',
        'start_date' => Carbon::now()->toDateString(),
        'contract_duration' => ContractDurationEnum::ONE_MONTH->value,
        'notice_period' => NoticePeriodEnum::FOURTEEN_DAYS->value,
        'notice_date' => NoticePeriodEnum::from(NoticePeriodEnum::FOURTEEN_DAYS->value)->subFrom(Carbon::now()->addMonth())->toDateString(),
        'renewal_type' => ContractRenewalTypesEnum::AUTOMATIC->value,
        'status' => ContractStatusEnum::ACTIVE->value,
    ];
});

it('creates the notice_date notification for an admin when a new contract is created', function ($duration, $period) {

    $endDate = ContractDurationEnum::from($duration)->addTo(Carbon::now());
    $noticeDate = NoticePeriodEnum::from($period)->subFrom($endDate)->toDateString();

    $formData = [
        ...$this->basicContractData,
        'contract_duration' => $duration,
        'notice_period' => $period,
        'notice_date' => $noticeDate
    ];


    if ($noticeDate <= Carbon::now()->toDateString()) {
        assertDatabaseCount('scheduled_notifications', 0);
    } else {

        $response = $this->postToTenant('api.contracts.store', $formData);
        $response->assertSessionHasNoErrors();

        $preference = $this->admin->notification_preferences()->where('notification_type', 'notice_date')->first();

        $contract = Contract::find(1);

        assertDatabaseHas(
            'scheduled_notifications',
            [
                'user_id' => $this->admin->id,
                'recipient_name' => $this->admin->fullName,
                'recipient_email' => $this->admin->email,
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

it('creates notice_date notification only if contract has `active` status', function ($duration, $period, $status) {

    $endDate = ContractDurationEnum::from($duration)->addTo(Carbon::now());
    $noticeDate = NoticePeriodEnum::from($period)->subFrom($endDate)->toDateString();

    Contract::factory()->create(
        [
            ...$this->basicContractData,
            'status' => $status,
            'contract_duration' => $duration,
            'notice_period' => $period,
            'notice_date' => $noticeDate
        ]
    );

    if ($noticeDate <= Carbon::now()->toDateString() || $status !== 'active') {
        assertDatabaseEmpty('scheduled_notifications');
    } else {
        $contract = Contract::find(1);
        $preference = $this->admin->notification_preferences()->where('notification_type', 'notice_date')->first();

        assertDatabaseHas(
            'scheduled_notifications',
            [
                'user_id' => $this->admin->id,
                'recipient_name' => $this->admin->fullName,
                'recipient_email' => $this->admin->email,
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
    $statuses = array_column(ContractStatusEnum::cases(), 'value');

    $combinations = [];
    foreach ($durations as $d) {
        foreach ($notices as $n) {
            foreach ($statuses as $status)
                $combinations[] = [$d, $n, $status];
        }
    }
    return $combinations;
});

it('does not create a notice_date notification if no notice_period is given', function ($duration) {
    $formData = [
        ...$this->basicContractData,
        'contract_duration' => $duration,
        'notice_period' => null,
        // 'notice_date' => null
    ];

    $this->postToTenant('api.contracts.store', $formData);

    $contract = Contract::first();

    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'notification_type' => 'notice_date',
            'scheduled_at' => null,
            'notifiable_type' => Contract::class,
            'notifiable_id' => $contract->id

        ]
    );
})->with(array_column(ContractDurationEnum::cases(), 'value'));

it('deletes notice_date notifications for admin when the contract status changes to expired/cancelled', function ($status) {

    $preference = $this->admin->notification_preferences()->where('notification_type', 'notice_date')->first();

    $contract = Contract::factory()->create($this->basicContractData);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->admin->id,
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'notice_date',
            'scheduled_at' => $contract->notice_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($contract),
            'notifiable_id' => $contract->id,
        ]
    );

    $contract->update([
        ...$this->basicContractData,
        'status' => $status,
    ]);


    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'user_id' => $this->admin->id,
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'notice_date',
            'scheduled_at' => $contract->notice_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($contract),
            'notifiable_id' => $contract->id,
        ]
    );
})->with(['expired', 'cancelled']);

it('creates notification for admin if status changes from `expired/cancelled` to `active`', function ($status, $duration, $period) {

    $contract = Contract::factory()->create([...$this->basicContractData, 'status' => $status]);

    assertDatabaseCount('scheduled_notifications', 0);

    $endDate = ContractDurationEnum::from($duration)->addTo(Carbon::now());
    $noticeDate = NoticePeriodEnum::from($period)->subFrom($endDate)->toDateString();

    $contract->update([
        ...$this->basicContractData,
        'contract_duration' => $duration,
        'notice_period' => $period,
        'notice_date' => $noticeDate,
        'status' => 'active'
    ]);

    $preference = $this->admin->notification_preferences()->where('notification_type', 'notice_date')->first();

    if ($noticeDate <= Carbon::now()->toDateString()) {
        assertDatabaseEmpty('scheduled_notifications');
    } else {

        assertDatabaseHas(
            'scheduled_notifications',
            [
                'user_id' => $this->admin->id,
                'recipient_name' => $this->admin->fullName,
                'recipient_email' => $this->admin->email,
                'scheduled_at' => $contract->notice_date->subDays($preference->notification_delay_days)->toDateString(),
                'notification_type' => 'notice_date',
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
            if ($n !== $d) {
                $combinations[] = ['expired', $d, $n];
                $combinations[] = ['cancelled', $d, $n,];
            }
        }
    }
    return $combinations;
});

it('updates notifications when notification_delay_days preference for notice_date of user changes', function ($duration, $period) {

    $preference = $this->admin->notification_preferences()->where('notification_type', 'notice_date')->first();
    $endDate = ContractDurationEnum::from($duration)->addTo(Carbon::now());
    $noticeDate = NoticePeriodEnum::from($period)->subFrom($endDate)->toDateString();

    $contract = Contract::factory()->create([
        ...$this->basicContractData,
        'contract_duration' => $duration,
        'notice_period' => $period,
        'notice_date' => $noticeDate
    ]);


    if ($noticeDate <= Carbon::now()->toDateString()) {
        assertDatabaseEmpty('scheduled_notifications');
    } else {

        assertDatabaseHas(
            'scheduled_notifications',
            [
                'user_id' => $this->admin->id,
                'recipient_name' => $this->admin->fullName,
                'recipient_email' => $this->admin->email,
                'notification_type' => 'notice_date',
                'scheduled_at' => $contract->notice_date->subDays($preference->notification_delay_days)->toDateString(),
                'notifiable_type' => 'App\Models\Tenants\Contract',
                'notifiable_id' => $contract->id,
            ]
        );

        $preference->update(['notification_delay_days' => 1,]);

        $preference->refresh();

        assertDatabaseHas(
            'scheduled_notifications',
            [
                'user_id' => $this->admin->id,
                'recipient_name' => $this->admin->fullName,
                'recipient_email' => $this->admin->email,
                'notification_type' => 'notice_date',
                'scheduled_at' => $contract->notice_date->subDays($preference->notification_delay_days)->toDateString(),
                'notifiable_type' => 'App\Models\Tenants\Contract',
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
            if ($d !== $n)
                $combinations[] = [$d, $n];
        }
    }
    return $combinations;
});

it('deletes notifications when notification preference notice_date of user is disabled', function ($duration, $period) {

    $preference = $this->admin->notification_preferences()->where('notification_type', 'notice_date')->first();
    $endDate = ContractDurationEnum::from($duration)->addTo(Carbon::now());
    $noticeDate = NoticePeriodEnum::from($period)->subFrom($endDate)->toDateString();

    $contract = Contract::factory()->create([
        ...$this->basicContractData,
        'contract_duration' => $duration,
        'notice_period' => $period,
        'notice_date' => $noticeDate
    ]);

    if ($noticeDate <= Carbon::now()->toDateString()) {
        assertDatabaseEmpty('scheduled_notifications');
    } else {

        assertDatabaseHas(
            'scheduled_notifications',
            [
                'user_id' => $this->admin->id,
                'recipient_name' => $this->admin->fullName,
                'recipient_email' => $this->admin->email,
                'notification_type' => 'notice_date',
                'scheduled_at' => $contract->notice_date->subDays($preference->notification_delay_days)->toDateString(),
                'notifiable_type' => 'App\Models\Tenants\Contract',
                'notifiable_id' => 1,
            ]
        );

        $preference->update(['enabled' => false]);

        assertDatabaseMissing(
            'scheduled_notifications',
            [
                'user_id' => $this->admin->id,
                'recipient_name' => $this->admin->fullName,
                'recipient_email' => $this->admin->email,
                'notification_type' => 'notice_date',
                'scheduled_at' => $contract->notice_date->subDays($preference->notification_delay_days)->toDateString(),
                'notifiable_type' => 'App\Models\Tenants\Contract',
                'notifiable_id' => 1,
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
                if ($d !== $n)
                    $combinations[] = [$d, $n];
            }
        }
        return $combinations;
    }
);

it('creates notifications when notification preference notice_date of user changes from disabled to enabled', function ($duration, $period) {

    $preference = $this->admin->notification_preferences()->where('notification_type', 'notice_date')->first();
    $endDate = ContractDurationEnum::from($duration)->addTo(Carbon::now());
    $noticeDate = NoticePeriodEnum::from($period)->subFrom($endDate)->toDateString();

    $preference->update(['enabled' => false]);


    $contract = Contract::factory()->create([
        ...$this->basicContractData,
        'contract_duration' => $duration,
        'notice_period' => $period,
        'notice_date' => $noticeDate
    ]);

    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'user_id' => $this->admin->id,
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'notice_date',
            'scheduled_at' => $contract->notice_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Contract',
            'notifiable_id' => 1,
        ]
    );

    $preference->update(['enabled' => true]);

    if ($noticeDate <= Carbon::now()->toDateString()) {
        assertDatabaseEmpty('scheduled_notifications');
    } else {
        assertDatabaseHas(
            'scheduled_notifications',
            [
                'user_id' => $this->admin->id,
                'recipient_name' => $this->admin->fullName,
                'recipient_email' => $this->admin->email,
                'notification_type' => 'notice_date',
                'scheduled_at' => $contract->notice_date->subDays($preference->notification_delay_days)->toDateString(),
                'notifiable_type' => 'App\Models\Tenants\Contract',
                'notifiable_id' => $contract->id,
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
                if ($d !== $n)
                    $combinations[] = [$d, $n];
            }
        }
        return $combinations;
    }
);

it('creates notice_date notification for a specific contract when notice_period is added after contract creation and was null', function ($duration, $period) {

    $preference = $this->admin->notification_preferences()->where('notification_type', 'notice_date')->first();
    $endDate = ContractDurationEnum::from($duration)->addTo(Carbon::now());
    $noticeDate = NoticePeriodEnum::from($period)->subFrom($endDate)->toDateString();

    $contract = Contract::factory()->create([
        ...$this->basicContractData,
        'contract_duration' => $duration,
        'notice_period' => null,
        'notice_date' => null
    ]);

    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'user_id' => $this->admin->id,
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'notice_date',
            'notifiable_type' => get_class($contract),
            'notifiable_id' => $contract->id,
        ]
    );

    if ($noticeDate > Carbon::now()->toDateString()) {
        $contract->update([
            'contract_duration' => $duration,
            'notice_period' => $period,
            'notice_date' => $noticeDate
        ]);

        assertDatabaseHas(
            'scheduled_notifications',
            [
                'user_id' => $this->admin->id,
                'recipient_name' => $this->admin->fullName,
                'recipient_email' => $this->admin->email,
                'notification_type' => 'notice_date',
                'scheduled_at' => $contract->notice_date->subDays($preference->notification_delay_days)->toDateString(),
                'notifiable_type' => get_class($contract),
                'notifiable_id' => $contract->id,
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
                if ($d !== $n)
                    $combinations[] = [$d, $n];
            }
        }
        return $combinations;
    }
);

it('updates notice_date notification for a specific contract when notice_period changes', function ($duration, $period) {

    $preference = $this->admin->notification_preferences()->where('notification_type', 'notice_date')->first();
    $endDate = ContractDurationEnum::from($duration)->addTo(Carbon::now());
    $noticeDate = NoticePeriodEnum::from($period)->subFrom($endDate)->toDateString();

    $contract = Contract::factory()->create([
        ...$this->basicContractData,
        'contract_duration' => $duration,
        'notice_period' => NoticePeriodEnum::DEFAULT->value
    ]);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->admin->id,
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'notice_date',
            'scheduled_at' => $contract->notice_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Contract',
            'notifiable_id' => 1,
        ]
    );

    if ($noticeDate > Carbon::now()->toDateString()) {
        $contract->update([
            ...$this->basicContractData,
            'contract_duration' => $duration,
            'notice_period' => $period,
            'notice_date' => $noticeDate
        ]);

        $contract->refresh();

        assertDatabaseHas(
            'scheduled_notifications',
            [
                'user_id' => $this->admin->id,
                'recipient_name' => $this->admin->fullName,
                'recipient_email' => $this->admin->email,
                'notification_type' => 'notice_date',
                'scheduled_at' => $contract->notice_date->subDays($preference->notification_delay_days)->toDateString(),
                'notifiable_type' => 'App\Models\Tenants\Contract',
                'notifiable_id' => 1,
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
                if ($d !== $n)
                    $combinations[] = [$d, $n];
            }
        }
        return $combinations;
    }
);

it('creates notice_date notifications for a new created user with admin role', function ($duration, $period) {

    $endDate = ContractDurationEnum::from($duration)->addTo(Carbon::now());
    $noticeDate = NoticePeriodEnum::from($period)->subFrom($endDate)->toDateString();

    $contractOne = Contract::factory()->create([
        'contract_duration' => $duration,
        'start_date' => Carbon::now(),
        'end_date' => $endDate,
        'notice_period' => $period,
        'notice_date' =>  $noticeDate
    ]);

    $user = User::factory()->withRole('Admin')->create();
    $preference = $user->notification_preferences()->where('notification_type', 'notice_date')->first();

    if ($noticeDate  > Carbon::now()->toDateString()) {
        assertDatabaseHas(
            'scheduled_notifications',
            [
                'user_id' => $user->id,
                'recipient_name' => $user->fullName,
                'recipient_email' => $user->email,
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
                'user_id' => $user->id,
                'recipient_name' => $user->fullName,
                'recipient_email' => $user->email,
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
                if ($d !== $n)
                    $combinations[] = [$d, $n];
            }
        }
        return $combinations;
    }
);


it('deletes notice_date notifications when the role of an admin changes to maintenance manager', function ($duration, $period) {

    $endDate = ContractDurationEnum::from($duration)->addTo(Carbon::now());
    $noticeDate = NoticePeriodEnum::from($period)->subFrom($endDate)->toDateString();

    $contractOne = Contract::factory()->create([
        'contract_duration' => $duration,
        'start_date' => Carbon::now(),
        'end_date' => $endDate,
        'notice_period' => $period,
        'notice_date' =>  $noticeDate
    ]);

    $user = User::factory()->withRole('Admin')->create();
    $preference = $user->notification_preferences()->where('notification_type', 'end_date')->first();

    if ($noticeDate  > Carbon::now()->toDateString()) {
        assertDatabaseHas(
            'scheduled_notifications',
            [
                'recipient_name' => $user->fullName,
                'recipient_email' => $user->email,
                'notification_type' => 'notice_date',
                'scheduled_at' => $contractOne->notice_date->subDays($preference->notification_delay_days)->toDateString(),
                'notifiable_type' => 'App\Models\Tenants\Contract',
                'notifiable_id' => $contractOne->id,
            ]
        );
    }

    $formData = [
        'email' => $user->email,
        'first_name' => $user->first_name,
        'last_name' => $user->last_name,
        'can_login' => true,
        'role' => 'Maintenance Manager',
    ];

    $this->patchToTenant('api.users.update', $formData, $user->id);

    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'recipient_name' => $user->fullName,
            'recipient_email' => $user->email,
            'notification_type' => 'notice_date',
            'scheduled_at' => $contractOne->notice_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Contract',
            'notifiable_id' => $contractOne->id,
        ]
    );
})->with(
    function () {
        $notices = array_column(NoticePeriodEnum::cases(), 'value');
        $durations = array_column(ContractDurationEnum::cases(), 'value');

        $combinations = [];
        foreach ($durations as $d) {
            foreach ($notices as $n) {
                if ($d !== $n)
                    $combinations[] = [$d, $n];
            }
        }
        return $combinations;
    }
);

it('deletes notice_date notifications when the contract is deleted', function ($duration, $period) {

    $endDate = ContractDurationEnum::from($duration)->addTo(Carbon::now());
    $noticeDate = NoticePeriodEnum::from($period)->subFrom($endDate)->toDateString();

    $contractOne = Contract::factory()->create([
        'contract_duration' => $duration,
        'start_date' => Carbon::now(),
        'end_date' => $endDate,
        'notice_period' => $period,
        'notice_date' =>  $noticeDate
    ]);

    $preference = $this->admin->notification_preferences()->where('notification_type', 'end_date')->first();

    if ($noticeDate  > Carbon::now()->toDateString()) {
        assertDatabaseHas(
            'scheduled_notifications',
            [
                'recipient_name' => $this->admin->fullName,
                'recipient_email' => $this->admin->email,
                'notification_type' => 'notice_date',
                'scheduled_at' => $contractOne->notice_date->subDays($preference->notification_delay_days)->toDateString(),
                'notifiable_type' => 'App\Models\Tenants\Contract',
                'notifiable_id' => $contractOne->id,
            ]
        );

        $contractOne->delete();

        assertDatabaseMissing(
            'scheduled_notifications',
            [
                'recipient_name' => $this->admin->fullName,
                'recipient_email' => $this->admin->email,
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
                if ($d !== $n)
                    $combinations[] = [$d, $n];
            }
        }
        return $combinations;
    }
);
