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
use App\Models\Tenants\Provider;
use App\Enums\ContractStatusEnum;
use App\Enums\ContractDurationEnum;
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

    $this->site = Site::factory()->create();
    Building::factory()->create();
    Floor::factory()->create();
    $this->provider = Provider::factory()->create();

    $this->room = Room::factory()->create();

    $this->asset = Asset::factory()->forLocation($this->room)->create();
    $this->asset->refresh();

    $this->basicContractData = [
        'provider_id' => $this->provider->id,
        'name' => 'Contrat de bail',
        'type' => 'Bail',
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

it('creates the end_date notification for the admin for a new created contract for an asset only for contract where end_date > now', function ($duration) {

    $preference = $this->admin->notification_preferences()->where('notification_type', 'end_date')->first();

    $contractOne = Contract::factory()->forLocation($this->asset)->create([
        'contract_duration' => $duration,
        'start_date' => Carbon::now(),
        'end_date' => ContractDurationEnum::from($duration)->addTo(Carbon::now())
    ]);
    $contractTwo = Contract::factory()->forLocation($this->asset)->create([
        'contract_duration' => $duration,
        'start_date' => Carbon::now()->subYears(2),
        'end_date' => ContractDurationEnum::from($duration)->addTo(Carbon::now()->subYears(2))
    ]);

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

it('creates the end_date notification for the maintenance manager for a new created contract where end_date > now', function ($duration) {

    $this->asset->maintainable()->update(['maintenance_manager_id' => $this->manager->id]);

    $preference = $this->manager->notification_preferences()->where('notification_type', 'end_date')->first();

    $formData = [
        ...$this->basicContractData,
        'contract_duration' => $duration
    ];

    $this->postToTenant('api.contracts.store', $formData);

    $formData = [
        ...$this->basicContractData,
        'contract_duration' => $duration,
        'start_date' => Carbon::now()->subYears(2),
        'end_date' => ContractDurationEnum::from($duration)->addTo(Carbon::now()->subYears(2))
    ];

    $this->postToTenant('api.contracts.store', $formData);

    assertDatabaseCount('scheduled_notifications', 2);

    $contractOne = Contract::first();
    $contractTwo = Contract::find(2);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_date',
            'scheduled_at' => $contractOne->end_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($contractOne),
            'notifiable_id' => $contractOne->id,
        ]
    );

    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_date',
            'scheduled_at' => $contractTwo->end_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($contractTwo),
            'notifiable_id' => $contractTwo->id,
        ]
    );
})->with(array_column(ContractDurationEnum::cases(), 'value'));

it('updates end_date notification for admin when end_date changes for a contract', function ($duration) {

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
            'scheduled_at' => $contractOne->end_date->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Contract',
            'notifiable_id' => 1,
        ]
    );

    $updatedContract = [
        ...$this->basicContractData,
        'start_date' => Carbon::now()->addMonths(2),
        'contract_duration' => $duration,
    ];

    $this->patchToTenant('api.contracts.update', $updatedContract, $contractOne->id);

    $contractOne->refresh();

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->admin->id,
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'end_date',
            'scheduled_at' => ContractDurationEnum::from($duration)->addTo(Carbon::now()->addMonths(2))->subDays(7)->toDateString(),
            'notifiable_type' => get_class($contractOne),
            'notifiable_id' => $contractOne->id,
        ]
    );
})->with(array_column(ContractDurationEnum::cases(), 'value'));

it('updates end_date notification for maintenance_manager when end_date changes for a contract', function ($duration) {

    $this->asset->maintainable()->update(['maintenance_manager_id' => $this->manager->id]);

    $formData = [
        ...$this->basicContractData,
        'contract_duration' => $duration
    ];

    $this->postToTenant('api.contracts.store', $formData);

    $contractOne = Contract::find(1);


    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_date',
            'scheduled_at' => $contractOne->end_date->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Contract',
            'notifiable_id' => 1,
        ]
    );

    $updatedContract = [
        ...$this->basicContractData,
        'start_date' => Carbon::now()->addMonths(2),
        'contract_duration' => $duration,
    ];

    $this->patchToTenant('api.contracts.update', $updatedContract, $contractOne->id);

    $contractOne->refresh();

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_date',
            'scheduled_at' => ContractDurationEnum::from($duration)->addTo(Carbon::now()->addMonths(2))->subDays(7)->toDateString(),
            'notifiable_type' => get_class($contractOne),
            'notifiable_id' => $contractOne->id,
        ]
    );
})->with(array_column(ContractDurationEnum::cases(), 'value'));

it('creates end_date notifications when a maintenance manager is added to an existing asset with contract', function ($duration) {

    $preference = $this->manager->notification_preferences()->where('notification_type', 'end_date')->first();

    $contractOne = Contract::factory()->forLocation($this->asset)->create([
        'contract_duration' => $duration,
        'start_date' => Carbon::now(),
        'end_date' => ContractDurationEnum::from($duration)->addTo(Carbon::now())
    ]);

    $formData = [
        ...$this->basicAssetData,
        'maintenance_manager_id' => $this->manager->id,
    ];

    $this->patchToTenant('api.assets.update', $formData, $this->asset->reference_code);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_date',
            'scheduled_at' => $contractOne->end_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($contractOne),
            'notifiable_id' => $contractOne->id,
        ]
    );
})->with(array_column(ContractDurationEnum::cases(), 'value'));

it('deletes end_date notifications when a maintenance manager is removed from an existing asset with contract', function ($duration) {

    $preference = $this->manager->notification_preferences()->where('notification_type', 'end_date')->first();

    $this->asset->maintainable()->update(['maintenance_manager_id' => $this->manager->id]);

    $formData = [
        ...$this->basicContractData,
        'contract_duration' => $duration,
        'start_date' => Carbon::now(),
    ];

    $this->postToTenant('api.contracts.store', $formData);

    $contractOne = Contract::find(1);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_date',
            'scheduled_at' => $contractOne->end_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($contractOne),
            'notifiable_id' => $contractOne->id,
        ]
    );

    $formData = [
        ...$this->basicAssetData,
        'maintenance_manager_id' => null,
    ];

    $this->patchToTenant('api.assets.update', $formData, $this->asset->reference_code);

    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_date',
            'scheduled_at' => $contractOne->end_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($contractOne),
            'notifiable_id' => $contractOne->id,
        ]
    );
})->with(array_column(ContractDurationEnum::cases(), 'value'));

it('creates end_date notification when a maintenance manager is changed for an existing asset with contract', function ($duration) {

    $preference = $this->manager->notification_preferences()->where('notification_type', 'end_date')->first();

    $this->asset->maintainable()->update(['maintenance_manager_id' => $this->manager->id]);

    $formData = [
        ...$this->basicContractData,
        'contract_duration' => $duration,
        'start_date' => Carbon::now(),
    ];

    $this->postToTenant('api.contracts.store', $formData);

    $contractOne = Contract::find(1);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_date',
            'scheduled_at' => $contractOne->end_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($contractOne),
            'notifiable_id' => $contractOne->id,
        ]
    );

    $newManager = User::factory()->withRole('Maintenance Manager')->create();
    $newManagerPreference = $newManager->notification_preferences()->where('notification_type', 'end_date')->first();

    $formData = [
        ...$this->basicAssetData,
        'maintenance_manager_id' => $newManager->id,
    ];

    $this->patchToTenant('api.assets.update', $formData, $this->asset->reference_code);

    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_date',
            'notifiable_type' => get_class($contractOne),
            'notifiable_id' => $contractOne->id,
        ]
    );

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $newManager->id,
            'recipient_name' => $newManager->fullName,
            'recipient_email' => $newManager->email,
            'notification_type' => 'end_date',
            'scheduled_at' => $contractOne->end_date->subDays($newManagerPreference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($contractOne),
            'notifiable_id' => $contractOne->id,
        ]
    );
})->with(array_column(ContractDurationEnum::cases(), 'value'));

it('deletes end_date notifications when the contract status changes to expired/cancelled', function ($status) {

    $preference = $this->admin->notification_preferences()->where('notification_type', 'end_date')->first();

    $formData = [
        ...$this->basicContractData,
    ];

    $this->postToTenant('api.contracts.store', $formData);

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

it('updates end_date notifications of admin when notification_delay_days preference for end_date of user changes for contracts where end_date > now', function ($duration) {

    $contractOne = Contract::factory()->forLocation($this->asset)->create([
        'contract_duration' => $duration,
        'start_date' => Carbon::now(),
        'end_date' => ContractDurationEnum::from($duration)->addTo(Carbon::now())
    ]);

    $contractTwo = Contract::factory()->forLocation($this->asset)->create([
        'contract_duration' => $duration,
        'start_date' => Carbon::now(),
        'end_date' => ContractDurationEnum::from($duration)->addTo(Carbon::now())
    ]);

    $contractThree = Contract::factory()->forLocation($this->asset)->create([
        'contract_duration' => $duration,
        'start_date' => Carbon::now(),
        'end_date' => ContractDurationEnum::from($duration)->addTo(Carbon::now())
    ]);


    $preference = $this->admin->notification_preferences()->where('notification_type', 'end_date')->first();

    assertDatabaseCount('scheduled_notifications', 3);

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
            'scheduled_at' =>  ContractDurationEnum::from($duration)->addTo(Carbon::now())->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Contract',
            'notifiable_id' => $contractThree->id,
        ]
    );
})->with(array_column(ContractDurationEnum::cases(), 'value'));

it('updates end_date notification of maintenance manager when notification_delay_days preference for end_date of user changes for contracts where end_date > now', function ($duration) {

    $this->asset->maintainable()->update(['maintenance_manager_id' => $this->manager->id]);

    $preference = $this->manager->notification_preferences()->where('notification_type', 'end_date')->first();

    $formData = [
        ...$this->basicContractData,
        'contract_duration' => $duration
    ];

    $this->postToTenant('api.contracts.store', $formData);

    $formData = [
        ...$this->basicContractData,
        'contract_duration' => $duration
    ];

    $this->postToTenant('api.contracts.store', $formData);

    $formData = [
        ...$this->basicContractData,
        'contract_duration' => $duration
    ];

    $this->postToTenant('api.contracts.store', $formData);

    $contractOne = Contract::find(1);
    $contractTwo = Contract::find(2);
    $contractThree = Contract::find(3);

    assertDatabaseCount('scheduled_notifications', 6);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
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
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
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
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_date',
            'status' => 'pending',
            'scheduled_at' => $contractThree->end_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Contract',
            'notifiable_id' => $contractThree->id,
        ]
    );

    $contractThree->update(['end_date' => Carbon::now()]);

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
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_date',
            'scheduled_at' => $contractOne->end_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Contract',
            'notifiable_id' => $contractOne->id,
        ]
    );
    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
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

it('deletes end_date notifications for admin when notification preference end_date of user is disabled and status `pending`', function ($duration) {

    $contractOne = Contract::factory()->forLocation($this->asset)->create([
        'contract_duration' => $duration,
        'start_date' => Carbon::now(),
        'end_date' => ContractDurationEnum::from($duration)->addTo(Carbon::now())
    ]);

    $contractTwo = Contract::factory()->forLocation($this->asset)->create([
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

it('deletes end_date notifications for maintenance_manager when notification preference end_date of user is disabled and status `pending`', function ($duration) {

    $this->asset->maintainable()->update(['maintenance_manager_id' => $this->manager->id]);

    $preference = $this->manager->notification_preferences()->where('notification_type', 'end_date')->first();

    $formData = [
        ...$this->basicContractData,
        'contract_duration' => $duration
    ];

    $this->postToTenant('api.contracts.store', $formData);

    $formData = [
        ...$this->basicContractData,
        'contract_duration' => $duration
    ];

    $this->postToTenant('api.contracts.store', $formData);


    $contractOne = Contract::find(1);
    $contractTwo = Contract::find(2);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
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
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_date',
            'status' => 'pending',
            'scheduled_at' =>  ContractDurationEnum::from($duration)->addTo(Carbon::now())->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($contractTwo),
            'notifiable_id' => $contractTwo->id,
        ]
    );

    $contractTwoNotification = $contractTwo->notifications()->where('user_id', $this->manager->id)->first();
    $contractTwoNotification->update(['status' => 'sent']);

    $formData = [
        'asset_type' => 'contract',
        'notification_type' => 'end_date',
        'notification_delay_days' => $preference->notification_delay_days,
        'enabled' => false,
    ];

    $this->patchToTenant('api.notifications.update', $formData, $preference->id);

    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_date',
            'scheduled_at' =>  ContractDurationEnum::from($duration)->addTo(Carbon::now())->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($contractOne),
            'notifiable_id' => $contractOne->id,
        ]
    );

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_date',
            'status' => 'sent',
            'scheduled_at' =>  ContractDurationEnum::from($duration)->addTo(Carbon::now())->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($contractTwo),
            'notifiable_id' => $contractTwo->id,
        ]
    );
})->with(array_column(ContractDurationEnum::cases(), 'value'));

it('creates end_date notifications for admin when notification preference end_date of user is enabled and contract end_date > now', function ($duration) {

    $preference = $this->admin->notification_preferences()->where('notification_type', 'end_date')->first();

    $formData = [
        'asset_type' => 'contract',
        'notification_type' => 'end_date',
        'notification_delay_days' => $preference->notification_delay_days,
        'enabled' => false,
    ];

    $this->patchToTenant('api.notifications.update', $formData, $preference->id);

    $contractOne = Contract::factory()->forLocation($this->asset)->create([
        'contract_duration' => $duration,
        'start_date' => Carbon::now(),
        'end_date' => ContractDurationEnum::from($duration)->addTo(Carbon::now())
    ]);

    $contractTwo = Contract::factory()->forLocation($this->asset)->create([
        'contract_duration' => $duration,
        'start_date' => Carbon::now(),
        'end_date' => ContractDurationEnum::from($duration)->addTo(Carbon::now())
    ]);


    assertDatabaseCount('scheduled_notifications', 0);

    $contractTwo->update(['end_date' => Carbon::now()]);

    $formData = [
        'asset_type' => 'contract',
        'notification_type' => 'end_date',
        'notification_delay_days' => $preference->notification_delay_days,
        'enabled' => true,
    ];

    $response = $this->patchToTenant('api.notifications.update', $formData, $preference->id);

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
            'notifiable_id' => 1,
        ]
    );

    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'user_id' => $this->admin->id,
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'end_date',
            'status' => 'pending',
            'notifiable_type' => 'App\Models\Tenants\Contract',
            'notifiable_id' => 2,
        ]
    );
})->with(array_column(ContractDurationEnum::cases(), 'value'));

it('creates end_date notifications for maintenance_manager when notification preference end_date of user is enabled and contract end_date > now', function ($duration) {

    $preference = $this->manager->notification_preferences()->where('notification_type', 'end_date')->first();

    $formData = [
        'asset_type' => 'contract',
        'notification_type' => 'end_date',
        'notification_delay_days' => $preference->notification_delay_days,
        'enabled' => false,
    ];

    $this->patchToTenant('api.notifications.update', $formData, $preference->id);

    $this->asset->maintainable()->update(['maintenance_manager_id' => $this->manager->id]);

    $formData = [
        ...$this->basicContractData,
        'contract_duration' => $duration
    ];

    $this->postToTenant('api.contracts.store', $formData);

    $formData = [
        ...$this->basicContractData,
        'contract_duration' => $duration
    ];

    $this->postToTenant('api.contracts.store', $formData);


    $contractOne = Contract::find(1);
    $contractTwo = Contract::find(2);

    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notifiable_type' => get_class($contractOne),
            'notifiable_id' => $contractOne->id,
        ]
    );
    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notifiable_type' => get_class($contractTwo),
            'notifiable_id' => $contractTwo->id,
        ]
    );

    $contractTwo->update(['end_date' => Carbon::now()]);

    $formData = [
        'asset_type' => 'contract',
        'notification_type' => 'end_date',
        'notification_delay_days' => $preference->notification_delay_days,
        'enabled' => true,
    ];

    $this->patchToTenant('api.notifications.update', $formData, $preference->id);

    assertDatabaseCount('scheduled_notifications', 3);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_date',
            'status' => 'pending',
            'scheduled_at' => $contractOne->end_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($contractOne),
            'notifiable_id' => $contractOne->id,
        ]
    );

    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_date',
            'status' => 'pending',
            'notifiable_type' => get_class($contractTwo),
            'notifiable_id' => $contractTwo->id,
        ]
    );
})->with(array_column(ContractDurationEnum::cases(), 'value'));

it('updates end_date notificactions when contract duration changes', function ($firstDuration, $otherDuration) {
    $contractOne = Contract::factory()->forLocation($this->asset)->create([
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
        'start_date' => Carbon::now(),
        'contract_duration' => $otherDuration,
    ];

    $this->patchToTenant('api.contracts.update', $updatedContract, $contractOne->id);

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

it('creates end_date notifications for all maintenance managers linked to the contract via asset/location', function () {})->with(array_column(ContractDurationEnum::cases(), 'value'));
