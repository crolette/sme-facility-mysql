<?php

use Carbon\Carbon;
use App\Models\LocationType;
use App\Models\Tenants\Site;
use App\Models\Tenants\User;
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
    $this->siteType = LocationType::factory()->create(['level' => 'site']);

    $wallMaterial = CategoryType::factory()->create(['category' => 'wall_materials']);
    $floorMaterial = CategoryType::factory()->create(['category' => 'floor_materials']);

    $this->basicSiteData = [
        'name' => 'New site',
        'surface_floor' => 2569.12,
        'address' => 'Rue du Buisson 22, 4000 Liège, Belgique',
        'floor_material_id' => $floorMaterial->id,
        'surface_walls' => 256.9,
        'wall_material_id' => $wallMaterial->id,
        'description' => 'Description new site',
        'locationType' => $this->siteType->id,
    ];
});

it('creates notification when adding maintenance manager to existing site without maintenance manager', function () {

    $formData = [
        ...$this->basicSiteData,
        'maintenance_frequency' => 'annual',
        'need_maintenance' => true,
        'next_maintenance_date' => Carbon::now()->addYear(),
        'last_maintenance_date' => Carbon::now()->toDateString(),
    ];

    $response = $this->postToTenant('api.sites.store', $formData);
    $response->assertSessionHasNoErrors();
    $response->assertStatus(200);

    assertDatabaseCount('scheduled_notifications', 1);
    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addYear()->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Site',
            'notifiable_id' => 1,
        ]
    );

    $site = Site::find(1);

    $formData = [
        ...$this->basicSiteData,
        'maintenance_manager_id' => $this->manager->id,
        'need_maintenance' => true,
        'maintenance_frequency' => 'annual',
        'next_maintenance_date' => Carbon::now()->addYear(),
        'last_maintenance_date' => Carbon::now()->toDateString(),
    ];

    $response = $this->patchToTenant('api.sites.update', $formData, $site->reference_code);
    $response->assertSessionHasNoErrors();
    $response->assertStatus(200);

    assertDatabaseCount('scheduled_notifications', 2);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addYear()->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Site',
            'notifiable_id' => 1,
        ]
    );
});

it('creates notification when replacing maintenance manager for the site and removes notifications for old maintenance manager', function () {

    $site = Site::factory()->create();

    $formData = [
        ...$this->basicSiteData,
        'maintenance_manager_id' => $this->manager->id,
        'maintenance_frequency' => 'annual',
        'need_maintenance' => true,
        'next_maintenance_date' => Carbon::now()->addYear(),
        'last_maintenance_date' => Carbon::now()->toDateString(),
    ];

    $response = $this->patchToTenant('api.sites.update', $formData, $site->reference_code);
    $response->assertSessionHasNoErrors();
    $response->assertStatus(200);

    assertDatabaseCount('scheduled_notifications', 2);

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addYear()->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Site',
            'notifiable_id' => $site->id,
        ]
    );
});

it('deletes notification when removing maintenance_manager from existing site', function () {
    $formData = [
        ...$this->basicSiteData,
        'maintenance_frequency' => 'annual',
        'need_maintenance' => true,
        'next_maintenance_date' => Carbon::now()->addYear(),
        'last_maintenance_date' => Carbon::now()->toDateString(),
        'maintenance_manager_id' => $this->manager->id,
    ];

    $response = $this->postToTenant('api.sites.store', $formData);
    $response->assertSessionHasNoErrors();
    $response->assertStatus(200);

    assertDatabaseCount('scheduled_notifications', 2);
    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addYear()->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Site',
            'notifiable_id' => 1,
        ]
    );

    $formData = [
        ...$this->basicSiteData,
        'maintenance_frequency' => 'annual',
        'need_maintenance' => true,
        'next_maintenance_date' => Carbon::now()->addYear(),
        'last_maintenance_date' => Carbon::now()->toDateString(),
    ];

    $site = Site::find(1);
    $response = $this->patchToTenant('api.sites.update', $formData, $site->reference_code);

    $response->assertSessionHasNoErrors();
    $response->assertStatus(200);

    assertDatabaseCount('scheduled_notifications', 1);
    assertDatabaseMissing(
        'scheduled_notifications',
        [
            'recipient_name' => $this->manager->fullName,
            'recipient_email' => $this->manager->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addYear()->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Site',
            'notifiable_id' => 1,
        ]
    );
});
