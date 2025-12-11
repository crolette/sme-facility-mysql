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

    $this->siteType = LocationType::factory()->create(['level' => 'site']);
    $this->buildingType = LocationType::factory()->create(['level' => 'building']);
    $this->floorType = LocationType::factory()->create(['level' => 'floor']);
    $this->roomType = LocationType::factory()->create(['level' => 'room']);
    $this->assetType = CategoryType::factory()->create(['category' => 'asset']);

    $this->site = Site::factory()->withMaintainableData()->create();
    $this->building = Building::factory()->withMaintainableData()->create();
    $this->floor = Floor::factory()->withMaintainableData()->create();
    $this->provider = Provider::factory()->create();

    $this->room = Room::factory()->withMaintainableData()->create();

    $this->asset = Asset::factory()->withMaintainableData()->forLocation($this->room)->create();
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
        'notice_period' => NoticePeriodEnum::FOURTEEN_DAYS->value,
        'renewal_type' => ContractRenewalTypesEnum::AUTOMATIC->value,
        'status' => ContractStatusEnum::ACTIVE->value,

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


it('creates end_date notification when a contract is created at site`s creation', function ($duration) {

    $formData = [
        'name' => 'New site',
        'description' => 'Description new site',
        'locationType' => $this->siteType->id,

        'contracts' => [
            [
                ...$this->basicContractData,
                'end_date' => ContractDurationEnum::from($duration)->addTo(Carbon::now())
            ],
        ]
    ];

    $this->postToTenant('api.sites.store', $formData);

    $contract = Contract::first();
    $preference = $this->admin->notification_preferences()->where('notification_type', 'end_date')->first();

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
})->with(array_column(ContractDurationEnum::cases(), 'value'));

it('can detach a contract from a site and does not delete end_date notification for the admin', function () {

    $contract = Contract::factory()->forLocation($this->site)->create([
        ...$this->basicContractData,
        'end_date' => ContractDurationEnum::from(ContractDurationEnum::ONE_YEAR->value)->addTo(Carbon::now())
    ],);

    $formData = ['contract_id' => $contract->id];

    $this->deleteFromTenant('api.sites.contracts.delete', $this->site, $formData);

    $contract = Contract::first();
    $preference = $this->admin->notification_preferences()->where('notification_type', 'end_date')->first();

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
});


it('creates end_date notification when a contract is created at building`s creation', function ($duration) {

    $formData = [
        'name' => 'New building',
        'description' => 'Description new building',
        'levelType' => $this->site->id,
        'locationType' => $this->buildingType->id,

        'contracts' => [
            [
                ...$this->basicContractData,
                'end_date' => ContractDurationEnum::from($duration)->addTo(Carbon::now())
            ],
        ]
    ];

    $this->postToTenant('api.buildings.store', $formData);

    $contract = Contract::first();
    $preference = $this->admin->notification_preferences()->where('notification_type', 'end_date')->first();


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
})->with(array_column(ContractDurationEnum::cases(), 'value'));

it('can detach a contract from a building and does not delete end_date notification for the admin', function () {

    $contract = Contract::factory()->forLocation($this->building)->create([
        ...$this->basicContractData,
        'end_date' => ContractDurationEnum::from(ContractDurationEnum::ONE_YEAR->value)->addTo(Carbon::now())
    ],);

    $formData = ['contract_id' => $contract->id];

    $this->deleteFromTenant('api.buildings.contracts.delete', $this->building, $formData);

    $contract = Contract::first();
    $preference = $this->admin->notification_preferences()->where('notification_type', 'end_date')->first();

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
});


it('creates end_date notification when a contract is created at floor`s creation', function ($duration) {

    $formData = [
        'name' => 'New floor',
        'description' => 'Description new floor',
        'levelType' => $this->building->id,
        'locationType' => $this->floorType->id,

        'contracts' => [
            [
                ...$this->basicContractData,
                'end_date' => ContractDurationEnum::from($duration)->addTo(Carbon::now())
            ],
        ]
    ];

    $this->postToTenant('api.floors.store', $formData);

    $contract = Contract::first();
    $preference = $this->admin->notification_preferences()->where('notification_type', 'end_date')->first();


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
})->with(array_column(ContractDurationEnum::cases(), 'value'));


it('can detach a contract from a floor and does not delete end_date notification for the admin', function () {

    $contract = Contract::factory()->forLocation($this->floor)->create([
        ...$this->basicContractData,
        'end_date' => ContractDurationEnum::from(ContractDurationEnum::ONE_YEAR->value)->addTo(Carbon::now())
    ],);

    $formData = ['contract_id' => $contract->id];

    $this->deleteFromTenant('api.floors.contracts.delete', $this->floor, $formData);

    $preference = $this->admin->notification_preferences()->where('notification_type', 'end_date')->first();

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
});

it('creates end_date notification when a contract is created at room`s creation', function ($duration) {

    $formData = [
        'name' => 'New room',
        'description' => 'Description new room',
        'levelType' => $this->floor->id,
        'locationType' => $this->roomType->id,

        'contracts' => [
            [
                ...$this->basicContractData,
                'end_date' => ContractDurationEnum::from($duration)->addTo(Carbon::now())
            ],
        ]
    ];

    $this->postToTenant('api.rooms.store', $formData);

    $contract = Contract::first();
    $preference = $this->admin->notification_preferences()->where('notification_type', 'end_date')->first();

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
})->with(array_column(ContractDurationEnum::cases(), 'value'));

it('can detach a contract from a room and does not delete end_date notification for the admin', function () {

    $contract = Contract::factory()->forLocation($this->room)->create([
        ...$this->basicContractData,
        'end_date' => ContractDurationEnum::from(ContractDurationEnum::ONE_YEAR->value)->addTo(Carbon::now())
    ],);

    $formData = ['contract_id' => $contract->id];

    $this->deleteFromTenant('api.rooms.contracts.delete', $this->room, $formData);

    $preference = $this->admin->notification_preferences()->where('notification_type', 'end_date')->first();

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
});

it('creates end_date notification when a contract is created at asset`s creation', function ($duration) {

    $formData = [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'locationId' => $this->room->id,
        'locationType' => 'room',
        'locationReference' => $this->room->reference_code,
        'categoryId' => $this->assetType->id,

        'contracts' => [
            [
                ...$this->basicContractData,
                'end_date' => ContractDurationEnum::from($duration)->addTo(Carbon::now())
            ],
        ]
    ];

    $this->postToTenant('api.assets.store', $formData);

    $contract = Contract::first();
    $preference = $this->admin->notification_preferences()->where('notification_type', 'end_date')->first();

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
})->with(array_column(ContractDurationEnum::cases(), 'value'));

it('can detach a contract from an asset and does not delete end_date notification for the admin', function () {

    $contract = Contract::factory()->forLocation($this->asset)->create([
        ...$this->basicContractData,
        'end_date' => ContractDurationEnum::from(ContractDurationEnum::ONE_YEAR->value)->addTo(Carbon::now())
    ],);

    $formData = ['contract_id' => $contract->id];

    $this->deleteFromTenant('api.assets.contracts.delete', $this->asset, $formData);

    $contract = Contract::first();
    $preference = $this->admin->notification_preferences()->where('notification_type', 'end_date')->first();

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
});
