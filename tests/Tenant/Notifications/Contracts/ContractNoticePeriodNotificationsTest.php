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

    $this->user = User::factory()->withRole('Admin')->create();
    $this->actingAs($this->user, 'tenant');

    $this->manager = User::factory()->withRole('Maintenance Manager')->create();

    $this->site = Site::factory()->create();
    Building::factory()->create();
    Floor::factory()->create();
    $this->provider = Provider::factory()->create();

    $this->room = Room::factory()
        ->for(LocationType::where('level', 'room')->first())
        ->for(Floor::first())
        ->create();

    $this->asset = Asset::factory()->forLocation($this->room)->create();

    $this->basicContractData = [
        'provider_id' => $this->provider->id,
        'name' => 'Contrat de bail',
        'type' => 'Bail',
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

it('creates the notice_date notification for a new created contract', function () {

    Contract::factory()->forLocation($this->asset)->create();

    // 2 notifications because of the end_date
    assertDatabaseCount('scheduled_notifications', 2);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->user->id,
            'recipient_name' => $this->user->fullName,
            'recipient_email' => $this->user->email,
            'notification_type' => 'notice_date',
            'scheduled_at' => Carbon::now()->addMonth(1)->subDays(21)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Contract',
            'notifiable_id' => 1,
        ]
    );
});


it('update notifications when notification_delay_days preference for notice_date of user changes', function () {

    Contract::factory()->forLocation($this->asset)->create();
    Contract::factory()->forLocation($this->asset)->create();

    assertDatabaseCount('scheduled_notifications', 4);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->user->id,
            'recipient_name' => $this->user->fullName,
            'recipient_email' => $this->user->email,
            'notification_type' => 'notice_date',
            'scheduled_at' => Carbon::now()->addMonth(1)->subDays(21)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Contract',
            'notifiable_id' => 1,
        ]
    );

    $preference = $this->user->notification_preferences()->where('notification_type', 'notice_date')->first();

    $formData = [
        'asset_type' => 'contract',
        'notification_type' => 'notice_date',
        'notification_delay_days' => 1,
        'enabled' => true,
    ];

    $response = $this->patchToTenant('api.notifications.update', $formData, $preference->id);
    $response->assertStatus(200);

    assertDatabaseCount('scheduled_notifications', 4);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->user->id,
            'recipient_name' => $this->user->fullName,
            'recipient_email' => $this->user->email,
            'notification_type' => 'notice_date',
            'scheduled_at' => Carbon::now()->addMonth(1)->subDays(15)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Contract',
            'notifiable_id' => 1,
        ]
    );

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->user->id,
            'recipient_name' => $this->user->fullName,
            'recipient_email' => $this->user->email,
            'notification_type' => 'notice_date',
            'scheduled_at' => Carbon::now()->addMonth(1)->subDays(15)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Contract',
            'notifiable_id' => 2,
        ]
    );
});

it('deletes notifications when notification preference notice_date of user is disabled', function () {

    Contract::factory()->forLocation($this->asset)->create();

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->user->id,
            'recipient_name' => $this->user->fullName,
            'recipient_email' => $this->user->email,
            'notification_type' => 'notice_date',
            'scheduled_at' => Carbon::now()->addMonth(1)->subDays(21)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Contract',
            'notifiable_id' => 1,
        ]
    );

    $preference = $this->user->notification_preferences()->where('notification_type', 'notice_date')->first();

    $formData = [
        'asset_type' => 'contract',
        'notification_type' => 'notice_date',
        'notification_delay_days' => $preference->notification_delay_days,
        'enabled' => false,
    ];

    $response = $this->patchToTenant('api.notifications.update', $formData, $preference->id);
    $response->assertStatus(200);

    assertDatabaseCount('scheduled_notifications', 1);

    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'user_id' => $this->user->id,
            'recipient_name' => $this->user->fullName,
            'recipient_email' => $this->user->email,
            'notification_type' => 'notice_date',
            'scheduled_at' => Carbon::now()->addMonth(1)->subDays(21)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Contract',
            'notifiable_id' => 1,
        ]
    );
});


it('creates notifications when notification preference notice_date of user is enabled', function () {

    Contract::factory()->forLocation($this->asset)->create();

    $preference = $this->user->notification_preferences()->where('notification_type', 'notice_date')->first();

    $formData = [
        'asset_type' => 'contract',
        'notification_type' => 'notice_date',
        'notification_delay_days' => $preference->notification_delay_days,
        'enabled' => false,
    ];

    $response = $this->patchToTenant('api.notifications.update', $formData, $preference->id);
    $response->assertStatus(200);

    assertDatabaseCount('scheduled_notifications', 1);
    $formData = [
        'asset_type' => 'contract',
        'notification_type' => 'notice_date',
        'notification_delay_days' => $preference->notification_delay_days,
        'enabled' => true,
    ];

    $response = $this->patchToTenant('api.notifications.update', $formData, $preference->id);
    $response->assertStatus(200);

    assertDatabaseCount('scheduled_notifications', 2);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->user->id,
            'recipient_name' => $this->user->fullName,
            'recipient_email' => $this->user->email,
            'notification_type' => 'notice_date',
            'scheduled_at' => Carbon::now()->addMonth(1)->subDays(21)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Contract',
            'notifiable_id' => 1,
        ]
    );
});

it('updates notification for a specific contract when notice_period changes', function () {

    $contract =  Contract::factory()->forLocation($this->asset)->create();

    $updatedContract = [
        ...$this->basicContractData,
        'notice_period' => NoticePeriodEnum::DEFAULT->value,
    ];

    $response = $this->patchToTenant('api.contracts.update', $updatedContract, $contract->id);
    $response->assertSessionHasNoErrors();

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->user->id,
            'recipient_name' => $this->user->fullName,
            'recipient_email' => $this->user->email,
            'notification_type' => 'end_date',
            'scheduled_at' => Carbon::now()->addMonth()->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Contract',
            'notifiable_id' => 1,
        ]
    );

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->user->id,
            'recipient_name' => $this->user->fullName,
            'recipient_email' => $this->user->email,
            'notification_type' => 'notice_date',
            'scheduled_at' => Carbon::now()->addMonth()->subDays(14)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Contract',
            'notifiable_id' => 1,
        ]
    );
});
