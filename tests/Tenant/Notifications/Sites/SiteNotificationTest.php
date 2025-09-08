<?php

use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\LocationType;
use App\Models\Tenants\Site;
use App\Models\Tenants\User;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Floor;
use App\Models\Tenants\Picture;
use App\Models\Tenants\Building;
use App\Models\Tenants\Document;
use App\Models\Tenants\Provider;
use Illuminate\Http\UploadedFile;
use App\Enums\MaintenanceFrequency;
use App\Models\Central\CategoryType;
use Illuminate\Support\Facades\Storage;
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
});

it('creates notifications (maintenance, warranty) for a new created site', function () {
    $wallMaterial = CategoryType::factory()->create(['category' => 'wall_materials']);
    $floorMaterial = CategoryType::factory()->create(['category' => 'floor_materials']);

    $formData = [
        'name' => 'New site',
        'surface_floor' => 2569.12,
        'address' => 'Rue du Buisson 22, 4000 Liège, Belgique',
        'floor_material_id' => $floorMaterial->id,
        'surface_walls' => 256.9,
        'wall_material_id' => $wallMaterial->id,
        'description' => 'Description new site',
        'locationType' => $this->siteType->id,
        'under_warranty' => true,
        'end_warranty_date' => Carbon::now()->addMonths(10),
        'need_maintenance' => true,
        'maintenance_frequency' => MaintenanceFrequency::ANNUAL->value,
        'last_maintenance_date' => Carbon::now()->toDateString(),
        'maintenance_frequency' => MaintenanceFrequency::ANNUAL->value
    ];

    $response = $this->postToTenant('api.sites.store', $formData);
    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);

    assertDatabaseCount('scheduled_notifications', 2);

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

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $this->admin->fullName,
            'recipient_email' => $this->admin->email,
            'notification_type' => 'end_warranty_date',
            'scheduled_at' => Carbon::now()->addMonths(10)->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Site',
            'notifiable_id' => 1,
        ]
    );
});


it(
    'deletes notifications when site is deleted',
    function () {
        $formData = [
            'name' => 'New site',
            'address' => 'Rue du Buisson 22, 4000 Liège, Belgique',
            'description' => 'Description new site',
            'locationType' => $this->siteType->id,
            'under_warranty' => true,
            'end_warranty_date' => Carbon::now()->addMonths(10),
            'need_maintenance' => true,
            'maintenance_frequency' => MaintenanceFrequency::ANNUAL->value,
            'last_maintenance_date' => Carbon::now()->toDateString(),
            'maintenance_frequency' => MaintenanceFrequency::ANNUAL->value
        ];

        $response = $this->postToTenant('api.sites.store', $formData);
        $response->assertSessionHasNoErrors();
        $response->assertStatus(200);

        assertDatabaseCount('scheduled_notifications', 2);

        $site = Site::find(1);

        $response = $this->deleteFromTenant('api.sites.destroy', $site->reference_code);

        assertDatabaseEmpty('scheduled_notifications');
    }
);
