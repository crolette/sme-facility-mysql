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
    $this->site->refresh();
    $this->site->maintainable->manager()->associate($this->manager->id)->save();

    $this->building = Building::factory()->withMaintainableData()->create();
    $this->building->refresh();
    $this->building->maintainable->manager()->associate($this->manager->id)->save();

    $this->floor = Floor::factory()->withMaintainableData()->create();
    $this->floor->refresh();
    $this->floor->maintainable->manager()->associate($this->manager->id)->save();

    $this->room = Room::factory()->withMaintainableData()->create();
    $this->room->refresh();
    $this->room->maintainable->manager()->associate($this->manager->id)->save();

    $this->asset = Asset::factory()->withMaintainableData()->forLocation($this->room)->create();
    $this->asset->refresh();
    $this->asset->maintainable->manager()->associate($this->manager->id)->save();

    $this->provider = Provider::factory()->create();

    $this->basicContractData = [
        'provider_id' => $this->provider->id,
        'name' => 'Contrat de bail',
        'type' => ContractTypesEnum::ALLIN->value,
        'notes' => 'Nouveau contrat de bail 2025',
        'internal_reference' => 'Bail Site 2025',
        'provider_reference' => 'Provider reference 2025',
        'start_date' => Carbon::now()->toDateString(),
        'contract_duration' => ContractDurationEnum::ONE_YEAR->value,
        'renewal_type' => ContractRenewalTypesEnum::AUTOMATIC->value,
        'status' => ContractStatusEnum::ACTIVE->value,

    ];
});


it('creates end_date notification for manager when a contract is created at site`s creation', function ($duration) {

    $formData = [
        'name' => 'New site',
        'description' => 'Description new site',
        'locationType' => $this->siteType->id,
        'maintenance_manager_id' => $this->manager->id,

        'contracts' => [
            [
                ...$this->basicContractData,
                'end_date' => ContractDurationEnum::from($duration)->addTo(Carbon::now())
            ],
        ]
    ];

    $this->postToTenant('api.sites.store', $formData);

    $contract = Contract::first();
    $preference = $this->manager->notification_preferences()->where('notification_type', 'end_date')->first();

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_date',
            'scheduled_at' => $contract->end_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($contract),
            'notifiable_id' => $contract->id,
        ]
    );
})->with(array_column(ContractDurationEnum::cases(), 'value'));

it('creates end_date notification for manager when a contract is attached to an existing site', function ($duration) {

    $contract = Contract::factory()->create([
        ...$this->basicContractData,
        'end_date' => ContractDurationEnum::from($duration)->addTo(Carbon::now())
    ]);


    $formData = [
        'existing_contracts' => [
            $contract->id
        ]
    ];

    $response = $this->postToTenant('api.sites.contracts.post', $formData, $this->site);
    $response->assertSessionHasNoErrors();

    $preference = $this->manager->notification_preferences()->where('notification_type', 'end_date')->first();

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_date',
            'scheduled_at' => $contract->end_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($contract),
            'notifiable_id' => $contract->id,
        ]
    );
})->with(array_column(ContractDurationEnum::cases(), 'value'));

it('does not delete end_date notification of maintenance manager when contract is removed from a site but user is also maintenance manager from another asset/location where this contract is used', function () {

    $formData = [
        'name' => 'New site',
        'description' => 'Description new site',
        'locationType' => $this->siteType->id,
        'maintenance_manager_id' => $this->manager->id,

        'contracts' => [
            [
                ...$this->basicContractData,
                'end_date' => ContractDurationEnum::from(ContractDurationEnum::ONE_YEAR->value)->addTo(Carbon::now())
            ],
        ]
    ];

    $this->postToTenant('api.sites.store', $formData);

    $site = Site::orderBy('id', 'desc')->first();
    $contract = Contract::first();

    $this->site->contracts()->attach($contract);

    $preference = $this->manager->notification_preferences()->where('notification_type', 'end_date')->first();

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_date',
            'scheduled_at' => $contract->end_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($contract),
            'notifiable_id' => $contract->id,
        ]
    );


    $formData = ['contract_id' => $contract->id];

    $this->deleteFromTenant('api.sites.contracts.delete', $site, $formData);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_date',
            'scheduled_at' => $contract->end_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($contract),
            'notifiable_id' => $contract->id,
        ]
    );
});

it('does not delete end_date notification of maintenance manager when contract is removed from a site but user is admin', function () {

    $formData = [
        'name' => 'New site',
        'description' => 'Description new site',
        'locationType' => $this->siteType->id,
        'maintenance_manager_id' => $this->manager->id,

        'contracts' => [
            [
                ...$this->basicContractData,
                'end_date' => ContractDurationEnum::from(ContractDurationEnum::ONE_YEAR->value)->addTo(Carbon::now())
            ],
        ]
    ];

    $this->postToTenant('api.sites.store', $formData);

    $site = Site::orderBy('id', 'desc')->first();
    $contract = Contract::first();

    $this->manager->syncRoles('Admin');
    $preference = $this->manager->notification_preferences()->where('notification_type', 'end_date')->first();

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_date',
            'scheduled_at' => $contract->end_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($contract),
            'notifiable_id' => $contract->id,
        ]
    );


    $formData = ['contract_id' => $contract->id];

    $this->deleteFromTenant('api.sites.contracts.delete', $site, $formData);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_date',
            'scheduled_at' => $contract->end_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($contract),
            'notifiable_id' => $contract->id,
        ]
    );
});

it('deletes end_date notification of maintenance manager when contract is removed from a site and manager is not linked other ways', function () {

    $formData = [
        'name' => 'New site',
        'description' => 'Description new site',
        'locationType' => $this->siteType->id,
        'maintenance_manager_id' => $this->manager->id,

        'contracts' => [
            [
                ...$this->basicContractData,
                'end_date' => ContractDurationEnum::from(ContractDurationEnum::ONE_YEAR->value)->addTo(Carbon::now())
            ],
        ]
    ];

    $this->postToTenant('api.sites.store', $formData);

    $site = Site::orderBy('id', 'desc')->first();
    $contract = Contract::first();


    $preference = $this->manager->notification_preferences()->where('notification_type', 'end_date')->first();

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_date',
            'scheduled_at' => $contract->end_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($contract),
            'notifiable_id' => $contract->id,
        ]
    );


    $formData = ['contract_id' => $contract->id];

    $this->deleteFromTenant('api.sites.contracts.delete', $site, $formData);

    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_date',
            'scheduled_at' => $contract->end_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($contract),
            'notifiable_id' => $contract->id,
        ]
    );
});

it('creates end_date notification for manager when a contract is created at building`s creation', function ($duration) {

    $formData = [
        'name' => 'New building',
        'description' => 'Description new building',
        'levelType' => $this->site->id,
        'locationType' => $this->buildingType->id,
        'maintenance_manager_id' => $this->manager->id,

        'contracts' => [
            [
                ...$this->basicContractData,
                'end_date' => ContractDurationEnum::from($duration)->addTo(Carbon::now())
            ],
        ]
    ];

    $this->postToTenant('api.buildings.store', $formData);

    $contract = Contract::first();
    $preference = $this->manager->notification_preferences()->where('notification_type', 'end_date')->first();

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_date',
            'scheduled_at' => $contract->end_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($contract),
            'notifiable_id' => $contract->id,
        ]
    );
})->with(array_column(ContractDurationEnum::cases(), 'value'));

it('creates end_date notification for manager when a contract is attached to an existing building', function ($duration) {

    $contract = Contract::factory()->create([
        ...$this->basicContractData,
        'end_date' => ContractDurationEnum::from($duration)->addTo(Carbon::now())
    ]);


    $formData = [
        'existing_contracts' => [
            $contract->id
        ]
    ];

    $response = $this->postToTenant('api.buildings.contracts.post', $formData, $this->building);
    $response->assertSessionHasNoErrors();

    $preference = $this->manager->notification_preferences()->where('notification_type', 'end_date')->first();

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_date',
            'scheduled_at' => $contract->end_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($contract),
            'notifiable_id' => $contract->id,
        ]
    );
})->with(array_column(ContractDurationEnum::cases(), 'value'));

it('does not delete end_date notification of maintenance manager when contract is removed from a building but user is also maintenance manager from another asset/location where this contract is used', function () {

    $formData = [
        'name' => 'New building',
        'description' => 'Description new building',
        'levelType' => $this->site->id,
        'locationType' => $this->buildingType->id,
        'maintenance_manager_id' => $this->manager->id,

        'contracts' => [
            [
                ...$this->basicContractData,
                'end_date' => ContractDurationEnum::from(ContractDurationEnum::ONE_YEAR->value)->addTo(Carbon::now())
            ],
        ]
    ];

    $this->postToTenant('api.buildings.store', $formData);

    $building = Building::orderBy('id', 'desc')->first();
    $contract = Contract::first();

    $this->site->contracts()->attach($contract);

    $preference = $this->manager->notification_preferences()->where('notification_type', 'end_date')->first();

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_date',
            'scheduled_at' => $contract->end_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($contract),
            'notifiable_id' => $contract->id,
        ]
    );


    $formData = ['contract_id' => $contract->id];

    $this->deleteFromTenant('api.buildings.contracts.delete', $building, $formData);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_date',
            'scheduled_at' => $contract->end_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($contract),
            'notifiable_id' => $contract->id,
        ]
    );
});

it('does not delete end_date notification of maintenance manager when contract is removed from a building but user is admin', function () {

    $formData = [
        'name' => 'New building',
        'description' => 'Description new building',
        'levelType' => $this->site->id,
        'locationType' => $this->buildingType->id,
        'maintenance_manager_id' => $this->manager->id,

        'contracts' => [
            [
                ...$this->basicContractData,
                'end_date' => ContractDurationEnum::from(ContractDurationEnum::ONE_YEAR->value)->addTo(Carbon::now())
            ],
        ]
    ];

    $this->postToTenant('api.buildings.store', $formData);

    $building = Building::orderBy('id', 'desc')->first();
    $contract = Contract::first();

    $this->manager->syncRoles('Admin');
    $preference = $this->manager->notification_preferences()->where('notification_type', 'end_date')->first();

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_date',
            'scheduled_at' => $contract->end_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($contract),
            'notifiable_id' => $contract->id,
        ]
    );


    $formData = ['contract_id' => $contract->id];

    $this->deleteFromTenant('api.buildings.contracts.delete', $building, $formData);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_date',
            'scheduled_at' => $contract->end_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($contract),
            'notifiable_id' => $contract->id,
        ]
    );
});

it('deletes end_date notification of maintenance manager when contract is removed from a building and manager is not linked other ways', function () {

    $formData = [
        'name' => 'New building',
        'description' => 'Description new building',
        'levelType' => $this->site->id,
        'locationType' => $this->buildingType->id,
        'maintenance_manager_id' => $this->manager->id,

        'contracts' => [
            [
                ...$this->basicContractData,
                'end_date' => ContractDurationEnum::from(ContractDurationEnum::ONE_YEAR->value)->addTo(Carbon::now())
            ],
        ]
    ];

    $this->postToTenant('api.buildings.store', $formData);

    $building = Building::orderBy('id', 'desc')->first();
    $contract = Contract::first();


    $preference = $this->manager->notification_preferences()->where('notification_type', 'end_date')->first();

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_date',
            'scheduled_at' => $contract->end_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($contract),
            'notifiable_id' => $contract->id,
        ]
    );


    $formData = ['contract_id' => $contract->id];

    $this->deleteFromTenant('api.buildings.contracts.delete', $building, $formData);

    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
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
        'maintenance_manager_id' => $this->manager->id,

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

it('creates end_date notification for manager when a contract is attached to an existing floor', function ($duration) {

    $contract = Contract::factory()->create([
        ...$this->basicContractData,
        'end_date' => ContractDurationEnum::from($duration)->addTo(Carbon::now())
    ]);


    $formData = [
        'existing_contracts' => [
            $contract->id
        ]
    ];

    $response = $this->postToTenant('api.floors.contracts.post', $formData, $this->floor);
    $response->assertSessionHasNoErrors();

    $preference = $this->manager->notification_preferences()->where('notification_type', 'end_date')->first();

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_date',
            'scheduled_at' => $contract->end_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($contract),
            'notifiable_id' => $contract->id,
        ]
    );
})->with(array_column(ContractDurationEnum::cases(), 'value'));

it('does not delete end_date notification of maintenance manager when contract is removed from a floor but user is also maintenance manager from another asset/location where this contract is used', function () {

    $formData = [
        'name' => 'New floor',
        'description' => 'Description new floor',
        'levelType' => $this->building->id,
        'locationType' => $this->floorType->id,
        'maintenance_manager_id' => $this->manager->id,

        'contracts' => [
            [
                ...$this->basicContractData,
                'end_date' => ContractDurationEnum::from(ContractDurationEnum::ONE_YEAR->value)->addTo(Carbon::now())
            ],
        ]
    ];

    $this->postToTenant('api.floors.store', $formData);

    $floor = Floor::orderBy('id', 'desc')->first();
    $contract = Contract::first();

    $this->site->contracts()->attach($contract);

    $preference = $this->manager->notification_preferences()->where('notification_type', 'end_date')->first();

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_date',
            'scheduled_at' => $contract->end_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($contract),
            'notifiable_id' => $contract->id,
        ]
    );


    $formData = ['contract_id' => $contract->id];

    $this->deleteFromTenant('api.floors.contracts.delete', $floor, $formData);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_date',
            'scheduled_at' => $contract->end_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($contract),
            'notifiable_id' => $contract->id,
        ]
    );
});

it('does not delete end_date notification of maintenance manager when contract is removed from a floor but user is admin', function () {

    $formData = [
        'name' => 'New floor',
        'description' => 'Description new floor',
        'levelType' => $this->building->id,
        'locationType' => $this->floorType->id,
        'maintenance_manager_id' => $this->manager->id,

        'contracts' => [
            [
                ...$this->basicContractData,
                'end_date' => ContractDurationEnum::from(ContractDurationEnum::ONE_YEAR->value)->addTo(Carbon::now())
            ],
        ]
    ];

    $this->postToTenant('api.floors.store', $formData);

    $floor = Floor::orderBy('id', 'desc')->first();
    $contract = Contract::first();

    $this->manager->syncRoles('Admin');
    $preference = $this->manager->notification_preferences()->where('notification_type', 'end_date')->first();

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_date',
            'scheduled_at' => $contract->end_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($contract),
            'notifiable_id' => $contract->id,
        ]
    );


    $formData = ['contract_id' => $contract->id];

    $this->deleteFromTenant('api.floors.contracts.delete', $floor, $formData);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_date',
            'scheduled_at' => $contract->end_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($contract),
            'notifiable_id' => $contract->id,
        ]
    );
});

it('deletes end_date notification of maintenance manager when contract is removed from a floor and manager is not linked other ways', function () {

    $formData = [
        'name' => 'New floor',
        'description' => 'Description new floor',
        'levelType' => $this->building->id,
        'locationType' => $this->floorType->id,
        'maintenance_manager_id' => $this->manager->id,

        'contracts' => [
            [
                ...$this->basicContractData,
                'end_date' => ContractDurationEnum::from(ContractDurationEnum::ONE_YEAR->value)->addTo(Carbon::now())
            ],
        ]
    ];

    $this->postToTenant('api.floors.store', $formData);

    $floor = Floor::orderBy('id', 'desc')->first();
    $contract = Contract::first();


    $preference = $this->manager->notification_preferences()->where('notification_type', 'end_date')->first();

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_date',
            'scheduled_at' => $contract->end_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($contract),
            'notifiable_id' => $contract->id,
        ]
    );


    $formData = ['contract_id' => $contract->id];

    $this->deleteFromTenant('api.floors.contracts.delete', $floor, $formData);

    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
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
        'maintenance_manager_id' => $this->manager->id,

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

it('creates end_date notification for manager when a contract is attached to an existing room', function ($duration) {

    $contract = Contract::factory()->create([
        ...$this->basicContractData,
        'end_date' => ContractDurationEnum::from($duration)->addTo(Carbon::now())
    ]);


    $formData = [
        'existing_contracts' => [
            $contract->id
        ]
    ];

    $response = $this->postToTenant('api.rooms.contracts.post', $formData, $this->room);
    $response->assertSessionHasNoErrors();

    $preference = $this->manager->notification_preferences()->where('notification_type', 'end_date')->first();

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_date',
            'scheduled_at' => $contract->end_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($contract),
            'notifiable_id' => $contract->id,
        ]
    );
})->with(array_column(ContractDurationEnum::cases(), 'value'));

it('does not delete end_date notification of maintenance manager when contract is removed from a room but user is also maintenance manager from another asset/location where this contract is used', function () {

    $formData = [
        'name' => 'New room',
        'description' => 'Description new room',
        'levelType' => $this->floor->id,
        'locationType' => $this->roomType->id,
        'maintenance_manager_id' => $this->manager->id,

        'contracts' => [
            [
                ...$this->basicContractData,
                'end_date' => ContractDurationEnum::from(ContractDurationEnum::ONE_YEAR->value)->addTo(Carbon::now())
            ],
        ]
    ];

    $this->postToTenant('api.rooms.store', $formData);

    $room = Room::orderBy('id', 'desc')->first();
    $contract = Contract::first();

    $this->site->contracts()->attach($contract);

    $preference = $this->manager->notification_preferences()->where('notification_type', 'end_date')->first();

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_date',
            'scheduled_at' => $contract->end_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($contract),
            'notifiable_id' => $contract->id,
        ]
    );


    $formData = ['contract_id' => $contract->id];

    $this->deleteFromTenant('api.rooms.contracts.delete', $room, $formData);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_date',
            'scheduled_at' => $contract->end_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($contract),
            'notifiable_id' => $contract->id,
        ]
    );
});

it('does not delete end_date notification of maintenance manager when contract is removed from a room but user is admin', function () {

    $formData = [
        'name' => 'New room',
        'description' => 'Description new room',
        'levelType' => $this->floor->id,
        'locationType' => $this->roomType->id,
        'maintenance_manager_id' => $this->manager->id,

        'contracts' => [
            [
                ...$this->basicContractData,
                'end_date' => ContractDurationEnum::from(ContractDurationEnum::ONE_YEAR->value)->addTo(Carbon::now())
            ],
        ]
    ];

    $this->postToTenant('api.rooms.store', $formData);

    $room = Room::orderBy('id', 'desc')->first();
    $contract = Contract::first();

    $this->manager->syncRoles('Admin');
    $preference = $this->manager->notification_preferences()->where('notification_type', 'end_date')->first();

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_date',
            'scheduled_at' => $contract->end_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($contract),
            'notifiable_id' => $contract->id,
        ]
    );


    $formData = ['contract_id' => $contract->id];

    $this->deleteFromTenant('api.rooms.contracts.delete', $room, $formData);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_date',
            'scheduled_at' => $contract->end_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($contract),
            'notifiable_id' => $contract->id,
        ]
    );
});

it('deletes end_date notification of maintenance manager when contract is removed from a room and manager is not linked other ways', function () {

    $formData = [
        'name' => 'New room',
        'description' => 'Description new room',
        'levelType' => $this->floor->id,
        'locationType' => $this->roomType->id,
        'maintenance_manager_id' => $this->manager->id,

        'contracts' => [
            [
                ...$this->basicContractData,
                'end_date' => ContractDurationEnum::from(ContractDurationEnum::ONE_YEAR->value)->addTo(Carbon::now())
            ],
        ]
    ];

    $this->postToTenant('api.rooms.store', $formData);

    $room = Room::orderBy('id', 'desc')->first();
    $contract = Contract::first();


    $preference = $this->manager->notification_preferences()->where('notification_type', 'end_date')->first();

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_date',
            'scheduled_at' => $contract->end_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($contract),
            'notifiable_id' => $contract->id,
        ]
    );


    $formData = ['contract_id' => $contract->id];

    $this->deleteFromTenant('api.rooms.contracts.delete', $room, $formData);

    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
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
        'maintenance_manager_id' => $this->manager->id,

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

it('creates end_date notification for manager when a contract is attached to an existing asset', function ($duration) {

    $contract = Contract::factory()->create([
        ...$this->basicContractData,
        'end_date' => ContractDurationEnum::from($duration)->addTo(Carbon::now())
    ]);


    $formData = [
        'existing_contracts' => [
            $contract->id
        ]
    ];

    $response = $this->postToTenant('api.assets.contracts.post', $formData, $this->asset);
    $response->assertSessionHasNoErrors();

    $preference = $this->manager->notification_preferences()->where('notification_type', 'end_date')->first();

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_date',
            'scheduled_at' => $contract->end_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($contract),
            'notifiable_id' => $contract->id,
        ]
    );
})->with(array_column(ContractDurationEnum::cases(), 'value'));

it('does not delete end_date notification of maintenance manager when contract is removed from an asset but user is also maintenance manager from another asset/location where this contract is used', function () {

    $formData = [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'locationId' => $this->room->id,
        'locationType' => 'room',
        'locationReference' => $this->room->reference_code,
        'categoryId' => $this->assetType->id,
        'maintenance_manager_id' => $this->manager->id,

        'contracts' => [
            [
                ...$this->basicContractData,
                'end_date' => ContractDurationEnum::from(ContractDurationEnum::ONE_YEAR->value)->addTo(Carbon::now())
            ],
        ]
    ];

    $this->postToTenant('api.assets.store', $formData);

    $asset = Asset::orderBy('id', 'desc')->first();
    $contract = Contract::first();

    $this->site->contracts()->attach($contract);

    $preference = $this->manager->notification_preferences()->where('notification_type', 'end_date')->first();

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_date',
            'scheduled_at' => $contract->end_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($contract),
            'notifiable_id' => $contract->id,
        ]
    );


    $formData = ['contract_id' => $contract->id];

    $this->deleteFromTenant('api.assets.contracts.delete', $asset, $formData);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_date',
            'scheduled_at' => $contract->end_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($contract),
            'notifiable_id' => $contract->id,
        ]
    );
});

it('does not delete end_date notification of maintenance manager when contract is removed from an asset but user is admin', function () {

    $formData = [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'locationId' => $this->room->id,
        'locationType' => 'room',
        'locationReference' => $this->room->reference_code,
        'categoryId' => $this->assetType->id,
        'maintenance_manager_id' => $this->manager->id,

        'contracts' => [
            [
                ...$this->basicContractData,
                'end_date' => ContractDurationEnum::from(ContractDurationEnum::ONE_YEAR->value)->addTo(Carbon::now())
            ],
        ]
    ];

    $this->postToTenant('api.assets.store', $formData);

    $asset = Asset::orderBy('id', 'desc')->first();
    $contract = Contract::first();

    $this->manager->syncRoles('Admin');
    $preference = $this->manager->notification_preferences()->where('notification_type', 'end_date')->first();

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_date',
            'scheduled_at' => $contract->end_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($contract),
            'notifiable_id' => $contract->id,
        ]
    );


    $formData = ['contract_id' => $contract->id];

    $this->deleteFromTenant('api.assets.contracts.delete', $asset, $formData);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_date',
            'scheduled_at' => $contract->end_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($contract),
            'notifiable_id' => $contract->id,
        ]
    );
});

it('deletes end_date notification of maintenance manager when contract is removed from an asset and manager is not linked other ways', function () {

    $formData = [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'locationId' => $this->room->id,
        'locationType' => 'room',
        'locationReference' => $this->room->reference_code,
        'categoryId' => $this->assetType->id,
        'maintenance_manager_id' => $this->manager->id,

        'contracts' => [
            [
                ...$this->basicContractData,
                'end_date' => ContractDurationEnum::from(ContractDurationEnum::ONE_YEAR->value)->addTo(Carbon::now())
            ],
        ]
    ];

    $this->postToTenant('api.assets.store', $formData);

    $asset = Asset::orderBy('id', 'desc')->first();
    $contract = Contract::first();


    $preference = $this->manager->notification_preferences()->where('notification_type', 'end_date')->first();

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_date',
            'scheduled_at' => $contract->end_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($contract),
            'notifiable_id' => $contract->id,
        ]
    );


    $formData = ['contract_id' => $contract->id];

    $this->deleteFromTenant('api.assets.contracts.delete', $asset, $formData);

    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_date',
            'scheduled_at' => $contract->end_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => get_class($contract),
            'notifiable_id' => $contract->id,
        ]
    );
});
