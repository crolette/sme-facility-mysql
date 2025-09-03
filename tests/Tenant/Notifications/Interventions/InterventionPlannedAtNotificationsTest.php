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
use App\Models\Tenants\Intervention;
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

    LocationType::factory()->create(['level' => 'site']);
    LocationType::factory()->create(['level' => 'building']);
    LocationType::factory()->create(['level' => 'floor']);
    LocationType::factory()->create(['level' => 'room']);
    CategoryType::factory()->create(['category' => 'provider']);
    CategoryType::factory()->create(['category' => 'asset']);

    $this->interventionType = CategoryType::factory()->create(['category' => 'intervention']);
    $this->interventionActionType = CategoryType::factory()->create(['category' => 'action']);

    $this->site = Site::factory()->create();
    Building::factory()->create();
    Floor::factory()->create();
    $this->provider = Provider::factory()->create();

    $this->room = Room::factory()
        ->for(LocationType::where('level', 'room')->first())
        ->for(Floor::first())
        ->create();

    $this->asset = Asset::factory()->forLocation($this->room)->create();
});

it('creates a planned_at notification user preference when user (admin) is created', function () {

    $formData = [
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'janedoe@facilitywebxp.be',
        'can_login' => true,
        'role' => 'Admin',
    ];

    $response = $this->postToTenant('api.users.store', $formData);
    $response->assertStatus(200);
    $user = User::where('email', 'janedoe@facilitywebxp.be')->first();

    assertDatabaseHas(
        'user_notification_preferences',
        [
            'user_id' => $user->id,
            'asset_type' => 'intervention',
            'notification_type' => 'planned_at',
        ]
    );
});

it('creates a planned_at notification user preference when user (maintenance manager) is created', function () {

    $formData = [
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'janedoe@facilitywebxp.be',
        'can_login' => true,
        'role' => 'Maintenance Manager',
    ];

    $response = $this->postToTenant('api.users.store', $formData);
    $response->assertStatus(200);
    $user = User::where('email', 'janedoe@facilitywebxp.be')->first();

    assertDatabaseHas(
        'user_notification_preferences',
        [
            'user_id' => $user->id,
            'asset_type' => 'intervention',
            'notification_type' => 'planned_at',
        ]
    );
});

it('creates the planned_at notification for a new created intervention for an ASSET', function () {

    $formData = [
        'intervention_type_id' => $this->interventionType->id,
        'priority' => 'medium',
        'status' => 'planned',
        'planned_at' => Carbon::now()->addMonth(1),
        'description' => fake()->paragraph(),
        'repair_delay' => Carbon::now()->addMonth(1),
        'locationId' => $this->asset->reference_code,
        'locationType' => 'asset'
    ];

    $response = $this->postToTenant('api.interventions.store', $formData);
    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
        ]);

    assertDatabaseCount('scheduled_notifications', 1);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->admin->id,
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'planned_at',
            'scheduled_at' => Carbon::now()->addMonth(1)->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Intervention',
            'notifiable_id' => 1,
        ]
    );
});

it('does not create a planned_at notification when planned_at is not defined for intervention', function () {
    $formData = [
        'intervention_type_id' => $this->interventionType->id,
        'priority' => 'medium',
        'status' => 'planned',
        'description' => fake()->paragraph(),
        'repair_delay' => Carbon::now()->addMonth(1),
        'locationId' => $this->asset->reference_code,
        'locationType' => 'asset'
    ];

    $response = $this->postToTenant('api.interventions.store', $formData);
    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
        ]);

    assertDatabaseCount('scheduled_notifications', 0);

    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'user_id' => $this->admin->id,
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'planned_at',
            'notifiable_type' => 'App\Models\Tenants\Intervention',
            'notifiable_id' => 1,
        ]
    );
});

it(
    'updates notification when planned_at changes for an intervention',
    function () {

        Intervention::factory()->forLocation($this->asset)->create();

        assertDatabaseHas(
            'scheduled_notifications',
            [
                'user_id' => $this->admin->id,
                'recipient_name' => $this->admin->fullName,
                'recipient_email' => $this->admin->email,
                'notification_type' => 'planned_at',
                'scheduled_at' => Carbon::now()->addMonth()->subDays(7)->toDateString(),
                'notifiable_type' => 'App\Models\Tenants\Intervention',
                'notifiable_id' => 1,
            ]
        );

        $formData = [
            'intervention_type_id' => $this->interventionType->id,
            'priority' => 'medium',
            'status' => 'planned',
            'planned_at' => Carbon::now()->addWeeks(2),
            'description' => fake()->paragraph(),
            'repair_delay' => Carbon::now()->addMonth(1),
            'locationId' => $this->asset->reference_code,
            'locationType' => 'asset'
        ];

        $intervention = Intervention::first();

        $response = $this->patchToTenant('api.interventions.update', $formData, $intervention->id);
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);

        assertDatabaseCount('scheduled_notifications', 1);

        assertDatabaseHas(
            'scheduled_notifications',
            [
                'user_id' => $this->admin->id,
                'recipient_name' => $this->admin->fullName,
                'recipient_email' => $this->admin->email,
                'notification_type' => 'planned_at',
                'scheduled_at' => Carbon::now()->addWeeks(2)->subDays(7)->toDateString(),
                'notifiable_type' => 'App\Models\Tenants\Intervention',
                'notifiable_id' => 1,
            ]
        );
    }
);

it('deletes notification when intervention is deleted', function () {

    Intervention::factory()->forLocation($this->asset)->create();
    assertDatabaseCount('scheduled_notifications', 1);

    $intervention = Intervention::first();

    $response = $this->deleteFromTenant('api.interventions.destroy', $intervention->id);
    $response->assertStatus(200);
    assertDatabaseCount('scheduled_notifications', 0);
});

it('deletes notifcation when user_preference planned_at is disabled', function () {
    Intervention::factory()->forLocation($this->asset)->create();

    assertDatabaseCount('scheduled_notifications', 1);

    $preference = $this->admin->notification_preferences()->where('notification_type', 'planned_at')->first();

    $formData = [
        'asset_type' => 'intervention',
        'notification_type' => 'planned_at',
        'notification_delay_days' => $preference->notification_delay_days,
        'enabled' => false,
    ];

    $response = $this->patchToTenant('api.notifications.update', $formData, $preference->id);
    $response->assertStatus(200);

    assertDatabaseCount('scheduled_notifications', 0);
    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'notification_type' => 'planned_at'
        ]
    );
});

it('creates notification when user_preference planned_at is enabled', function () {

    Intervention::factory()->forLocation($this->asset)->create();

    assertDatabaseCount('scheduled_notifications', 1);

    $preference = $this->admin->notification_preferences()->where('notification_type', 'planned_at')->first();

    $formData = [
        'asset_type' => 'intervention',
        'notification_type' => 'planned_at',
        'notification_delay_days' => $preference->notification_delay_days,
        'enabled' => false,
    ];

    $response = $this->patchToTenant('api.notifications.update', $formData, $preference->id);
    $response->assertStatus(200);

    assertDatabaseCount('scheduled_notifications', 0);

    $formData = [
        'asset_type' => 'intervention',
        'notification_type' => 'planned_at',
        'notification_delay_days' => $preference->notification_delay_days,
        'enabled' => true,
    ];

    $response = $this->patchToTenant('api.notifications.update', $formData, $preference->id);
    $response->assertStatus(200);

    assertDatabaseCount('scheduled_notifications', 1);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->admin->id,
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'planned_at',
            'scheduled_at' => Carbon::now()->addMonth(1)->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Intervention',
            'notifiable_id' => 1,
        ]
    );
});

it('updates notification when user_preference notification_delay_days for planned_at changes', function () {


    $formData = [
        'intervention_type_id' => $this->interventionType->id,
        'priority' => 'medium',
        'status' => 'planned',
        'planned_at' => Carbon::now()->addMonth(),
        'description' => fake()->paragraph(),
        'repair_delay' => Carbon::now()->addMonth(),
        'locationId' => $this->asset->reference_code,
        'locationType' => 'asset'
    ];

    $response = $this->postToTenant('api.interventions.store', $formData);
    $response->assertStatus(200);

    assertDatabaseCount('scheduled_notifications', 1);
    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->admin->id,
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'planned_at',
            'scheduled_at' => Carbon::now()->addMonth()->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Intervention',
            'notifiable_id' => 1,
        ]
    );



    $preference = $this->admin->notification_preferences()->where('notification_type', 'planned_at')->first();

    $formData = [
        'asset_type' => 'intervention',
        'notification_type' => 'planned_at',
        'notification_delay_days' => 3,
        'enabled' => true,
    ];

    $response = $this->patchToTenant('api.notifications.update', $formData, $preference->id);
    $response->assertStatus(200);

    assertDatabaseCount('scheduled_notifications', 1);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->admin->id,
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'planned_at',
            'scheduled_at' => Carbon::now()->addMonth()->subDays(3)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Intervention',
            'notifiable_id' => 1,
        ]
    );
});

// it('adds notification when maintenance_manager is added to an ASSET', function() {

// });

// it('removes notification when maintenance_manager is removed from an ASSET', function() {

// });