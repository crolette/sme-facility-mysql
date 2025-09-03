<?php

use Carbon\Carbon;
use App\Models\LocationType;
use App\Models\Tenants\Site;
use App\Models\Tenants\User;
use App\Models\Tenants\Building;
use App\Models\Central\CategoryType;
use function PHPUnit\Framework\assertCount;
use function Pest\Laravel\assertDatabaseHas;
use function PHPUnit\Framework\assertEquals;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseMissing;


beforeEach(function () {
    $this->admin = User::factory()->withRole('Admin')->create();
    $this->manager = User::factory()->withRole('Maintenance Manager')->create();
    $this->actingAs($this->admin, 'tenant');
    LocationType::factory()->create(['level' => 'site']);
    $this->buildingType = LocationType::factory()->create(['level' => 'building']);
    $this->site = Site::factory()->create();
    $this->wallMaterial = CategoryType::factory()->create(['category' => 'wall_materials']);
    $this->floorMaterial = CategoryType::factory()->create(['category' => 'floor_materials']);

    $this->basicBuildingData = [
        'name' => 'New building',
        'surface_floor' => 2569.12,
        'address' => 'Rue du Buisson 22, 4000 Liège, Belgique',
        'floor_material_id' => $this->floorMaterial->id,
        'surface_walls' => 256.9,
        'wall_material_id' => $this->wallMaterial->id,
        'levelType' => $this->site->id,
        'description' => 'Description new building',
        'locationType' => $this->buildingType->id,
    ];
});

it('creates end of warranty notification for a new created building', function () {

    $formData = [
        ...$this->basicBuildingData,
        'under_warranty' => true,
        'end_warranty_date' => Carbon::now()->addMonths(10),
        'maintenance_manager_id' => $this->manager->id,
    ];

    $response = $this->postToTenant('api.buildings.store', $formData);
    $response->assertSessionHasNoErrors();

    assertDatabaseCount('scheduled_notifications', 2);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'end_warranty_date',
            'scheduled_at' => Carbon::now()->addMonth(10)->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Building',
            'notifiable_id' => 1,
        ]
    );

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_warranty_date',
            'scheduled_at' => Carbon::now()->addMonth(10)->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Building',
            'notifiable_id' => 1,
        ]
    );
});

it('updates end of warranty notification when end_warranty_date changes', function () {

    $formData = [
        ...$this->basicBuildingData,
        'under_warranty' => true,
        'end_warranty_date' => Carbon::now()->addMonths(10),
        'maintenance_manager_id' => $this->manager->id,
    ];

    $response = $this->postToTenant('api.buildings.store', $formData);
    $response->assertSessionHasNoErrors();

    assertDatabaseCount('scheduled_notifications', 2);

    $formData = [
        ...$this->basicBuildingData,
        'under_warranty' => true,
        'end_warranty_date' => Carbon::now()->addYears(2),
        'maintenance_manager_id' => $this->manager->id,
    ];

    $building = Building::find(1);
    $response = $this->patchToTenant('api.buildings.update', $formData, $building->reference_code);
    $response->assertSessionHasNoErrors();
    $response->assertStatus(200);

    assertDatabaseCount('scheduled_notifications', 2);
    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'end_warranty_date',
            'scheduled_at' => Carbon::now()->addYears(2)->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Building',
            'notifiable_id' => 1,
        ]
    );

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_warranty_date',
            'scheduled_at' => Carbon::now()->addYears(2)->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Building',
            'notifiable_id' => 1,
        ]
    );
});

it('creates warranty notifications when under_warranty passes from false to true', function () {

    $formData = [
        ...$this->basicBuildingData,
    ];

    $response = $this->postToTenant('api.buildings.store', $formData);
    assertDatabaseCount('scheduled_notifications', 0);
    $response->assertSessionHasNoErrors();
    $response->assertStatus(200);

    $formData = [
        ...$this->basicBuildingData,
        'under_warranty' => true,
        'end_warranty_date' => Carbon::now()->addMonths(10),
    ];
    $building = Building::find(1);
    $response = $this->patchToTenant('api.buildings.update', $formData, $building->reference_code);

    $response->assertSessionHasNoErrors();
    $response->assertStatus(200);

    assertDatabaseCount('scheduled_notifications', 1);
    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'end_warranty_date',
            'scheduled_at' => Carbon::now()->addMonths(10)->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Building',
            'notifiable_id' => $building->id,
        ]
    );
});

it('deletes warranty notifications when under_warranty passes from true to false', function () {

    $formData = [
        ...$this->basicBuildingData,
        'under_warranty' => true,
        'end_warranty_date' => Carbon::now()->addMonths(10),
    ];

    $response = $this->postToTenant('api.buildings.store', $formData);
    $response->assertSessionHasNoErrors();
    $response->assertStatus(200);
    $building = Building::find(1);

    assertDatabaseCount('scheduled_notifications', 1);
    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'end_warranty_date',
            'scheduled_at' => Carbon::now()->addMonths(10)->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Building',
            'notifiable_id' => $building->id,
        ]
    );

    $formData = [
        ...$this->basicBuildingData,
        'under_warranty' => false,
    ];

    $response = $this->patchToTenant('api.buildings.update', $formData, $building->reference_code);

    $response->assertSessionHasNoErrors();
    $response->assertStatus(200);

    assertDatabaseCount('scheduled_notifications', 0);
});


it('update notifications when notification preference end_warranty_date of user changes', function () {

    $formData = [
        ...$this->basicBuildingData,
        'under_warranty' => true,
        'end_warranty_date' => Carbon::now()->addMonths(10),
        'maintenance_manager_id' => $this->manager->id,
    ];

    $this->postToTenant('api.buildings.store', $formData);

    assertDatabaseCount('scheduled_notifications', 2);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'end_warranty_date',
            'scheduled_at' => Carbon::now()->addMonth(10)->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Building',
            'notifiable_id' => 1,
        ]
    );
    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_warranty_date',
            'scheduled_at' => Carbon::now()->addMonth(10)->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Building',
            'notifiable_id' => 1,
        ]
    );

    $preference = $this->admin->notification_preferences()->where('notification_type', 'end_warranty_date')->first();

    $formData = [
        'asset_type' => 'maintenance',
        'notification_type' => 'end_warranty_date',
        'notification_delay_days' => 1,
        'enabled' => true,
    ];

    $response = $this->patchToTenant('api.notifications.update', $formData, $preference->id);
    $response->assertStatus(200);

    assertDatabaseCount('scheduled_notifications', 2);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'end_warranty_date',
            'scheduled_at' => Carbon::now()->addMonth(10)->subDays(1)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Building',
            'notifiable_id' => 1,
        ]
    );

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'end_warranty_date',
            'scheduled_at' => Carbon::now()->addMonth(10)->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Building',
            'notifiable_id' => 1,
        ]
    );
});

it('deletes notifications when notification preference end_warranty_date of user is disabled', function () {


    $formData = [
        ...$this->basicBuildingData,
        'under_warranty' => true,
        'end_warranty_date' => Carbon::now()->addMonths(10),
        'maintenance_manager_id' => $this->manager->id,
    ];

    $response = $this->postToTenant('api.buildings.store', $formData);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'end_warranty_date',
            'scheduled_at' => Carbon::now()->addMonth(10)->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Building',
            'notifiable_id' => 1,
        ]
    );

    $preference = $this->admin->notification_preferences()->where('notification_type', 'end_warranty_date')->first();

    $formData = [
        'asset_type' => 'maintenance',
        'notification_type' => 'end_warranty_date',
        'notification_delay_days' => $preference->notification_delay_days,
        'enabled' => false,
    ];

    $response = $this->patchToTenant('api.notifications.update', $formData, $preference->id);
    $response->assertStatus(200);

    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'end_warranty_date',
            'scheduled_at' => Carbon::now()->addMonth(10)->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Building',
            'notifiable_id' => 1,
        ]
    );
});


it('creates notifications when notification preference warranty_end_date of user is enabled', function () {

    $formData = [
        ...$this->basicBuildingData,
        'under_warranty' => true,
        'end_warranty_date' => Carbon::now()->addMonths(10),
        'maintenance_manager_id' => $this->manager->id,
    ];;


    $response = $this->postToTenant('api.buildings.store', $formData);

    $preference = $this->admin->notification_preferences()->where('notification_type', 'end_warranty_date')->first();

    $formData = [
        'asset_type' => 'maintenance',
        'notification_type' => 'end_warranty_date',
        'notification_delay_days' => $preference->notification_delay_days,
        'enabled' => false,
    ];

    $response = $this->patchToTenant('api.notifications.update', $formData, $preference->id);
    $response->assertStatus(200);


    $formData = [
        'asset_type' => 'maintenance',
        'notification_type' => 'end_warranty_date',
        'notification_delay_days' => $preference->notification_delay_days,
        'enabled' => true,
    ];

    $response = $this->patchToTenant('api.notifications.update', $formData, $preference->id);
    $response->assertStatus(200);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'end_warranty_date',
            'scheduled_at' => Carbon::now()->addMonth(10)->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Building',
            'notifiable_id' => 1,
        ]
    );
});
