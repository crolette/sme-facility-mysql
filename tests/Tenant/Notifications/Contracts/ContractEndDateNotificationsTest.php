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
});

// it('creates the end_date notification for the admin for a new created contract for an asset', function ($duration) {

//     $preference = $this->admin->notification_preferences()->where('notification_type', 'end_date')->first();

//     $formData = [
//         ...$this->basicContractData,
//         'contract_duration' => $duration
//     ];

//     $this->postToTenant('api.contracts.store', $formData);

//     assertDatabaseCount('scheduled_notifications', 1);

//     $contract = Contract::first();

//     assertDatabaseHas(
//         'scheduled_notifications',
//         [
//             'user_id' => $this->admin->id,
//             'recipient_name' => $this->admin->fullName,
//             'recipient_email' => $this->admin->email,
//             'notification_type' => 'end_date',
//             'scheduled_at' => $contract->end_date->subDays($preference->notification_delay_days)->toDateString(),
//             'notifiable_type' => 'App\Models\Tenants\Contract',
//             'notifiable_id' => 1,
//         ]
//     );
// })->with(array_column(ContractDurationEnum::cases(), 'value'));

it('creates the end_date notification for the maintenance manager for a new created contract', function ($duration) {

    $this->asset->refresh();
    $this->asset->maintainable->update(['maintenance_manager_id', $this->manager->id]);

    $preference = $this->manager->notification_preferences()->where('notification_type', 'end_date')->first();

    $formData = [
        ...$this->basicContractData,
        'contract_duration' => $duration
    ];

    $this->postToTenant('api.contracts.store', $formData);

    assertDatabaseCount('scheduled_notifications', 1);

    $contract = Contract::first();

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'notice_date',
            'scheduled_at' => $contract->end_date->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Contract',
            'notifiable_id' => 1,
        ]
    );
})->with(array_column(ContractDurationEnum::cases(), 'value'));

// it('update notifications when notification_delay_days preference for end_date of user changes', function () {

//     Contract::factory()->forLocation($this->asset)->create();
//     Contract::factory()->forLocation($this->asset)->create();

//     assertDatabaseCount('scheduled_notifications', 4);

//     assertDatabaseHas(
//         'scheduled_notifications',
//         [
//             'user_id' => $this->admin->id,
//             'recipient_name' => $this->admin->fullName,
//             'recipient_email' => $this->admin->email,
//             'notification_type' => 'end_date',
//             'scheduled_at' => Carbon::now()->addMonth(1)->subDays(7)->toDateString(),
//             'notifiable_type' => 'App\Models\Tenants\Contract',
//             'notifiable_id' => 1,
//         ]
//     );

//     $preference = $this->admin->notification_preferences()->where('notification_type', 'end_date')->first();

//     $formData = [
//         'asset_type' => 'contract',
//         'notification_type' => 'end_date',
//         'notification_delay_days' => 1,
//         'enabled' => true,
//     ];

//     $response = $this->patchToTenant('api.notifications.update', $formData, $preference->id);
//     $response->assertStatus(200);

//     assertDatabaseHas(
//         'scheduled_notifications',
//         [
//             'user_id' => $this->admin->id,
//             'recipient_name' => $this->admin->fullName,
//             'recipient_email' => $this->admin->email,
//             'notification_type' => 'end_date',
//             'scheduled_at' => Carbon::now()->addMonth(1)->subDays(1)->toDateString(),
//             'notifiable_type' => 'App\Models\Tenants\Contract',
//             'notifiable_id' => 1,
//         ]
//     );
//     assertDatabaseHas(
//         'scheduled_notifications',
//         [
//             'user_id' => $this->admin->id,
//             'recipient_name' => $this->admin->fullName,
//             'recipient_email' => $this->admin->email,
//             'notification_type' => 'end_date',
//             'scheduled_at' => Carbon::now()->addMonth(1)->subDays(1)->toDateString(),
//             'notifiable_type' => 'App\Models\Tenants\Contract',
//             'notifiable_id' => 2,
//         ]
//     );
// });

// it('deletes notifications when notification preference end_date of user is disabled', function () {

//     Contract::factory()->forLocation($this->asset)->create();

//     assertDatabaseCount('scheduled_notifications', 2);

//     assertDatabaseHas(
//         'scheduled_notifications',
//         [
//             'user_id' => $this->admin->id,
//             'recipient_name' => $this->user->fullName,
//             'recipient_email' => $this->user->email,
//             'notification_type' => 'end_date',
//             'scheduled_at' => Carbon::now()->addMonth(1)->subDays(7)->toDateString(),
//             'notifiable_type' => 'App\Models\Tenants\Contract',
//             'notifiable_id' => 1,
//         ]
//     );

//     $preference = $this->user->notification_preferences()->where('notification_type', 'end_date')->first();


//     $formData = [
//         'asset_type' => 'contract',
//         'notification_type' => 'end_date',
//         'notification_delay_days' => $preference->notification_delay_days,
//         'enabled' => false,
//     ];

//     $response = $this->patchToTenant('api.notifications.update', $formData, $preference->id);
//     $response->assertStatus(200);
//     assertDatabaseCount('scheduled_notifications', 1);

//     assertDatabaseMissing(
//         'scheduled_notifications',
//         [
//             'user_id' => $this->user->id,
//             'recipient_name' => $this->user->fullName,
//             'recipient_email' => $this->user->email,
//             'notification_type' => 'end_date',
//             'scheduled_at' => Carbon::now()->addMonth(1)->subDays(7)->toDateString(),
//             'notifiable_type' => 'App\Models\Tenants\Contract',
//             'notifiable_id' => 1,
//         ]
//     );
// });

// it('creates notifications when notification preference end_date of user is enabled', function () {

//     Contract::factory()->forLocation($this->asset)->create();

//     $preference = $this->user->notification_preferences()->where('notification_type', 'end_date')->first();

//     $formData = [
//         'asset_type' => 'contract',
//         'notification_type' => 'end_date',
//         'notification_delay_days' => $preference->notification_delay_days,
//         'enabled' => false,
//     ];

//     $response = $this->patchToTenant('api.notifications.update', $formData, $preference->id);
//     $response->assertStatus(200);

//     $formData = [
//         'asset_type' => 'contract',
//         'notification_type' => 'end_date',
//         'notification_delay_days' => $preference->notification_delay_days,
//         'enabled' => true,
//     ];

//     $response = $this->patchToTenant('api.notifications.update', $formData, $preference->id);
//     $response->assertStatus(200);

//     assertDatabaseCount('scheduled_notifications', 2);

//     assertDatabaseHas(
//         'scheduled_notifications',
//         [
//             'user_id' => $this->user->id,
//             'recipient_name' => $this->user->fullName,
//             'recipient_email' => $this->user->email,
//             'notification_type' => 'end_date',
//             'scheduled_at' => Carbon::now()->addMonth(1)->subDays(7)->toDateString(),
//             'notifiable_type' => 'App\Models\Tenants\Contract',
//             'notifiable_id' => 1,
//         ]
//     );
// });

// it('updates notification for a specific contract when end_date changes', function () {

//     $contract =  Contract::factory()->forLocation($this->asset)->create();

//     $updatedContract = [
//         ...$this->basicContractData,
//         'contract_duration' => ContractDurationEnum::TWO_YEARS->value,
//     ];

//     $response = $this->patchToTenant('api.contracts.update', $updatedContract, $contract->id);
//     $response->assertSessionHasNoErrors();

//     assertDatabaseCount('scheduled_notifications', 2);

//     $contract = Contract::find(1);

//     assertDatabaseHas(
//         'scheduled_notifications',
//         [
//             'user_id' => $this->user->id,
//             'recipient_name' => $this->user->fullName,
//             'recipient_email' => $this->user->email,
//             'notification_type' => 'end_date',
//             'scheduled_at' => Carbon::now()->addYears(2)->subDays(7)->toDateString(),
//             'notifiable_type' => 'App\Models\Tenants\Contract',
//             'notifiable_id' => 1,
//         ]
//     );
// });
