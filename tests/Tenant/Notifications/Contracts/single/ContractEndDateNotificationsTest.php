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
        'renewal_type' => ContractRenewalTypesEnum::AUTOMATIC->value,
        'status' => ContractStatusEnum::ACTIVE->value,
    ];
});

it('creates the end_date notification for the admin for a new created contract for an asset only for contract where end_date > now for duration', function ($duration) {

    $preference = $this->admin->notification_preferences()->where('notification_type', 'end_date')->first();

    $contractOne = Contract::factory()->create([
        'contract_duration' => $duration,
        'start_date' => Carbon::now(),
        'end_date' => ContractDurationEnum::from($duration)->addTo(Carbon::now())
    ]);

    $contractTwo = Contract::factory()->create([
        'contract_duration' => $duration,
        'start_date' => Carbon::now()->subYears(2),
        'end_date' => ContractDurationEnum::from($duration)->addTo(Carbon::now()->subYears(2))
    ]);

    assertDatabaseCount('scheduled_notifications', 1);

    $contract = Contract::first();
    assertDatabaseCount('contractables', 0);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->admin->id,
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'end_date',
            'scheduled_at' => $contract->end_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($contractOne),
            'notifiable_id' => $contractOne->id,
        ]
    );

    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'user_id' => $this->admin->id,
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'end_date',
            'scheduled_at' => $contract->end_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($contractTwo),
            'notifiable_id' => $contractTwo->id,
        ]
    );
})->with(array_column(ContractDurationEnum::cases(), 'value'));

it('does not create end_date notifications for contracts which are expired/cancelled', function ($status, $duration) {

    $preference = $this->admin->notification_preferences()->where('notification_type', 'end_date')->first();

    $contractOne = Contract::factory()->create([
        'status' => $status,
        'contract_duration' => $duration,
        'start_date' => Carbon::now(),
        'end_date' => ContractDurationEnum::from($duration)->addTo(Carbon::now())
    ]);

    $contractTwo = Contract::factory()->create([
        'contract_duration' => $duration,
        'start_date' => Carbon::now(),
        'end_date' => ContractDurationEnum::from($duration)->addTo(Carbon::now())
    ]);

    assertDatabaseCount('scheduled_notifications', 1);

    $contract = Contract::first();

    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'user_id' => $this->admin->id,
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'end_date',
            'scheduled_at' => $contract->end_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($contractOne),
            'notifiable_id' => $contractOne->id,
        ]
    );

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->admin->id,
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'end_date',
            'scheduled_at' => $contract->end_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($contractTwo),
            'notifiable_id' => $contractTwo->id,
        ]
    );
})->with(function () {
    $values = array_column(ContractDurationEnum::cases(), 'value');

    $combinations = [];
    foreach ($values as $f) {

        $combinations[] = [ContractStatusEnum::EXPIRED->value, $f];
        $combinations[] = [ContractStatusEnum::CANCELLED->value, $f];
    }
    return $combinations;
});

it('updates end_date notification for admin when end_date changes for a contract based on start date', function ($duration) {

    $preference = $this->admin->notification_preferences()->where('notification_type', 'end_date')->first();

    $contractOne = Contract::factory()->create([
        'contract_duration' => $duration,
        'start_date' => Carbon::now()->toDateString(),
        'end_date' => ContractDurationEnum::from($duration)->addTo(Carbon::now())
    ]);

    assertDatabaseCount('contractables', 0);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->admin->id,
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'end_date',
            'scheduled_at' => $contractOne->end_date->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Contract',
            'notifiable_id' => 1,
        ]
    );

    $updatedContract = [
        ...$this->basicContractData,
        'start_date' => Carbon::now()->addMonths(2)->toDateString(),
        'contract_duration' => $duration,
    ];

    $this->patchToTenant('api.contracts.update', $updatedContract, $contractOne->id);

    $contractOne->refresh();
    assertDatabaseCount('contractables', 0);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->admin->id,
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'end_date',
            'scheduled_at' => ContractDurationEnum::from($duration)->addTo(Carbon::now()->addMonths(2))->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($contractOne),
            'notifiable_id' => $contractOne->id,
        ]
    );
})->with(array_column(ContractDurationEnum::cases(), 'value'));

it('updates end_date notificactions for admin when contract duration changes', function ($firstDuration, $otherDuration) {
    $contractOne = Contract::factory()->create([
        'contract_duration' => $firstDuration,
        'start_date' => Carbon::now(),
        'end_date' => ContractDurationEnum::from($firstDuration)->addTo(Carbon::now())
    ]);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->admin->id,
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'end_date',
            'scheduled_at' => $contractOne->end_date->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Contract',
            'notifiable_id' => 1,
        ]
    );

    $updatedContract = [
        ...$this->basicContractData,
        'start_date' => Carbon::now()->toDateString(),
        'contract_duration' => $otherDuration,
    ];

    $this->patchToTenant('api.contracts.update', $updatedContract, $contractOne->id);

    assertDatabaseCount('contractables', 0);
    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->admin->id,
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'end_date',
            'scheduled_at' => ContractDurationEnum::from($otherDuration)->addTo($contractOne->start_date)->subDays(7)->toDateString(),
            'notifiable_type' => get_class($contractOne),
            'notifiable_id' => $contractOne->id,
        ]
    );
})->with(function () {
    $values = array_column(ContractDurationEnum::cases(), 'value');

    $combinations = [];
    foreach ($values as $f) {
        foreach ($values as $o) {
            $combinations[] = [$f, $o];
        }
    }
    return $combinations;
});

it('deletes end_date notifications for admin when the contract status changes to `expired/cancelled`', function ($status) {

    $preference = $this->admin->notification_preferences()->where('notification_type', 'end_date')->first();

    $contract = Contract::factory()->create([...$this->basicContractData, 'end_date' => ContractDurationEnum::from($this->basicContractData['contract_duration'])->addTo(Carbon::now())]);

    assertDatabaseCount('scheduled_notifications', 1);

    $contract = Contract::first();

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->admin->id,
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'end_date',
            'scheduled_at' => $contract->end_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($contract),
            'notifiable_id' => $contract->id,
        ]
    );

    $formData = [
        ...$this->basicContractData,
        'status' => $status,
    ];

    $this->patchToTenant('api.contracts.update', $formData, $contract->id);

    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'user_id' => $this->admin->id,
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'end_date',
            'scheduled_at' => $contract->end_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($contract),
            'notifiable_id' => $contract->id,
        ]
    );
})->with(['expired', 'cancelled']);

it('creates end_date notification for admin when the contract status changes from `expired/cancelled` to `active`', function ($status) {

    $formData = [
        ...$this->basicContractData,
        'status' => $status
    ];

    $this->postToTenant('api.contracts.store', $formData);

    assertDatabaseEmpty('scheduled_notifications');

    $contract = Contract::find(1);

    $formData = [
        ...$this->basicContractData,
        'end_date' => Carbon::now()->addYears(3),
        'status' => 'active'
    ];

    $this->patchToTenant('api.contracts.update', $formData, $contract->id);

    $preference = $this->admin->notification_preferences()->where('notification_type', 'end_date')->first();

    assertDatabaseCount('scheduled_notifications', 1);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->admin->id,
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'end_date',
            'scheduled_at' => $contract->end_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($contract),
            'notifiable_id' => $contract->id,
        ]
    );
})->with(['expired', 'cancelled']);

it('updates end_date notifications of admin when notification_delay_days preference for end_date of user changes for contracts where end_date > now and notification pending', function ($duration) {

    $contractOne = Contract::factory()->create([
        'contract_duration' => $duration,
        'start_date' => Carbon::now(),
        'end_date' => ContractDurationEnum::from($duration)->addTo(Carbon::now())
    ]);

    $contractTwo = Contract::factory()->create([
        'contract_duration' => $duration,
        'start_date' => Carbon::now(),
        'end_date' => ContractDurationEnum::from($duration)->addTo(Carbon::now())
    ]);

    $contractThree = Contract::factory()->create([
        'contract_duration' => $duration,
        'start_date' => Carbon::now(),
        'end_date' => ContractDurationEnum::from($duration)->addTo(Carbon::now())
    ]);

    assertDatabaseCount('scheduled_notifications', 3);

    $preference = $this->admin->notification_preferences()->where('notification_type', 'end_date')->first();
    $oldPreferenceDays = $preference->notification_delay_days;

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->admin->id,
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'end_date',
            'status' => 'pending',
            'scheduled_at' => $contractOne->end_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Contract',
            'notifiable_id' => $contractOne->id,
        ]
    );

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->admin->id,
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'end_date',
            'status' => 'pending',
            'scheduled_at' => $contractTwo->end_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Contract',
            'notifiable_id' => $contractTwo->id,
        ]
    );

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->admin->id,
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'end_date',
            'status' => 'pending',
            'scheduled_at' => $contractThree->end_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Contract',
            'notifiable_id' => $contractThree->id,
        ]
    );

    $contractThree->update(['end_date' => Carbon::now()]);

    $notification = $contractOne->notifications()->first();
    $notification->update(['status' => 'sent']);

    $formData = [
        'asset_type' => 'contract',
        'notification_type' => 'end_date',
        'notification_delay_days' => 1,
        'enabled' => true,
    ];

    $this->patchToTenant('api.notifications.update', $formData, $preference->id);

    $preference->refresh();

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->admin->id,
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'end_date',
            'scheduled_at' => $contractOne->end_date->subDays($oldPreferenceDays)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Contract',
            'notifiable_id' => $contractOne->id,
        ]
    );
    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->admin->id,
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'end_date',
            'status' => 'pending',
            'scheduled_at' => $contractTwo->end_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Contract',
            'notifiable_id' => $contractTwo->id,
        ]
    );

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->admin->id,
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'end_date',
            'status' => 'pending',
            'scheduled_at' =>  ContractDurationEnum::from($duration)->addTo(Carbon::now())->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Contract',
            'notifiable_id' => $contractThree->id,
        ]
    );
})->with(array_column(ContractDurationEnum::cases(), 'value'));

it('deletes end_date notifications for admin when notification preference end_date of user changes to disabled and status `pending`', function ($duration) {

    $contractOne = Contract::factory()->create([
        'contract_duration' => $duration,
        'start_date' => Carbon::now(),
        'end_date' => ContractDurationEnum::from($duration)->addTo(Carbon::now())
    ]);

    $contractTwo = Contract::factory()->create([
        'contract_duration' => $duration,
        'start_date' => Carbon::now(),
        'end_date' => ContractDurationEnum::from($duration)->addTo(Carbon::now())
    ]);

    $preference = $this->admin->notification_preferences()->where('notification_type', 'end_date')->first();

    assertDatabaseCount('scheduled_notifications', 2);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->admin->id,
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'end_date',
            'status' => 'pending',
            'scheduled_at' =>  ContractDurationEnum::from($duration)->addTo(Carbon::now())->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($contractOne),
            'notifiable_id' => $contractOne->id,
        ]
    );
    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->admin->id,
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'end_date',
            'status' => 'pending',
            'scheduled_at' =>  ContractDurationEnum::from($duration)->addTo(Carbon::now())->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($contractTwo),
            'notifiable_id' => $contractTwo->id,
        ]
    );

    $contractTwoNotification = $contractTwo->notifications()->first();
    $contractTwoNotification->update(['status' => 'sent']);

    $formData = [
        'asset_type' => 'contract',
        'notification_type' => 'end_date',
        'notification_delay_days' => $preference->notification_delay_days,
        'enabled' => false,
    ];

    $this->patchToTenant('api.notifications.update', $formData, $preference->id);
    assertDatabaseCount('scheduled_notifications', 1);

    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'user_id' => $this->admin->id,
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'end_date',
            'scheduled_at' =>  ContractDurationEnum::from($duration)->addTo(Carbon::now())->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($contractOne),
            'notifiable_id' => $contractOne->id,
        ]
    );

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->admin->id,
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'end_date',
            'status' => 'sent',
            'scheduled_at' =>  ContractDurationEnum::from($duration)->addTo(Carbon::now())->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($contractTwo),
            'notifiable_id' => $contractTwo->id,
        ]
    );
})->with(array_column(ContractDurationEnum::cases(), 'value'));

it('creates end_date notifications for admin when notification preference end_date of user changes to enabled and contract end_date > now', function ($duration) {

    $preference = $this->admin->notification_preferences()->where('notification_type', 'end_date')->first();
    $preference->update(['enabled' => false]);

    $contractOne = Contract::factory()->create([
        'contract_duration' => $duration,
        'start_date' => Carbon::now(),
        'end_date' => ContractDurationEnum::from($duration)->addTo(Carbon::now())
    ]);

    $contractTwo = Contract::factory()->create([
        'contract_duration' => $duration,
        'start_date' => ContractDurationEnum::from($duration)->subFrom(Carbon::now()),
        'end_date' => Carbon::now()
    ]);

    assertDatabaseCount('scheduled_notifications', 0);

    $preference->update(['enabled' => true]);

    assertDatabaseCount('scheduled_notifications', 1);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->admin->id,
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'end_date',
            'status' => 'pending',
            'scheduled_at' => $contractOne->end_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Contract',
            'notifiable_id' => $contractOne->id,
        ]
    );

    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'user_id' => $this->admin->id,
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'end_date',
            'notifiable_type' => 'App\Models\Tenants\Contract',
            'notifiable_id' => $contractTwo->id,
        ]
    );
})->with(array_column(ContractDurationEnum::cases(), 'value'));

it('creates end_date notifications for all existing contracts when a new user is created with admin role', function ($duration) {

    $contractOne = Contract::factory()->create([
        'contract_duration' => $duration,
        'start_date' => Carbon::now(),
        'end_date' => ContractDurationEnum::from($duration)->addTo(Carbon::now())
    ]);

    $contractTwo = Contract::factory()->create([
        'contract_duration' => $duration,
        'start_date' => ContractDurationEnum::from($duration)->subFrom(Carbon::now()),
        'end_date' => Carbon::now(),
    ]);


    $user = User::factory()->withRole('Admin')->create();

    $preference = $user->notification_preferences()->where('notification_type', 'end_date')->first();

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $user->fullName,
            'recipient_email' => $user->email,
            'notification_type' => 'end_date',
            'scheduled_at' => $contractOne->end_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Contract',
            'notifiable_id' => $contractOne->id,
        ]
    );

    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'recipient_name' => $user->fullName,
            'recipient_email' => $user->email,
            'notification_type' => 'end_date',
            'scheduled_at' => $contractTwo->end_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Contract',
            'notifiable_id' => $contractTwo->id,
        ]
    );
})->with(array_column(ContractDurationEnum::cases(), 'value'));

it('deletes end_date notifications when the role of an admin changes to maintenance manager', function ($duration) {
    $contractOne = Contract::factory()->create([
        'contract_duration' => $duration,
        'start_date' => Carbon::now(),
        'end_date' => ContractDurationEnum::from($duration)->addTo(Carbon::now())
    ]);

    $user = User::factory()->withRole('Admin')->create();

    $preference = $user->notification_preferences()->where('notification_type', 'end_date')->first();

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $user->fullName,
            'recipient_email' => $user->email,
            'notification_type' => 'end_date',
            'scheduled_at' => $contractOne->end_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Contract',
            'notifiable_id' => $contractOne->id,
        ]
    );

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
            'notification_type' => 'end_date',
            'scheduled_at' => $contractOne->end_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($contractOne),
            'notifiable_id' => $contractOne->id,
        ]
    );
})->with(array_column(ContractDurationEnum::cases(), 'value'));

it('deletes end_date notifications of the admin when contract is deleted', function ($duration) {
    $contractOne = Contract::factory()->create([
        'contract_duration' => $duration,
        'start_date' => Carbon::now(),
        'end_date' => ContractDurationEnum::from($duration)->addTo(Carbon::now())
    ]);

    $user = User::factory()->withRole('Admin')->create();

    $preference = $user->notification_preferences()->where('notification_type', 'end_date')->first();

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $user->fullName,
            'recipient_email' => $user->email,
            'notification_type' => 'end_date',
            'scheduled_at' => $contractOne->end_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Contract',
            'notifiable_id' => $contractOne->id,
        ]
    );

    $contractOne->delete();

    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'recipient_name' => $user->fullName,
            'recipient_email' => $user->email,
            'notification_type' => 'end_date',
            'scheduled_at' => $contractOne->end_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($contractOne),
            'notifiable_id' => $contractOne->id,
        ]
    );
})->with(array_column(ContractDurationEnum::cases(), 'value'));
