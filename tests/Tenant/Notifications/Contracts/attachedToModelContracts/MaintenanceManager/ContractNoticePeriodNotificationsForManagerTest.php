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
        'contract_duration' => ContractDurationEnum::ONE_MONTH->value,
        'notice_period' => NoticePeriodEnum::FOURTEEN_DAYS->value,
        'renewal_type' => ContractRenewalTypesEnum::AUTOMATIC->value,
        'status' => ContractStatusEnum::ACTIVE->value,
    ];
});

it('creates the notice_date notification for a manager when a contract is created at site`s creation', function ($duration, $period) {

    $endDate = ContractDurationEnum::from($duration)->addTo(Carbon::now());
    $noticeDate = NoticePeriodEnum::from($period)->subFrom($endDate)->toDateString();

    if ($noticeDate > Carbon::now()) {

        $formData = [
            'name' => 'New site',
            'description' => 'Description new site',
            'locationType' => $this->siteType->id,
            'maintenance_manager_id' => $this->manager->id,

            'contracts' => [
                [
                    ...$this->basicContractData,
                    'contract_duration' => $duration,
                    'notice_period' => $period,
                    'notice_date' => $noticeDate
                ]
            ]
        ];

        $response = $this->postToTenant('api.sites.store', $formData);
        $response->assertSessionHasNoErrors();

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
            if ($d !== $n)
                $combinations[] = [$d, $n];
        }
    }
    return $combinations;
});

it('creates the notice_date notification for a manager when a contract is created at building`s creation', function ($duration, $period) {

    $endDate = ContractDurationEnum::from($duration)->addTo(Carbon::now());
    $noticeDate = NoticePeriodEnum::from($period)->subFrom($endDate)->toDateString();

    if ($noticeDate > Carbon::now()) {

        $formData = [
            'name' => 'New building',
            'description' => 'Description new building',
            'levelType' => $this->site->id,
            'locationType' => $this->buildingType->id,
            'maintenance_manager_id' => $this->manager->id,

            'contracts' => [
                [
                    ...$this->basicContractData,
                    'contract_duration' => $duration,
                    'notice_period' => $period,
                    'notice_date' => $noticeDate
                ]
            ]
        ];

        $response = $this->postToTenant('api.buildings.store', $formData);
        $response->assertSessionHasNoErrors();

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
            if ($d !== $n)
                $combinations[] = [$d, $n];
        }
    }
    return $combinations;
});

it('creates the notice_date notification for a manager when a contract is created at floors`s creation', function ($duration, $period) {

    $endDate = ContractDurationEnum::from($duration)->addTo(Carbon::now());
    $noticeDate = NoticePeriodEnum::from($period)->subFrom($endDate)->toDateString();

    if ($noticeDate > Carbon::now()) {

        $formData = [
            'name' => 'New floor',
            'description' => 'Description new floor',
            'levelType' => $this->building->id,
            'locationType' => $this->floorType->id,
            'maintenance_manager_id' => $this->manager->id,

            'contracts' => [
                [
                    ...$this->basicContractData,
                    'contract_duration' => $duration,
                    'notice_period' => $period,
                    'notice_date' => $noticeDate
                ]
            ]
        ];

        $response = $this->postToTenant('api.floors.store', $formData);
        $response->assertSessionHasNoErrors();

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
            if ($d !== $n)
                $combinations[] = [$d, $n];
        }
    }
    return $combinations;
});

it('creates the notice_date notification for a manager when a contract is created at rooms`s creation', function ($duration, $period) {

    $endDate = ContractDurationEnum::from($duration)->addTo(Carbon::now());
    $noticeDate = NoticePeriodEnum::from($period)->subFrom($endDate)->toDateString();

    if ($noticeDate > Carbon::now()) {

        $formData = [
            'name' => 'New room',
            'description' => 'Description new room',
            'levelType' => $this->floor->id,
            'locationType' => $this->roomType->id,
            'maintenance_manager_id' => $this->manager->id,

            'contracts' => [
                [
                    ...$this->basicContractData,
                    'contract_duration' => $duration,
                    'notice_period' => $period,
                    'notice_date' => $noticeDate
                ]
            ]
        ];

        $response = $this->postToTenant('api.rooms.store', $formData);
        $response->assertSessionHasNoErrors();

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
            if ($d !== $n)
                $combinations[] = [$d, $n];
        }
    }
    return $combinations;
});

it('creates the notice_date notification for a manager when a contract is created at assets`s creation', function ($duration, $period) {

    $endDate = ContractDurationEnum::from($duration)->addTo(Carbon::now());
    $noticeDate = NoticePeriodEnum::from($period)->subFrom($endDate)->toDateString();

    if ($noticeDate > Carbon::now()) {

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
                    'contract_duration' => $duration,
                    'notice_period' => $period,
                    'notice_date' => $noticeDate
                ]
            ]
        ];

        $response = $this->postToTenant('api.assets.store', $formData);
        $response->assertSessionHasNoErrors();

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

it('creates the notice_date notification for a manager when an existing contract is attached at site`s creation', function ($duration, $period) {

    $endDate = ContractDurationEnum::from($duration)->addTo(Carbon::now());
    $noticeDate = NoticePeriodEnum::from($period)->subFrom($endDate)->toDateString();

    $contract = Contract::factory()->create([
        ...$this->basicContractData,
        'contract_duration' => $duration,
        'notice_period' => $period,
        'notice_date' => $noticeDate
    ]);

    if ($noticeDate > Carbon::now()) {

        $formData = [
            'name' => 'New site',
            'description' => 'Description new site',
            'locationType' => $this->siteType->id,
            'maintenance_manager_id' => $this->manager->id,

            'existing_contracts' => [
                $contract->id
            ]
        ];

        $response = $this->postToTenant('api.sites.store', $formData);
        $response->assertSessionHasNoErrors();

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
            if ($d !== $n)
                $combinations[] = [$d, $n];
        }
    }
    return $combinations;
});

it('creates the notice_date notification for a manager when an existing contract is attached at building`s creation', function ($duration, $period) {

    $endDate = ContractDurationEnum::from($duration)->addTo(Carbon::now());
    $noticeDate = NoticePeriodEnum::from($period)->subFrom($endDate)->toDateString();

    $contract = Contract::factory()->create([
        ...$this->basicContractData,
        'contract_duration' => $duration,
        'notice_period' => $period,
        'notice_date' => $noticeDate
    ]);

    if ($noticeDate > Carbon::now()) {

        $formData = [
            'name' => 'New building',
            'description' => 'Description new building',
            'levelType' => $this->site->id,
            'locationType' => $this->buildingType->id,
            'maintenance_manager_id' => $this->manager->id,

            'existing_contracts' => [
                $contract->id
            ]
        ];

        $response = $this->postToTenant('api.buildings.store', $formData);
        $response->assertSessionHasNoErrors();

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

it('creates the notice_date notification for a manager when an existing contract is attached at floor`s creation', function ($duration, $period) {

    $endDate = ContractDurationEnum::from($duration)->addTo(Carbon::now());
    $noticeDate = NoticePeriodEnum::from($period)->subFrom($endDate)->toDateString();

    $contract = Contract::factory()->create([
        ...$this->basicContractData,
        'contract_duration' => $duration,
        'notice_period' => $period,
        'notice_date' => $noticeDate
    ]);

    if ($noticeDate > Carbon::now()) {

        $formData = [
            'name' => 'New floor',
            'description' => 'Description new floor',
            'levelType' => $this->building->id,
            'locationType' => $this->floorType->id,
            'maintenance_manager_id' => $this->manager->id,

            'existing_contracts' => [
                $contract->id
            ]
        ];

        $response = $this->postToTenant('api.floors.store', $formData);
        $response->assertSessionHasNoErrors();

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
            if ($d !== $n)
                $combinations[] = [$d, $n];
        }
    }
    return $combinations;
});

it('creates the notice_date notification for a manager when an existing contract is attached at room`s creation', function ($duration, $period) {

    $endDate = ContractDurationEnum::from($duration)->addTo(Carbon::now());
    $noticeDate = NoticePeriodEnum::from($period)->subFrom($endDate)->toDateString();

    $contract = Contract::factory()->create([
        ...$this->basicContractData,
        'contract_duration' => $duration,
        'notice_period' => $period,
        'notice_date' => $noticeDate
    ]);

    if ($noticeDate > Carbon::now()) {

        $formData = [
            'name' => 'New room',
            'description' => 'Description new room',
            'levelType' => $this->floor->id,
            'locationType' => $this->roomType->id,
            'maintenance_manager_id' => $this->manager->id,

            'existing_contracts' => [
                $contract->id
            ]
        ];

        $response = $this->postToTenant('api.rooms.store', $formData);
        $response->assertSessionHasNoErrors();

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
            if ($d !== $n)
                if ($d !== $n)
                    $combinations[] = [$d, $n];
        }
    }
    return $combinations;
});

it('creates the notice_date notification for a manager when an existing contract is attached at asset`s creation', function ($duration, $period) {

    $endDate = ContractDurationEnum::from($duration)->addTo(Carbon::now());
    $noticeDate = NoticePeriodEnum::from($period)->subFrom($endDate)->toDateString();

    $contract = Contract::factory()->create([
        ...$this->basicContractData,
        'contract_duration' => $duration,
        'notice_period' => $period,
        'notice_date' => $noticeDate
    ]);

    if ($noticeDate > Carbon::now()) {

        $formData = [
            'name' => 'New asset',
            'description' => 'Description new asset',
            'locationId' => $this->room->id,
            'locationType' => 'room',
            'locationReference' => $this->room->reference_code,
            'categoryId' => $this->assetType->id,
            'maintenance_manager_id' => $this->manager->id,

            'existing_contracts' => [
                $contract->id
            ]
        ];

        $response = $this->postToTenant('api.assets.store', $formData);
        $response->assertSessionHasNoErrors();

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
            if ($d !== $n)
                if ($d !== $n)
                    $combinations[] = [$d, $n];
        }
    }
    return $combinations;
});

it('creates notice_date notification for manager when a contract is attached to an existing site', function ($duration, $period) {

    $endDate = ContractDurationEnum::from($duration)->addTo(Carbon::now());
    $noticeDate = NoticePeriodEnum::from($period)->subFrom($endDate)->toDateString();

    if ($noticeDate > Carbon::now()) {
        $contract = Contract::factory()->create([
            ...$this->basicContractData,
            'contract_duration' => $duration,
            'notice_period' => $period,
            'notice_date' => $noticeDate
        ]);


        $formData = [
            'existing_contracts' => [
                $contract->id
            ]
        ];

        $response = $this->postToTenant('api.sites.contracts.post', $formData, $this->site);
        $response->assertSessionHasNoErrors();

        $preference = $this->manager->notification_preferences()->where('notification_type', 'notice_date')->first();

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
            if ($d !== $n)
                $combinations[] = [$d, $n];
        }
    }
    return $combinations;
});

it('creates notice_date notification for manager when a contract is attached to an existing building', function ($duration, $period) {

    $endDate = ContractDurationEnum::from($duration)->addTo(Carbon::now());
    $noticeDate = NoticePeriodEnum::from($period)->subFrom($endDate)->toDateString();

    if ($noticeDate > Carbon::now()) {
        $contract = Contract::factory()->create([
            ...$this->basicContractData,
            'contract_duration' => $duration,
            'notice_period' => $period,
            'notice_date' => $noticeDate
        ]);


        $formData = [
            'existing_contracts' => [
                $contract->id
            ]
        ];

        $response = $this->postToTenant('api.buildings.contracts.post', $formData, $this->building);
        $response->assertSessionHasNoErrors();

        $preference = $this->manager->notification_preferences()->where('notification_type', 'notice_date')->first();

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
            if ($d !== $n)
                $combinations[] = [$d, $n];
        }
    }
    return $combinations;
});

it('creates notice_date notification for manager when a contract is attached to an existing floor', function ($duration, $period) {

    $endDate = ContractDurationEnum::from($duration)->addTo(Carbon::now());
    $noticeDate = NoticePeriodEnum::from($period)->subFrom($endDate)->toDateString();

    if ($noticeDate > Carbon::now()) {
        $contract = Contract::factory()->create([
            ...$this->basicContractData,
            'contract_duration' => $duration,
            'notice_period' => $period,
            'notice_date' => $noticeDate
        ]);


        $formData = [
            'existing_contracts' => [
                $contract->id
            ]
        ];

        $response = $this->postToTenant('api.floors.contracts.post', $formData, $this->floor);
        $response->assertSessionHasNoErrors();

        $preference = $this->manager->notification_preferences()->where('notification_type', 'notice_date')->first();

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
            if ($d !== $n)
                $combinations[] = [$d, $n];
        }
    }
    return $combinations;
});

it('creates notice_date notification for manager when a contract is attached to an existing room', function ($duration, $period) {

    $endDate = ContractDurationEnum::from($duration)->addTo(Carbon::now());
    $noticeDate = NoticePeriodEnum::from($period)->subFrom($endDate)->toDateString();

    if ($noticeDate > Carbon::now()) {
        $contract = Contract::factory()->create([
            ...$this->basicContractData,
            'contract_duration' => $duration,
            'notice_period' => $period,
            'notice_date' => $noticeDate
        ]);


        $formData = [
            'existing_contracts' => [
                $contract->id
            ]
        ];

        $response = $this->postToTenant('api.rooms.contracts.post', $formData, $this->room);
        $response->assertSessionHasNoErrors();

        $preference = $this->manager->notification_preferences()->where('notification_type', 'notice_date')->first();

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
            if ($d !== $n)
                $combinations[] = [$d, $n];
        }
    }
    return $combinations;
});

it('creates notice_date notification for manager when a contract is attached to an existing asset', function ($duration, $period) {

    $endDate = ContractDurationEnum::from($duration)->addTo(Carbon::now());
    $noticeDate = NoticePeriodEnum::from($period)->subFrom($endDate)->toDateString();

    if ($noticeDate > Carbon::now()) {
        $contract = Contract::factory()->create([
            ...$this->basicContractData,
            'contract_duration' => $duration,
            'notice_period' => $period,
            'notice_date' => $noticeDate
        ]);


        $formData = [
            'existing_contracts' => [
                $contract->id
            ]
        ];

        $response = $this->postToTenant('api.assets.contracts.post', $formData, $this->asset);
        $response->assertSessionHasNoErrors();

        $preference = $this->manager->notification_preferences()->where('notification_type', 'notice_date')->first();

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
            if ($d !== $n)
                $combinations[] = [$d, $n];
        }
    }
    return $combinations;
});



it('deletes notice_date notification for the maintenance manager when a contract is detached from a site', function () {
    $endDate = ContractDurationEnum::from(ContractDurationEnum::ONE_YEAR->value)->addTo(Carbon::now());
    $noticeDate = NoticePeriodEnum::from(NoticePeriodEnum::DEFAULT->value)->subFrom($endDate)->toDateString();

    $contract = Contract::factory()->forLocation($this->site)->create([
        ...$this->basicContractData,
        'contract_duration' => ContractDurationEnum::ONE_YEAR->value,
        'notice_period' => NoticePeriodEnum::DEFAULT->value,
        'notice_date' => $noticeDate
    ],);

    $formData = ['contract_id' => $contract->id];

    $this->deleteFromTenant('api.sites.contracts.delete', $this->site, $formData);

    $preference = $this->manager->notification_preferences()->where('notification_type', 'notice_date')->first();

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
});

it('deletes notice_date notification for the maintenance manager when a contract is detached from a building', function () {
    $endDate = ContractDurationEnum::from(ContractDurationEnum::ONE_YEAR->value)->addTo(Carbon::now());
    $noticeDate = NoticePeriodEnum::from(NoticePeriodEnum::DEFAULT->value)->subFrom($endDate)->toDateString();

    $contract = Contract::factory()->forLocation($this->building)->create([
        ...$this->basicContractData,
        'contract_duration' => ContractDurationEnum::ONE_YEAR->value,
        'notice_period' => NoticePeriodEnum::DEFAULT->value,
        'notice_date' => $noticeDate
    ],);

    $formData = ['contract_id' => $contract->id];

    $this->deleteFromTenant('api.buildings.contracts.delete', $this->building, $formData);

    $preference = $this->manager->notification_preferences()->where('notification_type', 'notice_date')->first();

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
});

it('deletes notice_date notification for the maintenance manager when a contract is detached from a floor', function () {
    $endDate = ContractDurationEnum::from(ContractDurationEnum::ONE_YEAR->value)->addTo(Carbon::now());
    $noticeDate = NoticePeriodEnum::from(NoticePeriodEnum::DEFAULT->value)->subFrom($endDate)->toDateString();

    $contract = Contract::factory()->forLocation($this->floor)->create([
        ...$this->basicContractData,
        'contract_duration' => ContractDurationEnum::ONE_YEAR->value,
        'notice_period' => NoticePeriodEnum::DEFAULT->value,
        'notice_date' => $noticeDate
    ],);

    $formData = ['contract_id' => $contract->id];

    $this->deleteFromTenant('api.floors.contracts.delete', $this->floor, $formData);

    $preference = $this->manager->notification_preferences()->where('notification_type', 'notice_date')->first();

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
});

it('deletes notice_date notification for the maintenance manager when a contract is detached from a room', function () {
    $endDate = ContractDurationEnum::from(ContractDurationEnum::ONE_YEAR->value)->addTo(Carbon::now());
    $noticeDate = NoticePeriodEnum::from(NoticePeriodEnum::DEFAULT->value)->subFrom($endDate)->toDateString();

    $contract = Contract::factory()->forLocation($this->room)->create([
        ...$this->basicContractData,
        'contract_duration' => ContractDurationEnum::ONE_YEAR->value,
        'notice_period' => NoticePeriodEnum::DEFAULT->value,
        'notice_date' => $noticeDate
    ],);

    $formData = ['contract_id' => $contract->id];

    $this->deleteFromTenant('api.rooms.contracts.delete', $this->room, $formData);

    $preference = $this->manager->notification_preferences()->where('notification_type', 'notice_date')->first();

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
});

it('deletes notice_date notification for the maintenance manager when a contract is detached from an asset', function () {
    $endDate = ContractDurationEnum::from(ContractDurationEnum::ONE_YEAR->value)->addTo(Carbon::now());
    $noticeDate = NoticePeriodEnum::from(NoticePeriodEnum::DEFAULT->value)->subFrom($endDate)->toDateString();

    $contract = Contract::factory()->forLocation($this->asset)->create([
        ...$this->basicContractData,
        'contract_duration' => ContractDurationEnum::ONE_YEAR->value,
        'notice_period' => NoticePeriodEnum::DEFAULT->value,
        'notice_date' => $noticeDate
    ],);

    $formData = ['contract_id' => $contract->id];

    $this->deleteFromTenant('api.assets.contracts.delete', $this->asset, $formData);

    $preference = $this->manager->notification_preferences()->where('notification_type', 'notice_date')->first();

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
});


it('does not delete notice_date notification for the maintenance manager when a contract is detached from a site but user is admin', function () {
    $endDate = ContractDurationEnum::from(ContractDurationEnum::ONE_YEAR->value)->addTo(Carbon::now());
    $noticeDate = NoticePeriodEnum::from(NoticePeriodEnum::DEFAULT->value)->subFrom($endDate)->toDateString();

    $contract = Contract::factory()->forLocation($this->site)->create([
        ...$this->basicContractData,
        'contract_duration' => ContractDurationEnum::ONE_YEAR->value,
        'notice_period' => NoticePeriodEnum::DEFAULT->value,
        'notice_date' => $noticeDate
    ],);

    $this->manager->syncRoles('Admin');

    $formData = ['contract_id' => $contract->id];

    $this->deleteFromTenant('api.sites.contracts.delete', $this->site, $formData);

    $preference = $this->manager->notification_preferences()->where('notification_type', 'notice_date')->first();

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
});

it('does not delete notice_date notification for the maintenance manager when a contract is detached from a building but user is admin', function () {
    $endDate = ContractDurationEnum::from(ContractDurationEnum::ONE_YEAR->value)->addTo(Carbon::now());
    $noticeDate = NoticePeriodEnum::from(NoticePeriodEnum::DEFAULT->value)->subFrom($endDate)->toDateString();

    $contract = Contract::factory()->forLocation($this->building)->create([
        ...$this->basicContractData,
        'contract_duration' => ContractDurationEnum::ONE_YEAR->value,
        'notice_period' => NoticePeriodEnum::DEFAULT->value,
        'notice_date' => $noticeDate
    ],);

    $this->manager->syncRoles('Admin');

    $formData = ['contract_id' => $contract->id];

    $this->deleteFromTenant('api.buildings.contracts.delete', $this->building, $formData);

    $preference = $this->manager->notification_preferences()->where('notification_type', 'notice_date')->first();

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
});

it('does not delete notice_date notification for the maintenance manager when a contract is detached from a floor but user is admin', function () {
    $endDate = ContractDurationEnum::from(ContractDurationEnum::ONE_YEAR->value)->addTo(Carbon::now());
    $noticeDate = NoticePeriodEnum::from(NoticePeriodEnum::DEFAULT->value)->subFrom($endDate)->toDateString();

    $contract = Contract::factory()->forLocation($this->floor)->create([
        ...$this->basicContractData,
        'contract_duration' => ContractDurationEnum::ONE_YEAR->value,
        'notice_period' => NoticePeriodEnum::DEFAULT->value,
        'notice_date' => $noticeDate
    ],);

    $this->manager->syncRoles('Admin');

    $formData = ['contract_id' => $contract->id];

    $this->deleteFromTenant('api.floors.contracts.delete', $this->floor, $formData);

    $preference = $this->manager->notification_preferences()->where('notification_type', 'notice_date')->first();

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
});

it('does not delete notice_date notification for the maintenance manager when a contract is detached from a room but user is admin', function () {
    $endDate = ContractDurationEnum::from(ContractDurationEnum::ONE_YEAR->value)->addTo(Carbon::now());
    $noticeDate = NoticePeriodEnum::from(NoticePeriodEnum::DEFAULT->value)->subFrom($endDate)->toDateString();

    $contract = Contract::factory()->forLocation($this->room)->create([
        ...$this->basicContractData,
        'contract_duration' => ContractDurationEnum::ONE_YEAR->value,
        'notice_period' => NoticePeriodEnum::DEFAULT->value,
        'notice_date' => $noticeDate
    ],);

    $this->manager->syncRoles('Admin');

    $formData = ['contract_id' => $contract->id];

    $this->deleteFromTenant('api.rooms.contracts.delete', $this->room, $formData);

    $preference = $this->manager->notification_preferences()->where('notification_type', 'notice_date')->first();

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
});

it('does not delete notice_date notification for the maintenance manager when a contract is detached from an asset but user is admin', function () {
    $endDate = ContractDurationEnum::from(ContractDurationEnum::ONE_YEAR->value)->addTo(Carbon::now());
    $noticeDate = NoticePeriodEnum::from(NoticePeriodEnum::DEFAULT->value)->subFrom($endDate)->toDateString();

    $contract = Contract::factory()->forLocation($this->asset)->create([
        ...$this->basicContractData,
        'contract_duration' => ContractDurationEnum::ONE_YEAR->value,
        'notice_period' => NoticePeriodEnum::DEFAULT->value,
        'notice_date' => $noticeDate
    ],);

    $this->manager->syncRoles('Admin');

    $formData = ['contract_id' => $contract->id];

    $this->deleteFromTenant('api.assets.contracts.delete', $this->asset, $formData);

    $preference = $this->manager->notification_preferences()->where('notification_type', 'notice_date')->first();

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
});


it('deletes notice_date notification for the maintenance manager when he is removed from a site', function () {
    $endDate = ContractDurationEnum::from(ContractDurationEnum::ONE_YEAR->value)->addTo(Carbon::now());
    $noticeDate = NoticePeriodEnum::from(NoticePeriodEnum::DEFAULT->value)->subFrom($endDate)->toDateString();
    $preference = $this->manager->notification_preferences()->where('notification_type', 'notice_date')->first();

    $contract = Contract::factory()->forLocation($this->site)->create([
        ...$this->basicContractData,
        'contract_duration' => ContractDurationEnum::ONE_YEAR->value,
        'notice_period' => NoticePeriodEnum::DEFAULT->value,
        'notice_date' => $noticeDate
    ],);

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

    $formData = [
        'name' => 'New site',
        'description' => 'Description new site',
        'locationType' => $this->siteType->id,
        'maintenance_manager_id' => null
    ];

    $this->patchToTenant('api.sites.update', $formData, $this->site);

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
});

it('deletes notice_date notification for the maintenance manager when he is removed from a building', function () {
    $endDate = ContractDurationEnum::from(ContractDurationEnum::ONE_YEAR->value)->addTo(Carbon::now());
    $noticeDate = NoticePeriodEnum::from(NoticePeriodEnum::DEFAULT->value)->subFrom($endDate)->toDateString();
    $preference = $this->manager->notification_preferences()->where('notification_type', 'notice_date')->first();

    $contract = Contract::factory()->forLocation($this->building)->create([
        ...$this->basicContractData,
        'contract_duration' => ContractDurationEnum::ONE_YEAR->value,
        'notice_period' => NoticePeriodEnum::DEFAULT->value,
        'notice_date' => $noticeDate
    ],);

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

    $formData = [
        'name' => 'New building',
        'description' => 'Description new building',
        'levelType' => $this->site->id,
        'locationType' => $this->buildingType->id,
        'maintenance_manager_id' => null
    ];

    $this->patchToTenant('api.buildings.update', $formData, $this->building);

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
});

it('deletes notice_date notification for the maintenance manager when he is removed from a floor', function () {
    $endDate = ContractDurationEnum::from(ContractDurationEnum::ONE_YEAR->value)->addTo(Carbon::now());
    $noticeDate = NoticePeriodEnum::from(NoticePeriodEnum::DEFAULT->value)->subFrom($endDate)->toDateString();
    $preference = $this->manager->notification_preferences()->where('notification_type', 'notice_date')->first();

    $contract = Contract::factory()->forLocation($this->floor)->create([
        ...$this->basicContractData,
        'contract_duration' => ContractDurationEnum::ONE_YEAR->value,
        'notice_period' => NoticePeriodEnum::DEFAULT->value,
        'notice_date' => $noticeDate
    ],);

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

    $formData = [
        'name' => 'New floor',
        'description' => 'Description new floor',
        'levelType' => $this->building->id,
        'locationType' => $this->floorType->id,
        'maintenance_manager_id' => null
    ];

    $this->patchToTenant('api.floors.update', $formData, $this->floor);

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
});

it('deletes notice_date notification for the maintenance manager when he is removed from a room', function () {
    $endDate = ContractDurationEnum::from(ContractDurationEnum::ONE_YEAR->value)->addTo(Carbon::now());
    $noticeDate = NoticePeriodEnum::from(NoticePeriodEnum::DEFAULT->value)->subFrom($endDate)->toDateString();
    $preference = $this->manager->notification_preferences()->where('notification_type', 'notice_date')->first();

    $contract = Contract::factory()->forLocation($this->room)->create([
        ...$this->basicContractData,
        'contract_duration' => ContractDurationEnum::ONE_YEAR->value,
        'notice_period' => NoticePeriodEnum::DEFAULT->value,
        'notice_date' => $noticeDate
    ],);

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

    $formData = [
        'name' => 'New room',
        'description' => 'Description new room',
        'levelType' => $this->floor->id,
        'locationType' => $this->roomType->id,
        'maintenance_manager_id' => null
    ];

    $this->patchToTenant('api.rooms.update', $formData, $this->room);

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
});

it('deletes notice_date notification for the maintenance manager when he is removed from an asset', function () {
    $endDate = ContractDurationEnum::from(ContractDurationEnum::ONE_YEAR->value)->addTo(Carbon::now());
    $noticeDate = NoticePeriodEnum::from(NoticePeriodEnum::DEFAULT->value)->subFrom($endDate)->toDateString();
    $preference = $this->manager->notification_preferences()->where('notification_type', 'notice_date')->first();

    $contract = Contract::factory()->forLocation($this->asset)->create([
        ...$this->basicContractData,
        'contract_duration' => ContractDurationEnum::ONE_YEAR->value,
        'notice_period' => NoticePeriodEnum::DEFAULT->value,
        'notice_date' => $noticeDate
    ],);

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

    $formData = [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'locationId' => $this->room->id,
        'locationType' => 'room',
        'locationReference' => $this->room->reference_code,
        'categoryId' => $this->assetType->id,
        'maintenance_manager_id' => null
    ];

    $this->patchToTenant('api.assets.update', $formData, $this->asset);

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
});
