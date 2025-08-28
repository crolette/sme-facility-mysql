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

    $this->manager = User::factory()->create();
    $this->manager->assignRole('Maintenance Manager');

    LocationType::factory()->create(['level' => 'site']);
    LocationType::factory()->create(['level' => 'building']);
    LocationType::factory()->create(['level' => 'floor']);
    LocationType::factory()->create(['level' => 'room']);
    CategoryType::factory()->create(['category' => 'provider']);
    $this->categoryType = CategoryType::factory()->create(['category' => 'asset']);

    $this->site = Site::factory()->create();
    Building::factory()->create();
    Floor::factory()->create();
    $this->provider = Provider::factory()->create();

    $this->room = Room::factory()
        ->for(LocationType::where('level', 'room')->first())
        ->for(Floor::first())
        ->create();

    $this->basicAssetData = [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'locationId' => $this->site->id,
        'locationType' => 'site',
        'locationReference' => $this->site->reference_code,
        'surface' => 12,
        'categoryId' => $this->categoryType->id,
    ];
});

it('creates end of warranty notification for a new created contract', function () {


    $formData = [
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'janedoe@facilitywebxp.be',
        'can_login' => true,
        'role' => 'Admin',
        'job_position' => 'Manager',
    ];

    $this->postToTenant('api.users.store', $formData);
    $admin = User::where('email', 'janedoe@facilitywebxp.be')->first();

    $formData = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@facilitywebxp.be',
        'can_login' => true,
        'role' => 'Maintenance Manager',
        'job_position' => 'Manager',
    ];

    $this->postToTenant('api.users.store', $formData);

    $manager = User::where('email', 'john@facilitywebxp.be')->first();

    $formData = [
        ...$this->basicAssetData,
        'under_warranty' => true,
        'end_warranty_date' => Carbon::now()->addMonths(10),
        'maintenance_manager_id' => $manager->id,
    ];

    $this->postToTenant('api.assets.store', $formData);

    assertDatabaseCount('scheduled_notifications', 2);
    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $admin->fullName,
            'recipient_email' => $admin->email,
            'notification_type' => 'end_warranty_date',
            'scheduled_at' => Carbon::now()->addMonth(10)->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => 1,
        ]
    );

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $manager->fullName,
            'recipient_email' => $manager->email,
            'notification_type' => 'end_warranty_date',
            'scheduled_at' => Carbon::now()->addMonth(10)->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => 1,
        ]
    );
});

it('creates depreciation notification for a new created contract', function () {
    $formData = [
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'janedoe@facilitywebxp.be',
        'can_login' => true,
        'role' => 'Admin',
        'job_position' => 'Manager',
    ];

    $this->postToTenant('api.users.store', $formData);
    $admin = User::where('email', 'janedoe@facilitywebxp.be')->first();

    $formData = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@facilitywebxp.be',
        'can_login' => true,
        'role' => 'Maintenance Manager',
        'job_position' => 'Manager',
    ];

    $this->postToTenant('api.users.store', $formData);

    $manager = User::where('email', 'john@facilitywebxp.be')->first();

    $formData = [
        ...$this->basicAssetData,
        'maintenance_manager_id' => $manager->id,
        'depreciable' => true,
        'depreciation_start_date' => Carbon::now()->toDateString(),
        'depreciation_end_date' => Carbon::now()->addYear(3)->toDateString(),
        'depreciation_duration' => 3,
        'residual_value' => 1250.69,

    ];

    $this->postToTenant('api.assets.store', $formData);

    assertDatabaseCount('scheduled_notifications', 2);
    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $admin->fullName,
            'recipient_email' => $admin->email,
            'notification_type' => 'depreciation_end_date',
            'scheduled_at' => Carbon::now()->addYear(3)->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => 1,
        ]
    );

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $manager->fullName,
            'recipient_email' => $manager->email,
            'notification_type' => 'depreciation_end_date',
            'scheduled_at' => Carbon::now()->addYear(3)->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => 1,
        ]
    );
});

it('creates maintenance notification for a new created contract', function () {
    $formData = [
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'janedoe@facilitywebxp.be',
        'can_login' => true,
        'role' => 'Admin',
        'job_position' => 'Manager',
    ];

    $this->postToTenant('api.users.store', $formData);
    $admin = User::where('email', 'janedoe@facilitywebxp.be')->first();

    $formData = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@facilitywebxp.be',
        'can_login' => true,
        'role' => 'Maintenance Manager',
        'job_position' => 'Manager',
    ];

    $this->postToTenant('api.users.store', $formData);

    $manager = User::where('email', 'john@facilitywebxp.be')->first();

    $formData = [
        ...$this->basicAssetData,
        'maintenance_manager_id' => $manager->id,
        'maintenance_frequency' => 'annual',
        'need_maintenance' => true,
        'next_maintenance_date' => Carbon::now()->addYear(),
        'last_maintenance_date' => Carbon::now()->toDateString(),
    ];

    $response =  $this->postToTenant('api.assets.store', $formData);
    $response->assertSessionHasNoErrors();

    assertDatabaseCount('scheduled_notifications', 2);
    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $admin->fullName,
            'recipient_email' => $admin->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addYear()->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => 1,
        ]
    );

    assertDatabaseHas(
        'scheduled_notifications',
        [
            'recipient_name' => $manager->fullName,
            'recipient_email' => $manager->email,
            'notification_type' => 'next_maintenance_date',
            'scheduled_at' => Carbon::now()->addYear()->subDays(7)->toDateString(),
            'notifiable_type' => 'App\Models\Tenants\Asset',
            'notifiable_id' => 1,
        ]
    );
});

// it('update notifications when notification preference notice_date of user changes', function () {

//     $formData = [
//         'first_name' => 'Jane',
//         'last_name' => 'Doe',
//         'email' => 'janedoe@facilitywebxp.be',
//         'can_login' => true,
//         'role' => 'Admin',
//         'job_position' => 'Manager',
//     ];

//     $this->postToTenant('api.users.store', $formData);


//     $formData = $this->basicContractData;


//     $response = $this->postToTenant('api.contracts.store', $formData);
//     $createdUser = User::where('email', 'janedoe@facilitywebxp.be')->first();

//     assertDatabaseHas(
//         'scheduled_notifications',
//         [
//             'recipient_name' => $createdUser->fullName,
//             'recipient_email' => $createdUser->email,
//             'notification_type' => 'notice_date',
//             'scheduled_at' => Carbon::now()->addMonth(1)->subDays(21)->toDateString(),
//             'notifiable_type' => 'App\Models\Tenants\Contract',
//             'notifiable_id' => 1,
//         ]
//     );

//     $preference = $createdUser->notification_preferences()->where('notification_type', 'notice_date')->first();

//     $formData = [
//         'asset_type' => 'contract',
//         'notification_type' => 'notice_date',
//         'notification_delay_days' => 1,
//         'enabled' => true,
//     ];

//     $response = $this->patchToTenant('api.notifications.update', $formData, $preference->id);
//     $response->assertStatus(200);

//     assertDatabaseHas(
//         'scheduled_notifications',
//         [
//             'recipient_name' => $createdUser->fullName,
//             'recipient_email' => $createdUser->email,
//             'notification_type' => 'notice_date',
//             'scheduled_at' => Carbon::now()->addMonth(1)->subDays(15)->toDateString(),
//             'notifiable_type' => 'App\Models\Tenants\Contract',
//             'notifiable_id' => 1,
//         ]
//     );
// });

// it('update notifications when notification preference end_date of user changes', function () {

//     $formData = [
//         'first_name' => 'Jane',
//         'last_name' => 'Doe',
//         'email' => 'janedoe@facilitywebxp.be',
//         'can_login' => true,
//         'role' => 'Admin',
//         'job_position' => 'Manager',
//     ];

//     $this->postToTenant('api.users.store', $formData);


//     $formData = $this->basicContractData;


//     $response = $this->postToTenant('api.contracts.store', $formData);
//     $createdUser = User::where('email', 'janedoe@facilitywebxp.be')->first();

//     assertDatabaseHas(
//         'scheduled_notifications',
//         [
//             'recipient_name' => $createdUser->fullName,
//             'recipient_email' => $createdUser->email,
//             'notification_type' => 'end_date',
//             'scheduled_at' => Carbon::now()->addMonth(1)->subDays(7)->toDateString(),
//             'notifiable_type' => 'App\Models\Tenants\Contract',
//             'notifiable_id' => 1,
//         ]
//     );

//     $preference = $createdUser->notification_preferences()->where('notification_type', 'end_date')->first();

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
//             'recipient_name' => $createdUser->fullName,
//             'recipient_email' => $createdUser->email,
//             'notification_type' => 'end_date',
//             'scheduled_at' => Carbon::now()->addMonth(1)->subDays(1)->toDateString(),
//             'notifiable_type' => 'App\Models\Tenants\Contract',
//             'notifiable_id' => 1,
//         ]
//     );
// });

// it('deletes notifications when notification preference notice_date of user is disabled', function () {

//     $formData = [
//         'first_name' => 'Jane',
//         'last_name' => 'Doe',
//         'email' => 'janedoe@facilitywebxp.be',
//         'can_login' => true,
//         'role' => 'Admin',
//         'job_position' => 'Manager',
//     ];

//     $this->postToTenant('api.users.store', $formData);


//     $formData = $this->basicContractData;


//     $response = $this->postToTenant('api.contracts.store', $formData);
//     $createdUser = User::where('email', 'janedoe@facilitywebxp.be')->first();

//     assertDatabaseHas(
//         'scheduled_notifications',
//         [
//             'recipient_name' => $createdUser->fullName,
//             'recipient_email' => $createdUser->email,
//             'notification_type' => 'notice_date',
//             'scheduled_at' => Carbon::now()->addMonth(1)->subDays(21)->toDateString(),
//             'notifiable_type' => 'App\Models\Tenants\Contract',
//             'notifiable_id' => 1,
//         ]
//     );

//     $preference = $createdUser->notification_preferences()->where('notification_type', 'notice_date')->first();

//     $formData = [
//         'asset_type' => 'contract',
//         'notification_type' => 'notice_date',
//         'notification_delay_days' => $preference->notification_delay_days,
//         'enabled' => false,
//     ];

//     $response = $this->patchToTenant('api.notifications.update', $formData, $preference->id);
//     $response->assertStatus(200);

//     assertDatabaseMissing(
//         'scheduled_notifications',
//         [
//             'recipient_name' => $createdUser->fullName,
//             'recipient_email' => $createdUser->email,
//             'notification_type' => 'notice_date',
//             'scheduled_at' => Carbon::now()->addMonth(1)->subDays(21)->toDateString(),
//             'notifiable_type' => 'App\Models\Tenants\Contract',
//             'notifiable_id' => 1,
//         ]
//     );
// });

// it('deletes notifications when notification preference end_date of user is disabled', function () {

//     $formData = [
//         'first_name' => 'Jane',
//         'last_name' => 'Doe',
//         'email' => 'janedoe@facilitywebxp.be',
//         'can_login' => true,
//         'role' => 'Admin',
//         'job_position' => 'Manager',
//     ];

//     $this->postToTenant('api.users.store', $formData);


//     $formData = $this->basicContractData;


//     $response = $this->postToTenant('api.contracts.store', $formData);
//     $createdUser = User::where('email', 'janedoe@facilitywebxp.be')->first();

//     assertDatabaseHas(
//         'scheduled_notifications',
//         [
//             'recipient_name' => $createdUser->fullName,
//             'recipient_email' => $createdUser->email,
//             'notification_type' => 'end_date',
//             'scheduled_at' => Carbon::now()->addMonth(1)->subDays(7)->toDateString(),
//             'notifiable_type' => 'App\Models\Tenants\Contract',
//             'notifiable_id' => 1,
//         ]
//     );

//     $preference = $createdUser->notification_preferences()->where('notification_type', 'end_date')->first();

//     $formData = [
//         'asset_type' => 'contract',
//         'notification_type' => 'end_date',
//         'notification_delay_days' => $preference->notification_delay_days,
//         'enabled' => false,
//     ];

//     $response = $this->patchToTenant('api.notifications.update', $formData, $preference->id);
//     $response->assertStatus(200);

//     assertDatabaseMissing(
//         'scheduled_notifications',
//         [
//             'recipient_name' => $createdUser->fullName,
//             'recipient_email' => $createdUser->email,
//             'notification_type' => 'end_date',
//             'scheduled_at' => Carbon::now()->addMonth(1)->subDays(7)->toDateString(),
//             'notifiable_type' => 'App\Models\Tenants\Contract',
//             'notifiable_id' => 1,
//         ]
//     );
// });

// it('creates notifications when notification preference notice_date of user is enabled', function () {

//     $formData = [
//         'first_name' => 'Jane',
//         'last_name' => 'Doe',
//         'email' => 'janedoe@facilitywebxp.be',
//         'can_login' => true,
//         'role' => 'Admin',
//         'job_position' => 'Manager',
//     ];

//     $this->postToTenant('api.users.store', $formData);


//     $formData = $this->basicContractData;


//     $response = $this->postToTenant('api.contracts.store', $formData);
//     $createdUser = User::where('email', 'janedoe@facilitywebxp.be')->first();

//     $preference = $createdUser->notification_preferences()->where('notification_type', 'notice_date')->first();

//     $formData = [
//         'asset_type' => 'contract',
//         'notification_type' => 'notice_date',
//         'notification_delay_days' => $preference->notification_delay_days,
//         'enabled' => false,
//     ];

//     $response = $this->patchToTenant('api.notifications.update', $formData, $preference->id);
//     $response->assertStatus(200);


//     $formData = [
//         'asset_type' => 'contract',
//         'notification_type' => 'notice_date',
//         'notification_delay_days' => $preference->notification_delay_days,
//         'enabled' => true,
//     ];

//     $response = $this->patchToTenant('api.notifications.update', $formData, $preference->id);
//     $response->assertStatus(200);

//     assertDatabaseHas(
//         'scheduled_notifications',
//         [
//             'recipient_name' => $createdUser->fullName,
//             'recipient_email' => $createdUser->email,
//             'notification_type' => 'notice_date',
//             'scheduled_at' => Carbon::now()->addMonth(1)->subDays(21)->toDateString(),
//             'notifiable_type' => 'App\Models\Tenants\Contract',
//             'notifiable_id' => 1,
//         ]
//     );
// });

// it('creates notifications when notification preference end_date of user is enabled', function () {

//     $formData = [
//         'first_name' => 'Jane',
//         'last_name' => 'Doe',
//         'email' => 'janedoe@facilitywebxp.be',
//         'can_login' => true,
//         'role' => 'Admin',
//         'job_position' => 'Manager',
//     ];

//     $this->postToTenant('api.users.store', $formData);


//     $formData = $this->basicContractData;


//     $response = $this->postToTenant('api.contracts.store', $formData);
//     $createdUser = User::where('email', 'janedoe@facilitywebxp.be')->first();

//     $preference = $createdUser->notification_preferences()->where('notification_type', 'end_date')->first();

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

//     assertDatabaseHas(
//         'scheduled_notifications',
//         [
//             'recipient_name' => $createdUser->fullName,
//             'recipient_email' => $createdUser->email,
//             'notification_type' => 'end_date',
//             'scheduled_at' => Carbon::now()->addMonth(1)->subDays(7)->toDateString(),
//             'notifiable_type' => 'App\Models\Tenants\Contract',
//             'notifiable_id' => 1,
//         ]
//     );
// });
