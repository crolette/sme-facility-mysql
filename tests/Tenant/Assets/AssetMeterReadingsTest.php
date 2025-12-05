<?php

use Carbon\Carbon;
use App\Models\LocationType;
use App\Models\Tenants\Room;
use App\Models\Tenants\Site;
use App\Models\Tenants\User;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Floor;
use App\Models\Tenants\Building;

use App\Models\Tenants\Provider;
use Illuminate\Http\UploadedFile;
use App\Models\Central\CategoryType;
use Illuminate\Support\Facades\Storage;
use function PHPUnit\Framework\assertCount;
use function Pest\Laravel\assertDatabaseHas;
use function PHPUnit\Framework\assertEquals;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseMissing;


beforeEach(function () {
    $this->user = User::factory()->withRole('Admin')->create();
    $this->actingAs($this->user, 'tenant');
    $this->manager = User::factory()->withRole('Maintenance Manager')->create();

    $this->categoryType = CategoryType::factory()->create(['category' => 'asset']);
    CategoryType::factory()->count(2)->create(['category' => 'asset']);
    $this->site = Site::factory()->create();
    $this->building = Building::factory()->create();
    $this->floor = Floor::factory()->create();
    $this->room = Room::factory()->create();
});



// it('can create a new asset with meter readings', function () {

//     $formData = [
//         'name' => 'New asset',
//         'description' => 'Description new asset',
//         'has_meter_readings' => true,
//         'meter_number' => '0235845568',
//         'locationId' => $this->room->id,
//         'locationReference' => $this->room->reference_code,
//         'locationType' => 'room',
//         'categoryId' => $this->categoryType->id,
//     ];

//     $response = $this->postToTenant('api.assets.store', $formData);
//     $response->assertStatus(200)
//         ->assertJson(['status' => 'success']);

//     $asset = Asset::first();

//     assertDatabaseCount('assets', 1);

//     assertDatabaseHas('assets', [
//         'code' => 'A0001',
//         'reference_code' => $this->room->reference_code . '-' . 'A0001',
//         'location_type' => get_class($this->room),
//         'location_id' => $this->room->id,
//         'category_type_id' => $this->categoryType->id,
//         'has_meter_readings' => true,
//         'meter_number' => '0235845568',
//     ]);

//     assertDatabaseHas('maintainables', [
//         'maintainable_type' => get_class($asset),
//         'maintainable_id' => $asset->id,
//         'name' => 'New asset',
//         'description' => 'Description new asset',
//     ]);
// });

// it('can update the has_meter_reading of an asset', function () {

//     $asset = Asset::factory()->forLocation($this->room)->create();

//     assertDatabaseHas(
//         'assets',
//         [
//             'id' => $asset->id,
//             'has_meter_readings' => false,
//             'meter_number' => null
//         ]
//     );

//     $formData = [
//         'name' => 'New asset',
//         'description' => 'Description new asset',
//         'has_meter_readings' => true,
//         'meter_number' => '0235845568',
//         'locationId' => $this->room->id,
//         'locationReference' => $this->room->reference_code,
//         'locationType' => 'room',
//         'categoryId' => $this->categoryType->id,
//     ];

//     $response = $this->patchToTenant('api.assets.update', $formData, $asset);
//     $response->assertStatus(200)
//         ->assertJson(['status' => 'success']);

//     assertDatabaseHas(
//         'assets',
//         [
//             'id' => $asset->id,
//             'has_meter_readings' => true,
//             'meter_number' => '0235845568'
//         ]
//     );
// });


it('remove the meter number if has_meter_reading passes from true to false', function () {

    $asset = Asset::factory()->forLocation($this->room)->create(['has_meter_readings' => true, 'meter_number' => '358412326dad']);

    assertDatabaseHas(
        'assets',
        [
            'id' => $asset->id,
            'has_meter_readings' => true,
            'meter_number' => '358412326dad'
        ]
    );

    $formData = [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'has_meter_readings' => false,
        'meter_number' => '0235845568',
        'locationId' => $this->room->id,
        'locationReference' => $this->room->reference_code,
        'locationType' => 'room',
        'categoryId' => $this->categoryType->id,
    ];

    $response = $this->patchToTenant('api.assets.update', $formData, $asset);
    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);

    assertDatabaseHas(
        'assets',
        [
            'id' => $asset->id,
            'has_meter_readings' => false,
            'meter_number' => null
        ]
    );
});

it('can add a new meter reading for an asset', function () {

    $asset = Asset::factory()->forLocation($this->room)->create();

    $formData = [
        'meter' => 1234.24,
        'meter_date' => Carbon::now()->toDateString(),
        'notes' => 'Vérifier le mois prochain'
    ];
    $response = $this->postToTenant('api.assets.meter-readings.store', $formData,  $asset);
    $response->assertSessionHasNoErrors();
    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);

    assertDatabaseHas(('meter_readings'), [
        'meter' => 1234.24,
        'meter_date' => Carbon::now()->toDateString(),
        'notes' => 'Vérifier le mois prochain',
        'asset_id' => $asset->id,
        'user_id' => $this->user->id
    ]);
});
