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
use App\Enums\InterventionStatus;
use Illuminate\Http\UploadedFile;
use App\Enums\ContractDurationEnum;
use App\Enums\MaintenanceFrequency;
use App\Models\Central\CategoryType;
use App\Models\Tenants\Intervention;
use App\Enums\ContractRenewalTypesEnum;

use function PHPUnit\Framework\assertCount;
use function Pest\Laravel\assertDatabaseHas;
use function PHPUnit\Framework\assertEquals;
use App\Models\Tenants\ScheduledNotification;
use function PHPUnit\Framework\assertNotNull;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseMissing;

beforeEach(function () {

    $this->admin = User::factory()->withRole('Admin')->create();
    $this->actingAs($this->admin, 'tenant');

    $this->manager = User::factory()->withRole('Maintenance Manager')->create();

    LocationType::factory()->create(['level' => 'room']);
    CategoryType::factory()->create(['category' => 'provider']);
    $this->assetCategory = CategoryType::factory()->create(['category' => 'asset']);

    $this->interventionType = CategoryType::factory()->create(['category' => 'intervention']);
    $this->interventionActionType = CategoryType::factory()->create(['category' => 'action']);

    $this->site = Site::factory()->withMaintainableData()->create();
    Building::factory()->withMaintainableData()->create();
    Floor::factory()->withMaintainableData()->create();
    $this->provider = Provider::factory()->create();

    $this->room = Room::factory()->withMaintainableData()->create();

    $this->asset = Asset::factory()->withMaintainableData()->forLocation($this->room)->create();
});

it('creates a user preference "planned_at" notification when user (admin) is created', function () {

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

it('creates a user preference "planned_at" notification when user (maintenance manager) is created', function () {

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

it('creates a planned_at notification for a new created intervention for an ASSET if the status is not `draft/completed/cancelled`', function ($status) {

    $formData = [
        'intervention_type_id' => $this->interventionType->id,
        'priority' => 'medium',
        'status' => $status,
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
})->with(array_values(array_diff(array_column(InterventionStatus::cases(), 'value'), ['draft', 'completed', 'cancelled'])));

it('does not create a planned_at notification for a new created intervention for an ASSET if the status is not `draft/completed/cancelled` when planned_at is today', function ($status) {

    $formData = [
        'intervention_type_id' => $this->interventionType->id,
        'priority' => 'medium',
        'status' => $status,
        'planned_at' => Carbon::now(),
        'description' => fake()->paragraph(),
        'locationId' => $this->asset->reference_code,
        'locationType' => 'asset'
    ];

    $response = $this->postToTenant('api.interventions.store', $formData);
    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
        ]);

    assertDatabaseCount('scheduled_notifications', 0);
})->with(array_values(array_diff(array_column(InterventionStatus::cases(), 'value'), ['draft', 'completed', 'cancelled'])));

it('creates a planned_at notification for a new created intervention for an ASSET if the status is not `draft/completed/cancelled` when planned_at is > today', function ($status) {

    $formData = [
        'intervention_type_id' => $this->interventionType->id,
        'priority' => 'medium',
        'status' => $status,
        'planned_at' => Carbon::tomorrow(),
        'description' => fake()->paragraph(),
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
            'scheduled_at' => Carbon::tomorrow()->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Intervention',
            'notifiable_id' => 1,
        ]
    );
})->with(array_values(array_diff(array_column(InterventionStatus::cases(), 'value'), ['draft', 'completed', 'cancelled'])));

it('does not create a planned_at notification for a new created intervention for an ASSET if status is not `planned/in progress/waiting_parts`', function ($status) {

    $formData = [
        'intervention_type_id' => $this->interventionType->id,
        'priority' => 'medium',
        'status' => $status,
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

    assertDatabaseCount('scheduled_notifications', 0);
})->with(array_values(array_diff(array_column(InterventionStatus::cases(), 'value'), ['planned', 'in progress', 'waiting for parts'])));

it('adds planned_at notification when maintenance_manager is linked to an ASSET', function ($status) {

    $formData = [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'locationId' => $this->site->id,
        'locationType' => 'site',
        'locationReference' => $this->site->reference_code,
        'categoryId' => $this->assetCategory->id,
        'maintenance_manager_id' => $this->manager->id
    ];

    $response = $this->postToTenant('api.assets.store', $formData);

    $asset = Asset::whereHas('maintainable', fn($query) => $query->where('name', 'New asset'))->first();

    $formData = [
        'intervention_type_id' => $this->interventionType->id,
        'priority' => 'medium',
        'status' => $status,
        'planned_at' => Carbon::now()->addMonth(1),
        'description' => fake()->paragraph(),
        'repair_delay' => Carbon::now()->addMonth(1),
        'locationId' => $asset->reference_code,
        'locationType' => 'asset'
    ];

    $response = $this->postToTenant('api.interventions.store', $formData);
    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
        ]);

    assertDatabaseCount('scheduled_notifications', 2);

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

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'planned_at',
            'scheduled_at' => Carbon::now()->addMonth(1)->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Intervention',
            'notifiable_id' => 1,
        ]
    );
})->with(array_values(array_diff(array_column(InterventionStatus::cases(), 'value'), ['draft', 'completed', 'cancelled'])));

it('adds planned_at notification when maintenance_manager is linked to an asset and intervention already exists', function ($status) {

    $formData = [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'locationId' => $this->site->id,
        'locationType' => 'site',
        'locationReference' => $this->site->reference_code,
        'categoryId' => $this->assetCategory->id,
    ];

    $response = $this->postToTenant('api.assets.store', $formData);

    $asset = Asset::whereHas('maintainable', fn($query) => $query->where('name', 'New asset'))->first();

    $formData = [
        'intervention_type_id' => $this->interventionType->id,
        'priority' => 'medium',
        'status' => $status,
        'planned_at' => Carbon::now()->addMonth(1),
        'description' => fake()->paragraph(),
        'repair_delay' => Carbon::now()->addMonth(1),
        'locationId' => $asset->reference_code,
        'locationType' => 'asset'
    ];

    $response = $this->postToTenant('api.interventions.store', $formData);
    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
        ]);

    assertDatabaseCount('scheduled_notifications', 1);

    $formData = [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'locationId' => $this->site->id,
        'locationType' => 'site',
        'locationReference' => $this->site->reference_code,
        'categoryId' => $this->assetCategory->id,
        'maintenance_manager_id' => $this->manager->id
    ];

    $response = $this->patchToTenant('api.assets.update', $formData, $asset->reference_code);
    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
        ]);

    assertDatabaseCount('scheduled_notifications', 2);
    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'planned_at',
            'scheduled_at' => Carbon::now()->addMonth(1)->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Intervention',
            'notifiable_id' => 1,
        ]
    );
})->with(array_values(array_diff(array_column(InterventionStatus::cases(), 'value'), ['draft', 'completed', 'cancelled'])));

it('creates planned_at notification for new maintenance manager when maintenance manager change in an asset', function ($status) {

    $tempManager = User::factory()->withRole('Maintenance Manager')->create();

    $formData = [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'locationId' => $this->site->id,
        'locationType' => 'site',
        'locationReference' => $this->site->reference_code,
        'categoryId' => $this->assetCategory->id,
        'maintenance_manager_id' => $tempManager->id
    ];

    $this->postToTenant('api.assets.store', $formData);

    $asset = Asset::whereHas('maintainable', fn($query) => $query->where('name', 'New asset'))->first();
    Intervention::factory()->withAction()->forLocation($asset)->create(['status' =>  $status]);

    assertDatabaseCount('scheduled_notifications', 2);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $tempManager->id,
            'recipient_name' => $tempManager->fullName,
            'recipient_email' => $tempManager->email,
            'notification_type' => 'planned_at',
            'scheduled_at' => Carbon::now()->addMonth(1)->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Intervention',
            'notifiable_id' => 1,
        ]
    );

    $formData = [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'locationId' => $this->site->id,
        'locationType' => 'site',
        'locationReference' => $this->site->reference_code,
        'categoryId' => $this->assetCategory->id,
        'maintenance_manager_id' => $this->manager->id
    ];

    $this->patchToTenant('api.assets.update', $formData, $asset->reference_code);

    assertDatabaseCount('scheduled_notifications', 2);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'planned_at',
            'scheduled_at' => Carbon::now()->addMonth(1)->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Intervention',
            'notifiable_id' => 1,
        ]
    );
})->with(array_values(array_diff(array_column(InterventionStatus::cases(), 'value'), ['draft', 'completed', 'cancelled'])));

it('deletes planned_at notification for old maintenance manager when he is replaced in an asset', function ($status) {
    $tempManager = User::factory()->withRole('Maintenance Manager')->create();

    $formData = [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'locationId' => $this->site->id,
        'locationType' => 'site',
        'locationReference' => $this->site->reference_code,
        'categoryId' => $this->assetCategory->id,
        'maintenance_manager_id' => $tempManager->id
    ];

    $this->postToTenant('api.assets.store', $formData);

    $asset = Asset::whereHas('maintainable', fn($query) => $query->where('name', 'New asset'))->first();
    Intervention::factory()->withAction()->forLocation($asset)->create(['status' =>  $status]);

    assertDatabaseCount('scheduled_notifications', 2);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $tempManager->id,
            'recipient_name' => $tempManager->fullName,
            'recipient_email' => $tempManager->email,
            'notification_type' => 'planned_at',
            'scheduled_at' => Carbon::now()->addMonth(1)->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Intervention',
            'notifiable_id' => 1,
        ]
    );

    $formData = [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'locationId' => $this->site->id,
        'locationType' => 'site',
        'locationReference' => $this->site->reference_code,
        'categoryId' => $this->assetCategory->id,
        'maintenance_manager_id' => $this->manager->id
    ];

    $this->patchToTenant('api.assets.update', $formData, $asset->reference_code);

    assertDatabaseCount('scheduled_notifications', 2);

    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'user_id' => $tempManager->id,
            'recipient_name' => $tempManager->fullName,
            'recipient_email' => $tempManager->email,
            'notification_type' => 'planned_at',
            'scheduled_at' => Carbon::now()->addMonth(1)->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Intervention',
            'notifiable_id' => 1,
        ]
    );
})->with(array_values(array_diff(array_column(InterventionStatus::cases(), 'value'), ['draft', 'completed', 'cancelled'])));

it('does not delete planned_at notification when a maintenance manager with admin role is removed from an ASSET', function ($status) {
    $formData = [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'locationId' => $this->site->id,
        'locationType' => 'site',
        'locationReference' => $this->site->reference_code,
        'categoryId' => $this->assetCategory->id,
        'maintenance_manager_id' => $this->admin->id
    ];

    $response = $this->postToTenant('api.assets.store', $formData);
    $response->assertStatus(200);

    $asset = Asset::whereHas('maintainable', fn($query) => $query->where('name', 'New asset'))->first();

    $formData = [
        'intervention_type_id' => $this->interventionType->id,
        'priority' => 'medium',
        'status' => $status,
        'planned_at' => Carbon::now()->addMonth(1),
        'description' => fake()->paragraph(),
        'repair_delay' => Carbon::now()->addMonth(1),
        'locationId' => $asset->reference_code,
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
            'scheduled_at' => Carbon::now()->addMonth(1)->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Intervention',
            'notifiable_id' => 1,
        ]
    );

    $formData = [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'locationId' => $this->site->id,
        'locationType' => 'site',
        'locationReference' => $this->site->reference_code,
        'categoryId' => $this->assetCategory->id,
        'maintenance_manager_id' => null
    ];

    $response = $this->patchToTenant('api.assets.update', $formData, $asset->reference_code);
    $response->assertStatus(200);


    assertDatabaseHas('maintainables', [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'maintenance_manager_id' => null
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
})->with(array_values(array_diff(array_column(InterventionStatus::cases(), 'value'), ['draft', 'completed', 'cancelled'])));

it('deletes planned_at notification when maintenance manager with maintenance manager role is removed from an ASSET and notification status is pending', function ($status) {
    $formData = [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'locationId' => $this->site->id,
        'locationType' => 'site',
        'locationReference' => $this->site->reference_code,
        'categoryId' => $this->assetCategory->id,
        'maintenance_manager_id' => $this->manager->id
    ];

    $response = $this->postToTenant('api.assets.store', $formData);
    $response->assertStatus(200);

    $asset = Asset::whereHas('maintainable', fn($query) => $query->where('name', 'New asset'))->first();

    $formData = [
        'intervention_type_id' => $this->interventionType->id,
        'priority' => 'medium',
        'status' => $status,
        'planned_at' => Carbon::now()->addMonth(1),
        'description' => fake()->paragraph(),
        'repair_delay' => Carbon::now()->addMonth(1),
        'locationId' => $asset->reference_code,
        'locationType' => 'asset'
    ];

    $response = $this->postToTenant('api.interventions.store', $formData);
    $response->assertStatus(200);

    assertDatabaseCount('scheduled_notifications', 2);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'planned_at',
            'scheduled_at' => Carbon::now()->addMonth(1)->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Intervention',
            'notifiable_id' => 1,
        ]
    );

    $formData = [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'locationId' => $this->site->id,
        'locationType' => 'site',
        'locationReference' => $this->site->reference_code,
        'categoryId' => $this->assetCategory->id,
        'maintenance_manager_id' => null
    ];

    $response = $this->patchToTenant('api.assets.update', $formData, $asset->reference_code);
    $response->assertStatus(200);


    assertDatabaseHas('maintainables', [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'maintenance_manager_id' => null
    ]);

    assertDatabaseCount('scheduled_notifications', 1);

    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'planned_at',
            'status' => 'pending',
            'scheduled_at' => Carbon::now()->addMonth(1)->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Intervention',
            'notifiable_id' => 1,
        ]
    );
})->with(array_values(array_diff(array_column(InterventionStatus::cases(), 'value'), ['draft', 'completed', 'cancelled'])));

it('does not delete planned_at notification when maintenance manager with maintenance manager role is removed from an ASSET and notification status is sent', function () {
    $formData = [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'locationId' => $this->site->id,
        'locationType' => 'site',
        'locationReference' => $this->site->reference_code,
        'categoryId' => $this->assetCategory->id,
        'maintenance_manager_id' => $this->manager->id
    ];

    $response = $this->postToTenant('api.assets.store', $formData);
    $response->assertStatus(200);

    $asset = Asset::whereHas('maintainable', fn($query) => $query->where('name', 'New asset'))->first();

    $formData = [
        'intervention_type_id' => $this->interventionType->id,
        'priority' => 'medium',
        'status' => 'planned',
        'planned_at' => Carbon::now()->addMonth(1),
        'description' => fake()->paragraph(),
        'repair_delay' => Carbon::now()->addMonth(1),
        'locationId' => $asset->reference_code,
        'locationType' => 'asset'
    ];

    $response = $this->postToTenant('api.interventions.store', $formData);
    $response->assertStatus(200);

    assertDatabaseCount('scheduled_notifications', 2);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'planned_at',
            'scheduled_at' => Carbon::now()->addMonth(1)->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Intervention',
            'notifiable_id' => 1,
        ]
    );

    $notification = ScheduledNotification::where('notifiable_type', 'App\Models\Tenants\Intervention')->where('notifiable_id', 1)->where('user_id', $this->manager->id)->first();
    $notification->update(['status' => 'sent']);

    $formData = [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'locationId' => $this->site->id,
        'locationType' => 'site',
        'locationReference' => $this->site->reference_code,
        'categoryId' => $this->assetCategory->id,
        'maintenance_manager_id' => null
    ];

    $response = $this->patchToTenant('api.assets.update', $formData, $asset->reference_code);
    $response->assertStatus(200);


    assertDatabaseHas('maintainables', [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'maintenance_manager_id' => null
    ]);

    assertDatabaseCount('scheduled_notifications', 2);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'status' => 'sent',
            'notification_type' => 'planned_at',
            'scheduled_at' => Carbon::now()->addMonth(1)->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Intervention',
            'notifiable_id' => 1,
        ]
    );
})->with(array_values(array_diff(array_column(InterventionStatus::cases(), 'value'), ['draft', 'completed', 'cancelled'])));

it('does not create a planned_at notification when planned_at is not defined for intervention', function ($status) {
    $formData = [
        'intervention_type_id' => $this->interventionType->id,
        'priority' => 'medium',
        'status' => $status,
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
})->with(array_column(InterventionStatus::cases(), 'value'));

it('updates notification when planned_at changes for an intervention', function ($status) {

    Intervention::factory()->withAction()->forLocation($this->asset)->create();

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
        'status' => $status,
        'planned_at' => Carbon::now()->addWeeks(2),
        'description' => fake()->paragraph(),
        'repair_delay' => Carbon::now()->addMonth(1),
        'locationId' => $this->asset->reference_code,
        'locationType' => get_class($this->asset)
    ];

    $intervention = Intervention::first();

    $response = $this->patchToTenant('api.interventions.update', $formData, $intervention->id);
    $response->assertSessionHasNoErrors();
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
})->with(array_values(array_diff(array_column(InterventionStatus::cases(), 'value'), ['draft', 'completed', 'cancelled'])));

it('creates planned_at notification when planned_at is added for an existing intervention', function ($status) {

    Intervention::factory()->withAction()->forLocation($this->asset)->create(['planned_at' => null]);

    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'notification_type' => 'planned_at',
            'notifiable_type' => 'App\Models\Tenants\Intervention',
            'notifiable_id' => 1,
        ]
    );

    $formData = [
        'intervention_type_id' => $this->interventionType->id,
        'priority' => 'medium',
        'status' => $status,
        'planned_at' => Carbon::now()->addWeeks(2),
        'description' => fake()->paragraph(),
        'locationId' => $this->asset->reference_code,
        'locationType' => get_class($this->asset)
    ];

    $intervention = Intervention::first();

    $response = $this->patchToTenant('api.interventions.update', $formData, $intervention->id);
    $response->assertSessionHasNoErrors();
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
})->with(array_values(array_diff(array_column(InterventionStatus::cases(), 'value'), ['draft', 'completed', 'cancelled'])));

it('deletes planned_at notification when intervention status changes to completed/cancelled and status is pending', function ($status) {

    $intervention = Intervention::factory()->withAction()->forLocation($this->asset)->create();

    $notification = ScheduledNotification::where('notifiable_type', get_class($intervention))->where('notifiable_id', $intervention->id)->first();

    $notification->update(['status' => 'sent']);

    $formData = [
        'intervention_type_id' => $this->interventionType->id,
        'priority' => 'medium',
        'status' => $status,
        'description' => fake()->paragraph(),
        'locationId' => $this->asset->reference_code,
        'locationType' => get_class($this->asset)
    ];

    $response = $this->patchToTenant('api.interventions.update', $formData, $intervention->id);
    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
        ]);

    assertDatabaseCount('scheduled_notifications', 1);
})->with(['completed', 'cancelled']);

it('does not delete planned_at notification when intervention status changes to completed/cancelled and status is sent', function ($status) {

    $intervention = Intervention::factory()->withAction()->forLocation($this->asset)->create();

    $notification = ScheduledNotification::where('notifiable_type', get_class($intervention))->where('notifiable_id', $intervention->id)->first();

    $notification->update(['status' => 'sent']);

    $formData = [
        'intervention_type_id' => $this->interventionType->id,
        'priority' => 'medium',
        'status' => $status,
        'description' => fake()->paragraph(),
        'locationId' => $this->asset->reference_code,
        'locationType' => get_class($this->asset)
    ];

    $response = $this->patchToTenant('api.interventions.update', $formData, $intervention->id);
    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
        ]);

    assertDatabaseCount('scheduled_notifications', 1);
})->with(['completed', 'cancelled']);

it('deletes planned_at notification with status `pending` when intervention is deleted', function () {

    Intervention::factory()->withAction()->forLocation($this->asset)->create();
    assertDatabaseCount('scheduled_notifications', 1);

    $intervention = Intervention::first();

    $response = $this->deleteFromTenant('api.interventions.destroy', $intervention->id);
    $response->assertStatus(200);
    assertDatabaseCount('scheduled_notifications', 0);
});

it('deletes planned_at notification with status `sent` when intervention is deleted', function () {

    Intervention::factory()->withAction()->forLocation($this->asset)->create();
    assertDatabaseCount('scheduled_notifications', 1);

    $intervention = Intervention::first();

    $notification = ScheduledNotification::first();
    $notification->update(['status' => 'sent']);

    $response = $this->deleteFromTenant('api.interventions.destroy', $intervention->id);
    $response->assertStatus(200);
    assertDatabaseCount('scheduled_notifications', 0);
});

it('deletes planned_at notifcation with status `pending` when user_preference planned_at is disabled', function () {
    Intervention::factory()->withAction()->forLocation($this->asset)->create();

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
            'user_id' => $this->admin->id,
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'planned_at',
            'status' => 'pending',
            'notifiable_type' => 'App\Models\Tenants\Intervention',
            'notifiable_id' => 1,
        ]
    );
});

it('does not delete planned_at notifcation with status `sent` when user_preference planned_at is disabled', function () {
    Intervention::factory()->withAction()->forLocation($this->asset)->create();

    assertDatabaseCount('scheduled_notifications', 1);

    $notification = ScheduledNotification::first();
    $notification->update(['status' => 'sent']);

    $preference = $this->admin->notification_preferences()->where('notification_type', 'planned_at')->first();

    $formData = [
        'asset_type' => 'intervention',
        'notification_type' => 'planned_at',
        'notification_delay_days' => $preference->notification_delay_days,
        'enabled' => false,
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
            'status' => 'sent',
            'notification_type' => 'planned_at',
            'notifiable_type' => 'App\Models\Tenants\Intervention',
            'notifiable_id' => 1,
        ]
    );
});

it('creates notification when user preference planned_at is enabled', function () {

    Intervention::factory()->withAction()->forLocation($this->asset)->create();

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
            'status' => 'pending',
            'scheduled_at' => Carbon::now()->addMonth(1)->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Intervention',
            'notifiable_id' => 1,
        ]
    );
});

it('updates planned_at notification with status `pending` when user preference notification_delay_days for planned_at changes', function ($status) {

    $formData = [
        'intervention_type_id' => $this->interventionType->id,
        'priority' => 'medium',
        'status' => $status,
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
})->with(array_values(array_diff(array_column(InterventionStatus::cases(), 'value'), ['draft', 'completed', 'cancelled'])));

it('does not update planned_at notification with status `sent` when user_preference notification_delay_days for planned_at changes', function ($status) {

    $formData = [
        'intervention_type_id' => $this->interventionType->id,
        'priority' => 'medium',
        'status' => $status,
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
            'status' => 'pending',
            'scheduled_at' => Carbon::now()->addMonth()->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Intervention',
            'notifiable_id' => 1,
        ]
    );

    $notification = ScheduledNotification::first();
    $notification->update(['status' => 'sent']);

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
            'status' => 'sent',
            'scheduled_at' => Carbon::now()->addMonth()->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Intervention',
            'notifiable_id' => 1,
        ]
    );
})->with(array_values(array_diff(array_column(InterventionStatus::cases(), 'value'), ['draft', 'completed', 'cancelled'])));

it('does not create planned_at notifications for admin when user preference planned_at is disabled', function () {

    $preference = $this->admin->notification_preferences()->where('notification_type', 'planned_at')->first();

    $formData = [
        'asset_type' => 'intervention',
        'notification_type' => 'planned_at',
        'notification_delay_days' => 1,
        'enabled' => false,
    ];

    $this->patchToTenant('api.notifications.update', $formData, $preference->id);

    Intervention::factory()->withAction()->forLocation($this->asset)->create(['planned_at' => Carbon::yesterday()]);
    Intervention::factory()->withAction()->forLocation($this->asset)->create(['planned_at' => Carbon::tomorrow()]);

    assertDatabaseCount('scheduled_notifications', 0);
});

it('creates planned_at notifications for admin when user preference planned_at is enabled only for planned_at > today', function () {

    $preference = $this->admin->notification_preferences()->where('notification_type', 'planned_at')->first();

    $formData = [
        'asset_type' => 'intervention',
        'notification_type' => 'planned_at',
        'notification_delay_days' => 1,
        'enabled' => false,
    ];

    $this->patchToTenant('api.notifications.update', $formData, $preference->id);

    Intervention::factory()->withAction()->forLocation($this->asset)->create(['planned_at' => Carbon::yesterday()]);
    Intervention::factory()->withAction()->forLocation($this->asset)->create(['planned_at' => Carbon::tomorrow()]);

    assertDatabaseCount('scheduled_notifications', 0);

    $formData = [
        'asset_type' => 'intervention',
        'notification_type' => 'planned_at',
        'notification_delay_days' => 7,
        'enabled' => true,
    ];

    $this->patchToTenant('api.notifications.update', $formData, $preference->id);

    assertDatabaseCount('scheduled_notifications', 1);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $this->admin->id,
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'planned_at',
            'status' => 'pending',
            'scheduled_at' => Carbon::tomorrow()->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Intervention',
            'notifiable_id' => 2,
        ]
    );

    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'user_id' => $this->admin->id,
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'planned_at',
            'status' => 'pending',
            'notifiable_type' => 'App\Models\Tenants\Intervention',
            'notifiable_id' => 1,
        ]
    );
});

it('creates planned_at notifications for maintenance_manager when user preference planned_at is enabled only for planned_at > today', function () {

    $preference = $this->manager->notification_preferences()->where('notification_type', 'planned_at')->first();

    $formData = [
        'asset_type' => 'intervention',
        'notification_type' => 'planned_at',
        'notification_delay_days' => 1,
        'enabled' => false,
    ];

    $this->patchToTenant('api.notifications.update', $formData, $preference->id);

    Intervention::factory()->withAction()->forLocation($this->asset)->create(['planned_at' => Carbon::yesterday()]);
    Intervention::factory()->withAction()->forLocation($this->asset)->create(['planned_at' => Carbon::tomorrow()]);

    $this->asset->maintainable->update(['maintenance_manager_id' => $this->manager->id]);

    $formData = [
        'asset_type' => 'intervention',
        'notification_type' => 'planned_at',
        'notification_delay_days' => 7,
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
            'notification_type' => 'planned_at',
            'status' => 'pending',
            'scheduled_at' => Carbon::tomorrow()->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Intervention',
            'notifiable_id' => 2,
        ]
    );

    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'user_id' => $this->manager->id,
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'planned_at',
            'status' => 'pending',
            'notifiable_type' => 'App\Models\Tenants\Intervention',
            'notifiable_id' => 1,
        ]
    );
});

it('creates planned_at notifications for a new created user with admin role', function () {

    $intervention = Intervention::factory()->withAction()->forLocation($this->asset)->create(['planned_at' => Carbon::tomorrow()]);

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
    $preference = $createdUser->notification_preferences()->where('notification_type', 'planned_at')->first();

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'user_id' => $createdUser->id,
            'recipient_name' => $createdUser->fullName,
            'recipient_email' => $createdUser->email,
            'notification_type' => 'planned_at',
            'scheduled_at' => Carbon::tomorrow()->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Intervention',
            'notifiable_id' => $intervention->id,
        ]
    );
});

it('creates end_date notifications when the role of a maintenance manager changes to admin', function () {

    $intervention = Intervention::factory()->withAction()->forLocation($this->asset)->create(['planned_at' => Carbon::tomorrow()]);


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
    $preference = $createdUser->notification_preferences()->where('notification_type', 'planned_at')->first();

    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'recipient_name' => $createdUser->fullName,
            'recipient_email' => $createdUser->email,
            'notification_type' => 'planned_at',
            'notifiable_type' => 'App\Models\Tenants\Intervention',
            'notifiable_id' => $intervention->id,
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

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $createdUser->fullName,
            'recipient_email' => $createdUser->email,
            'notification_type' => 'planned_at',
            'scheduled_at' => Carbon::tomorrow()->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Intervention',
            'notifiable_id' => $intervention->id,
        ]
    );
});

it('deletes end_date notifications when the role of an admin changes to maintenance manager', function () {
    $intervention = Intervention::factory()->withAction()->forLocation($this->asset)->create(['planned_at' => Carbon::tomorrow()]);

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
    $preference = $createdUser->notification_preferences()->where('notification_type', 'end_date')->first();

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $createdUser->fullName,
            'recipient_email' => $createdUser->email,
            'notification_type' => 'planned_at',
            'scheduled_at' => Carbon::tomorrow()->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Intervention',
            'notifiable_id' => $intervention->id,
        ]
    );

    $formData = [
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'janedoe@facilitywebxp.be',
        'can_login' => true,
        'role' => 'Maintenance Manager',
        'job_position' => 'Manager',
    ];

    $this->patchToTenant('api.users.update', $formData, $createdUser->id);

    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'recipient_name' => $createdUser->fullName,
            'recipient_email' => $createdUser->email,
            'notification_type' => 'planned_at',
            'scheduled_at' => Carbon::tomorrow()->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Intervention',
            'notifiable_id' => $intervention->id,
        ]
    );
});

it('deletes end_date notifications when the role of an admin changes to maintenance manager for assets only where he is not maintenance manager', function () {
    $interventionOne = Intervention::factory()->withAction()->forLocation($this->asset)->create(['planned_at' => Carbon::tomorrow()]);
    $interventionTwo = Intervention::factory()->withAction()->forLocation($this->room)->create(['planned_at' => Carbon::tomorrow()]);

    $this->room->refresh();

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
    $this->room->maintainable()->update(['maintenance_manager_id' => $createdUser->id]);
    $preference = $createdUser->notification_preferences()->where('notification_type', 'end_date')->first();

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $createdUser->fullName,
            'recipient_email' => $createdUser->email,
            'notification_type' => 'planned_at',
            'scheduled_at' => $interventionOne->planned_at->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Intervention',
            'notifiable_id' => $interventionOne->id,
        ]
    );
    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $createdUser->fullName,
            'recipient_email' => $createdUser->email,
            'notification_type' => 'planned_at',
            'scheduled_at' => $interventionTwo->planned_at->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Intervention',
            'notifiable_id' => $interventionTwo->id,
        ]
    );

    $formData = [
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'janedoe@facilitywebxp.be',
        'can_login' => true,
        'role' => 'Maintenance Manager',
        'job_position' => 'Manager',
    ];

    $this->patchToTenant('api.users.update', $formData, $createdUser->id);

    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'recipient_name' => $createdUser->fullName,
            'recipient_email' => $createdUser->email,
            'notification_type' => 'planned_at',
            'scheduled_at' => $interventionOne->planned_at->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Intervention',
            'notifiable_id' => $interventionOne->id,
        ]
    );
    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $createdUser->fullName,
            'recipient_email' => $createdUser->email,
            'notification_type' => 'planned_at',
            'scheduled_at' => $interventionTwo->planned_at->subDays($preference->notification_delay_days)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Intervention',
            'notifiable_id' => $interventionTwo->id,
        ]
    );
});
