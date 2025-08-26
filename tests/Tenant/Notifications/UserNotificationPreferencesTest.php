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

    $this->user = User::factory()->create();
    $this->user->assignRole('Admin');
    $this->actingAs($this->user, 'tenant');

    LocationType::factory()->create(['level' => 'site']);
    LocationType::factory()->create(['level' => 'building']);
    LocationType::factory()->create(['level' => 'floor']);
    LocationType::factory()->create(['level' => 'room']);
    CategoryType::factory()->create(['category' => 'provider']);
    CategoryType::factory()->create(['category' => 'asset']);

    $this->site = Site::factory()->create();
    $this->building = Building::factory()->create();
    $this->floor = Floor::factory()->create();
    $this->provider = Provider::factory()->create();

    $this->room = Room::factory()
        ->for(LocationType::where('level', 'room')->first())
        ->for(Floor::first())
        ->create();

    $this->asset = Asset::factory()->forLocation(Room::first())->create();

    // $this->basicAssetData = [
    //     'name' => 'New asset',
    //     'description' => 'Description new asset',
    //     'locationId' => $this->site->id,
    //     'locationType' => 'site',
    //     'locationReference' => $this->site->reference_code,
    //     'surface' => 12,
    //     'categoryId' => $this->categoryType->id,
    //     'maintenance_manager_id' => $this->manager->id
    // ];
});

it('creates default notification when user is created', function () {

    $formData = [
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'janedoe@facilitywebxp.be',
        'can_login' => true,
        'role' => 'Maintenance Manager',
        'job_position' => 'Manager',
    ];

    $response = $this->postToTenant('api.users.store', $formData);
    $response->assertSessionHasNoErrors();
    $response->assertStatus(200);

    $createdUser = User::where('email', 'janedoe@facilitywebxp.be')->first();

    assertDatabaseCount('user_notification_preferences', 7);
    assertEquals($createdUser->notification_preferences()->count(), 7);
});

it('can create a new notification preference', function () {

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

    $this->actingAs($createdUser, 'tenant');

    $formData = [
        'asset_type' => 'asset',
        'notification_type' => 'depreciation_end_date',
        'notification_delay_days' => 30,
        'enabled' => true,
    ];

    $response = $this->postToTenant('api.notification-preferences.store', $formData);
    $response->assertStatus(200);

    assertDatabaseHas(
        'user_notification_preferences',
        [
            'user_id' => $createdUser->id,
            'asset_type' => 'asset',
            'notification_type' => 'depreciation_end_date',
            'notification_delay_days' => 30,
            'enabled' => true,
        ]
    );
});

it('can update notification preferences', function () {

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

    $preference = $createdUser->notification_preferences()->first();

    $formData = [
        'asset_type' => $preference->asset_type,
        'notification_type' => $preference->notification_type,
        'notification_delay_days' => 30,
        'enabled' => true,
    ];

    $response = $this->patchToTenant('api.notification-preferences.update', $formData, $preference->id);
    $response->assertStatus(200);

    assertDatabaseHas(
        'user_notification_preferences',
        [
            'user_id' => $createdUser->id,
            'asset_type' => $preference->asset_type,
            'notification_type' => $preference->notification_type,
            'notification_delay_days' => 30,
            'enabled' => true,
        ]
    );
});
