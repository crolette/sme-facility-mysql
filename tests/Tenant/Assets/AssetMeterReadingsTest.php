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

use App\Enums\MeterReadingsUnits;
use Illuminate\Http\UploadedFile;
use App\Models\Central\CategoryType;
use App\Models\Tenants\MeterReading;
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
    $this->site = Site::factory()->withMaintainableData()->create();
    $this->building = Building::factory()->withMaintainableData()->create();
    $this->floor = Floor::factory()->withMaintainableData()->create();
    $this->room = Room::factory()->withMaintainableData()->create();
});

it('can create a new asset with meter readings', function ($unit) {

    $formData = [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'has_meter_readings' => true,
        'meter_number' => '0235845568',
        'meter_unit' => $unit,
        'locationId' => $this->room->id,
        'locationReference' => $this->room->reference_code,
        'locationType' => 'room',
        'categoryId' => $this->categoryType->id,
    ];

    $response = $this->postToTenant('api.assets.store', $formData);
    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);

    assertDatabaseCount('assets', 1);

    assertDatabaseHas('assets', [
        'code' => 'A0001',
        'reference_code' => $this->room->reference_code . '-' . 'A0001',
        'location_type' => get_class($this->room),
        'location_id' => $this->room->id,
        'category_type_id' => $this->categoryType->id,
        'has_meter_readings' => true,
        'meter_number' => '0235845568',
        'meter_unit' => $unit,
    ]);
})->with(array_column(MeterReadingsUnits::cases(), 'value'));

it('can update the has_meter_reading of an asset', function () {

    $asset = Asset::factory()->withMaintainableData()->forLocation($this->room)->create();

    assertDatabaseHas(
        'assets',
        [
            'id' => $asset->id,
            'has_meter_readings' => false,
            'meter_unit' => null,
            'meter_number' => null
        ]
    );

    $formData = [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'has_meter_readings' => true,
        'meter_number' => '0235845568',
        'meter_unit' => 'kWh',
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
            'has_meter_readings' => true,
            'meter_unit' => 'kWh',
            'meter_number' => '0235845568'
        ]
    );
});


it('remove the meter number and unit if has_meter_reading passes from true to false', function () {

    $asset = Asset::factory()->withMaintainableData()->forLocation($this->room)->create(['has_meter_readings' => true, 'meter_number' => '358412326dad', 'meter_unit' => 'kWh',]);

    assertDatabaseHas(
        'assets',
        [
            'id' => $asset->id,
            'has_meter_readings' => true,
            'meter_unit' => 'kWh',
            'meter_number' => '358412326dad'
        ]
    );

    $formData = [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'has_meter_readings' => false,
        'meter_number' => '0235845568',
        'meter_unit' => 'kWh',
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
            'meter_number' => null,
            'meter_unit' => null,
        ]
    );
});

it('can add a new meter reading for an asset', function () {

    $asset = Asset::factory()->withMaintainableData()->forLocation($this->room)->create();

    $formData = [
        'meter' => 1234.24,
        'meter_date' => Carbon::now()->toDateString(),
        'notes' => 'Vérifier le mois prochain'
    ];
    $response = $this->postToTenant('api.meter-readings.store', $formData,  $asset);
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

it('can update a meter reading', function () {

    $asset = Asset::factory()->withMaintainableData()->forLocation($this->room)->create(['has_meter_readings' => true, 'meter_number' => '358412326dad', 'meter_unit' => 'kWh',]);

    $meter = new MeterReading([
        'meter' => 1669.58,
        'meter_date' => Carbon::yesterday()->toDateString(),
        'notes' => 'Check'
    ]);

    $meter->user()->associate($this->user);
    $meter->asset()->associate($asset);
    $meter->save();


    $formData = [
        'meter' => 2224.24,
        'meter_date' => Carbon::now()->toDateString(),
        'notes' => 'Vérifier le mois prochain'
    ];

    $response = $this->patchToTenant('api.meter-readings.patch', $formData, $meter);
    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);

    assertDatabaseHas(('meter_readings'), [
        'meter' => 2224.24,
        'meter_date' => Carbon::now()->toDateString(),
        'notes' => 'Vérifier le mois prochain',
        'asset_id' => $asset->id,
        'user_id' => $this->user->id
    ]);
});

it('cannot add a new meter that is smaller than previous meter', function () {

    $asset = Asset::factory()->withMaintainableData()->forLocation($this->room)->create(['has_meter_readings' => true, 'meter_number' => '358412326dad', 'meter_unit' => 'kWh',]);

    $meter = new MeterReading([
        'meter' => 1669.58,
        'meter_date' => Carbon::yesterday()->toDateString(),
        'notes' => 'Check'
    ]);

    $meter->user()->associate($this->user);
    $meter->asset()->associate($asset);
    $meter->save();


    $formData = [
        'meter' => 1234.24,
        'meter_date' => Carbon::now()->toDateString(),
        'notes' => 'Vérifier le mois prochain'
    ];

    $response = $this->postToTenant('api.meter-readings.store', $formData, $asset);
    $response->assertStatus(400)
        ->assertJson(['status' => 'error']);
});

it('can delete a meter reading', function () {

    $asset = Asset::factory()->withMaintainableData()->forLocation($this->room)->create(['has_meter_readings' => true, 'meter_number' => '358412326dad', 'meter_unit' => 'kWh',]);

    $meter = new MeterReading([
        'meter' => 1669.58,
        'meter_date' => Carbon::yesterday()->toDateString(),
        'notes' => 'Check'
    ]);

    $meter->user()->associate($this->user);
    $meter->asset()->associate($asset);
    $meter->save();

    $response = $this->deleteFromTenant('api.meter-readings.delete', $meter);
    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);

    assertDatabaseMissing(('meter_readings'), [
        'id' => $meter->id,
        'meter' => 1669.58,
        'meter_date' => Carbon::yesterday()->toDateString(),
        'notes' => 'Check',
        'asset_id' => $asset->id,
        'user_id' => $this->user->id
    ]);
});
