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

    LocationType::factory()->create(['level' => 'site']);
    LocationType::factory()->create(['level' => 'building']);
    LocationType::factory()->create(['level' => 'floor']);
    LocationType::factory()->create(['level' => 'room']);
    CategoryType::factory()->count(2)->create(['category' => 'document']);
    $this->categoryType = CategoryType::factory()->create(['category' => 'asset']);
    CategoryType::factory()->count(2)->create(['category' => 'asset']);
    $this->site = Site::factory()->create();
    $this->building = Building::factory()->create();
    $this->floor = Floor::factory()->create();

    $this->room = Room::factory()
        ->for(LocationType::where('level', 'room')->first())
        ->for(Floor::first())
        ->create();
});

it('can render the index assets page', function () {

    $asset = Asset::factory()->forLocation($this->site)->create();
    Asset::factory()->forLocation($this->building)->create();
    Asset::factory()->forLocation($this->floor)->create();
    Asset::factory()->forLocation($this->room)->create();

    $response = $this->getFromTenant('tenant.assets.index');
    $response->assertOk();

    $asset = Asset::find($asset->id);

    $response->assertInertia(
        fn($page) =>
        $page->component('tenants/assets/IndexAssets')
            ->has('items', 4)
            ->where('items.0.maintainable.name', $asset->maintainable->name)
            ->where('items.0.location.id', $this->site->id)
            ->where('items.0.category', $asset->assetCategory->label)
            ->where('items.0.location_type', get_class($this->site))
            ->where('items.0.location_id', $this->site->id)
    );
});

it('can render the create asset page', function () {

    $response = $this->getFromTenant('tenant.assets.create');
    $response->assertOk();

    $response->assertInertia(
        fn($page) => $page->component('tenants/assets/CreateUpdateAsset')
            ->has('categories', 3)
    );
    $response->assertOk();
});

it('can create a new asset to site', function () {

    $formData = [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'depreciable' => true,
        'depreciation_start_date' => Carbon::now(),
        'depreciation_end_date' => Carbon::now()->addYear(3),
        'depreciation_duration' => 3,
        'residual_value' => 1250.69,
        'locationId' => $this->site->id,
        'locationType' => 'site',
        'locationReference' => $this->site->reference_code,
        'surface' => 12,
        'categoryId' => $this->categoryType->id,
        'need_maintenance' => false,
        'maintenance_manager_id' => $this->manager->id,
    ];

    $response = $this->postToTenant('api.assets.store', $formData);
    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);


    $asset = Asset::first();

    assertDatabaseCount('assets', 1);

    assertDatabaseHas('assets', [
        'code' => 'A0001',
        'reference_code' => $this->site->reference_code . '-' . 'A0001',
        'location_type' => get_class($this->site),
        'location_id' => $this->site->id,
        'category_type_id' => $this->categoryType->id,
        'surface' => 12,
        'depreciation_start_date' => Carbon::now()->toDateString(),
        'depreciation_end_date' => Carbon::now()->addYear(3)->toDateString(),
        'depreciation_duration' => 3,
        'residual_value' => 1250.69,
    ]);

    assertDatabaseHas('maintainables', [
        'maintenance_manager_id' => $this->manager->id,
        'maintainable_type' => get_class($asset),
        'maintainable_id' => $asset->id,
        'name' => 'New asset',
        'description' => 'Description new asset',
    ]);
});


it('can create a new mobile asset', function () {

    $formData = [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'is_mobile' => true,
        'locationId' => $this->user->id,
        'locationType' => 'user',
        'surface' => 12,
        'categoryId' => $this->categoryType->id,
        'need_maintenance' => false
    ];

    $response = $this->postToTenant('api.assets.store', $formData);
    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);


    $asset = Asset::first();

    assertDatabaseCount('assets', 1);

    assertDatabaseHas('assets', [
        'code' => 'A0001',
        'reference_code' => 'A0001',
        'location_type' => get_class($this->user),
        'location_id' => $this->user->id,
        'category_type_id' => $this->categoryType->id,
        'surface' => 12,
    ]);

    assertDatabaseHas('maintainables', [
        'maintainable_type' => get_class($asset),
        'maintainable_id' => $asset->id,
        'name' => 'New asset',
        'description' => 'Description new asset',
    ]);
});


it('can create a new asset to building', function () {

    $formData = [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'locationId' => $this->building->id,
        'locationReference' => $this->building->reference_code,
        'locationType' => 'building',
        'categoryId' => $this->categoryType->id,
        'need_maintenance' => false
    ];

    $response = $this->postToTenant('api.assets.store', $formData);
    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);
    $asset = Asset::first();

    assertDatabaseCount('assets', 1);

    assertDatabaseHas('assets', [
        'code' => 'A0001',
        'reference_code' => $this->building->reference_code . '-' . 'A0001',
        'location_type' => get_class($this->building),
        'location_id' => $this->building->id,
        'category_type_id' => $this->categoryType->id
    ]);

    assertDatabaseHas('maintainables', [
        'maintainable_type' => get_class($asset),
        'maintainable_id' => $asset->id,
        'name' => 'New asset',
        'description' => 'Description new asset',
    ]);
});

it('can create an asset with uploaded pictures', function () {

    $file1 = UploadedFile::fake()->image('avatar.png');
    $file2 = UploadedFile::fake()->image('test.jpg');

    $formData = [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'locationId' => $this->building->id,
        'locationReference' => $this->building->reference_code,
        'locationType' => 'building',
        'categoryId' => $this->categoryType->id,
        'pictures' => [
            $file1,
            $file2
        ]
    ];

    $response = $this->postToTenant('api.assets.store', $formData);
    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);
    assertDatabaseCount('pictures', 2);
    assertDatabaseHas('pictures', [
        'imageable_type' => 'App\Models\Tenants\Asset',
        'imageable_id' => 1
    ]);
});

it('can add pictures to an asset', function () {
    $this->asset = Asset::factory()->forLocation($this->room)->create();
    $file1 = UploadedFile::fake()->image('avatar.png');
    $file2 = UploadedFile::fake()->image('test.jpg');

    $formData = [
        'pictures' => [
            $file1,
            $file2
        ]
    ];

    $response = $this->postToTenant('api.assets.pictures.post', $formData, $this->asset);
    $response->assertSessionHasNoErrors();
    assertDatabaseCount('pictures', 2);
    assertDatabaseHas('pictures', [
        'imageable_type' => 'App\Models\Tenants\Asset',
        'imageable_id' => 1
    ]);
});

it('cannot create a new asset with non existing building', function () {

    $formData = [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'locationId' => 2,
        'locationReference' => $this->building->reference_code,
        'locationType' => 'building',
        'categoryId' => $this->categoryType->id,
    ];

    $response = $this->postToTenant('api.assets.store', $formData);
    $response->assertSessionHasErrors([
        'locationId' => 'The selected location id is invalid.'
    ]);
});

it('cannot create a new asset with unrelated asset category type', function () {

    $formData = [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'locationId' => $this->building->id,
        'locationReference' => $this->building->reference_code,
        'locationType' => 'building',
        'categoryId' => 2,
    ];

    $response = $this->postToTenant('api.assets.store', $formData);
    $response->assertSessionHasErrors([
        'categoryId' => 'The selected category id is invalid.'
    ]);
});

it('cannot create a new asset with non existing location type', function () {
    $formData = [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'locationId' => $this->building->id,
        'locationReference' => $this->building->id,
        'locationType' => 'test',
        'categoryId' => $this->categoryType->id,
    ];

    $response = $this->postToTenant('api.assets.store', $formData);
    $response->assertSessionHasErrors([
        'locationType' => 'The selected location type is invalid.'
    ]);
});

it('can create a new asset to floor', function () {

    $formData = [
        'name' => 'New asset',
        'surface' => 12,
        'description' => 'Description new asset',
        'locationId' => $this->floor->id,
        'locationReference' => $this->floor->reference_code,
        'locationType' => 'floor',
        'categoryId' => $this->categoryType->id,
    ];

    $response = $this->postToTenant('api.assets.store', $formData);
    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);

    $asset = Asset::first();

    assertDatabaseCount('assets', 1);

    assertDatabaseHas('assets', [
        'code' => 'A0001',
        'reference_code' => $this->floor->reference_code . '-' . 'A0001',
        'location_type' => get_class($this->floor),
        'location_id' => $this->floor->id,
        'surface' => 12,
        'category_type_id' => $this->categoryType->id,
    ]);

    assertDatabaseHas('maintainables', [
        'maintainable_type' => get_class($asset),
        'maintainable_id' => $asset->id,
        'name' => 'New asset',
        'description' => 'Description new asset',
    ]);
});

it('can create a new asset to room', function () {

    $formData = [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'surface' => 12.40,
        'locationId' => $this->room->id,
        'locationReference' => $this->room->reference_code,
        'locationType' => 'room',
        'model' => 'Blue daba di daba da',
        'brand' => 'Alpine',
        'serial_number' => '123-AZ-65-XF',
        'categoryId' => $this->categoryType->id,
    ];

    $response = $this->postToTenant('api.assets.store', $formData);
    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);

    $asset = Asset::first();

    assertDatabaseCount('assets', 1);

    assertDatabaseHas('assets', [
        'code' => 'A0001',
        'reference_code' => $this->room->reference_code . '-' . 'A0001',
        'surface' => 12.40,
        'location_type' => get_class($this->room),
        'location_id' => $this->room->id,
        'model' => 'Blue daba di daba da',
        'brand' => 'Alpine',
        'serial_number' => '123-AZ-65-XF',
        'category_type_id' => $this->categoryType->id,
    ]);

    assertDatabaseHas('maintainables', [
        'maintainable_type' => get_class($asset),
        'maintainable_id' => $asset->id,
        'name' => 'New asset',
        'description' => 'Description new asset',
    ]);
});


it('can show the asset page', function () {

    $asset = Asset::factory()->forLocation($this->room)->create();

    $asset = Asset::find($asset->id);

    $response = $this->getFromTenant('tenant.assets.show', $asset);

    $response->assertInertia(
        fn($page) => $page->component('tenants/assets/ShowAsset')
            ->has('item')
            ->where('item.location.code', $this->room->code)
            ->where('item.maintainable.description', $asset->maintainable->description)
            ->where('item.code', $asset->code)
            ->where('item.category', $this->categoryType->label)
            ->where('item.reference_code', $asset->reference_code)
            ->where('item.location_type', get_class($this->room))
    );
});

it('can render the update asset page', function () {

    $asset = Asset::factory()->forLocation($this->room)->create();

    $response = $this->getFromTenant('tenant.assets.edit', $asset);

    $response->assertInertia(
        fn($page) => $page->component('tenants/assets/CreateUpdateAsset')
            ->has('asset')
            ->where('asset.reference_code', $asset->reference_code)
            ->where('asset.location_type', get_class($this->room))
    );
});

it('can update asset and his maintainable', function () {

    $asset = Asset::factory()->forLocation($this->room)->create();
    $asset = Asset::find($asset->id);

    $oldName = $asset->maintainable->name;
    $oldDescription = $asset->maintainable->description;

    $formData = [
        'depreciable' => true,
        'depreciation_start_date' => Carbon::now()->toDateString(),
        'depreciation_end_date' => Carbon::now()->addYear(3)->toDateString(),
        'depreciation_duration' => 3,
        'residual_value' => 1250.69,
        'name' => "New asset name",
        'description' => "New asset description",
        'categoryId' => $asset->assetCategory->id,

    ];

    $response = $this->patchToTenant('api.assets.update', $formData, $asset);
    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);

    assertDatabaseHas('assets', [
        'id' => $asset->id,
        'depreciable' => true,
        'depreciation_start_date' => Carbon::now()->toDateString(),
        'depreciation_end_date' => Carbon::now()->addYear(3)->toDateString(),
        'depreciation_duration' => 3,
        'residual_value' => 1250.69,
    ]);
    assertDatabaseHas('maintainables', [
        'name' => "New asset name",
        'description' => "New asset description",
        'maintainable_type' => get_class($asset),
        'maintainable_id' => $asset->id
    ]);

    assertDatabaseMissing('maintainables', [
        'name' => $oldName,
        'description' => $oldDescription
    ]);
});

it('can update asset\'s location', function () {

    $asset = Asset::factory()->forLocation($this->room)->create();

    $asset = Asset::find($asset->id);

    $name = $asset->maintainable->name;
    $description = $asset->maintainable->description;
    $oldReference = $asset->reference_code;

    $formData = [
        'name' => $name,
        'surface' => 12.2,
        'locationId' => $this->floor->id,
        'locationReference' => $this->floor->reference_code,
        'locationType' => 'floor',
        'categoryId' => $asset->assetCategory->id,
    ];

    $response = $this->patchToTenant('api.assets.update', $formData, $asset);
    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);

    assertDatabaseHas('assets', [
        'code' => 'A0001',
        'surface' => 12.2,
        'reference_code' => $this->floor->reference_code . '-A0001',
        'location_type' => get_class($this->floor),
        'location_id' => $this->floor->id
    ]);

    assertDatabaseMissing('assets', [
        'reference_code' => $oldReference,
        'location_type' => get_class($this->room),
        'location_id' => $this->room->id
    ]);

    assertDatabaseHas('maintainables', [
        'name' => $name,
        'description' => $description,
        'maintainable_type' => get_class($asset),
        'maintainable_id' => $asset->id
    ]);
});

it('can soft delete an asset but kept in DB with his maintainable', function () {

    $asset = Asset::factory()->forLocation($this->room)->create();
    $asset = Asset::find($asset->id);

    assertDatabaseHas('maintainables', [
        'name' => $asset->maintainable->name,
        'description' => $asset->maintainable->description,
        'maintainable_type' => get_class($asset),
        'maintainable_id' => $asset->id
    ]);

    $response = $this->deleteFromTenant('api.assets.destroy', $asset);
    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);

    $this->assertSoftDeleted('assets', [
        'reference_code' => $this->room->reference_code . '-A0001',
        'code' => 'A0001',
    ]);

    assertDatabaseHas('maintainables', [
        'name' => $asset->maintainable->name,
        'description' => $asset->maintainable->description,
        'maintainable_type' => get_class($asset),
        'maintainable_id' => $asset->id
    ]);
});

it('can restore a soft deleted asset', function () {
    $asset = Asset::factory()->forLocation($this->room)->create();
    $asset = Asset::find($asset->id);
    $response = $this->deleteFromTenant('api.assets.destroy', $asset);
    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);

    $this->assertSoftDeleted('assets', [
        'reference_code' => $this->room->reference_code . '-A0001',
        'code' => 'A0001',
    ]);

    $response = $this->postToTenant('api.assets.restore', [], $asset->reference_code);
    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);
    $this->assertNull($asset->deleted_at);

    assertDatabaseHas('assets', [
        'code' => 'A0001',
        'reference_code' => $this->room->reference_code . '-A0001',
        'location_type' => get_class($this->room),
        'location_id' => $this->room->id
    ]);

    assertDatabaseHas('maintainables', [
        'name' => $asset->maintainable->name,
        'description' => $asset->maintainable->description,
        'maintainable_type' => get_class($asset),
        'maintainable_id' => $asset->id
    ]);
});

it('can force delete a soft deleted asset', function () {
    $asset = Asset::factory()->forLocation($this->room)->create();
    $asset = Asset::find($asset->id);

    $assetName = $asset->maintainable->name;
    $assetDescription = $asset->maintainable->description;
    $assetId = $asset->id;

    $response = $this->deleteFromTenant('api.assets.destroy', $asset);
    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);

    $this->assertSoftDeleted('assets', [
        'reference_code' => $this->room->reference_code . '-A0001',
        'code' => 'A0001',
    ]);
    $this->assertNull($asset->deleted_at);

    $response = $this->deleteFromTenant('api.assets.force', $asset->reference_code);
    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);
    assertDatabaseEmpty('assets');

    assertDatabaseMissing('maintainables', [
        'name' => $assetName,
        'description' => $assetDescription,
        'maintainable_id' => $assetId
    ]);
});

it('fails when model has more than 100 chars', function () {


    $formData = [
        'name' => fake()->text(50),
        'description' => fake()->text(250),
        'locationId' => $this->site->id,
        'locationReference' => $this->site->reference_code,
        'locationType' => 'site',
        'categoryId' => $this->categoryType->id,
        'model' => str_repeat('A', 101)
    ];

    $response = $this->postToTenant('api.assets.store', $formData);
    $response->assertSessionHasErrors([
        'model' => 'The model field must not be greater than 100 characters.',
    ]);
});

it('fails when brand has more than 100 chars', function () {

    $formData = [
        'name' => fake()->text(50),
        'description' => fake()->text(250),
        'locationId' => $this->site->id,
        'locationReference' => $this->site->reference_code,
        'locationType' => 'site',
        'categoryId' => $this->categoryType->id,
        'brand' => str_repeat('A', 101)
    ];

    $response = $this->postToTenant('api.assets.store', $formData);
    $response->assertSessionHasErrors([
        'brand' => 'The brand field must not be greater than 100 characters.',
    ]);
});

it('fails when serial_number has more than 50 chars', function () {


    $formData = [
        'name' => fake()->text(50),
        'description' => fake()->text(250),
        'locationId' => $this->site->id,
        'locationReference' => $this->site->reference_code,
        'locationType' => 'site',
        'categoryId' => $this->categoryType->id,
        'serial_number' => str_repeat('A', 51)
    ];

    $response = $this->postToTenant('api.assets.store', $formData);
    $response->assertSessionHasErrors([
        'serial_number' => 'The serial number field must not be greater than 50 characters.',
    ]);
});


it('can attach a provider to an asset\'s maintainable', function () {
    CategoryType::factory()->create(['category' => 'provider']);
    $provider = Provider::factory()->create();

    $formData = [
        'name' => fake()->text(50),
        'description' => fake()->text(250),
        'locationId' => $this->site->id,
        'locationReference' => $this->site->reference_code,
        'locationType' => 'site',
        'categoryId' => $this->categoryType->id,
        'purchase_cost' => 9999999.2,
        'providers' => [['id' => $provider->id]]
    ];

    $response = $this->postToTenant('api.assets.store', $formData);
    $response->assertSessionHasNoErrors();

    $asset = Asset::first();
    assertCount(1, $asset->maintainable->providers);
});


it('can update providers to an asset\'s maintainable', function () {
    CategoryType::factory()->create(['category' => 'provider']);
    Provider::factory()->count(3)->create();
    $providers = Provider::all()->pluck('id');
    $asset = Asset::factory()->forLocation($this->room)->create();
    $asset = Asset::find($asset->id);

    $formData = [
        'name' => "New asset name",
        'description' => "New asset description",
        'categoryId' => $asset->assetCategory->id,
        'providers' => [['id' => $providers[0]], ['id' => $providers[1]], ['id' => $providers[2]],]
    ];

    $response = $this->patchToTenant('api.assets.update', $formData, $asset);
    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);

    $asset = Asset::find($asset->id);
    assertCount(3, $asset->maintainable->providers);
});
