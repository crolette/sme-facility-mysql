<?php

use App\Enums\MaintenanceFrequency;
use Carbon\Carbon;
use App\Models\LocationType;
use App\Models\Tenants\Room;
use App\Models\Tenants\Site;
use App\Models\Tenants\User;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Floor;
use App\Models\Tenants\Building;
use App\Models\Central\CategoryType;
use App\Models\Central\AssetCategory;
use App\Models\Tenants\Provider;

use function Pest\Laravel\assertDatabaseHas;
use function PHPUnit\Framework\assertEquals;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseMissing;
use function PHPUnit\Framework\assertCount;

beforeEach(function () {
    $this->user = User::factory()->withRole('Admin')->create();
    $this->actingAs($this->user, 'tenant');

    $this->siteType = LocationType::factory()->create(['level' => 'site']);
    $this->buildingType = LocationType::factory()->create(['level' => 'building']);
    $this->floorType = LocationType::factory()->create(['level' => 'floor']);
    $this->roomType = LocationType::factory()->create(['level' => 'room']);
    $this->category = CategoryType::factory()->create(['category' => 'asset']);

    $this->site = Site::factory()->create();
    $this->building = Building::factory()->create();
    $this->floor = Floor::factory()->create();

    $this->room = Room::factory()
        ->for(LocationType::where('level', 'room')->first())
        ->for(Floor::first())
        ->create();
});

it('can create with under_warranty to true', function () {
    $formData = [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'locationId' => $this->site->id,
        'locationReference' => $this->site->reference_code,
        'locationType' => 'site',
        'categoryId' => $this->category->id,
        'under_warranty' => true,
        // 'purchase_date' => Carbon::now()->subMonths(2)->toDateString(),
        'end_warranty_date' => Carbon::now()->addMonths(2)->toDateString(),
    ];

    $response = $this->postToTenant('api.assets.store', $formData);
    $response->assertStatus(200);
    $response->assertSessionHasNoErrors();

    $asset = Asset::first();

    assertDatabaseHas('maintainables', [
        'maintainable_type' => get_class($asset),
        'maintainable_id' => $asset->id,
        'name' => 'New asset',
        'description' => 'Description new asset',
        'under_warranty' => true,
        'end_warranty_date' => Carbon::now()->addMonths(2)->toDateString(),
    ]);
});

it('can update end_warranty_date ', function () {
    $formData = [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'locationId' => $this->site->id,
        'locationReference' => $this->site->reference_code,
        'locationType' => 'site',
        'categoryId' => $this->category->id,
        'under_warranty' => true,
        'end_warranty_date' => Carbon::now()->addMonths(2)->toDateString(),
    ];

    $response = $this->postToTenant('api.assets.store', $formData);
    $response->assertStatus(200);
    $response->assertSessionHasNoErrors();

    $asset = Asset::first();

    $formData = [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'locationId' => $this->site->id,
        'locationReference' => $this->site->reference_code,
        'locationType' => 'site',
        'categoryId' => $this->category->id,
        'under_warranty' => true,
        'end_warranty_date' => Carbon::now()->addYears(2)->toDateString(),
    ];

    $response = $this->patchToTenant('api.assets.update', $formData, $asset->reference_code);
    $response->assertStatus(200);
    $response->assertSessionHasNoErrors();

    assertDatabaseHas('maintainables', [
        'maintainable_type' => get_class($asset),
        'maintainable_id' => $asset->id,
        'name' => 'New asset',
        'description' => 'Description new asset',
        'under_warranty' => true,
        'end_warranty_date' => Carbon::now()->addYears(2)->toDateString(),
    ]);
});

it('can update under_warranty from true to false', function () {
    $formData = [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'locationId' => $this->site->id,
        'locationReference' => $this->site->reference_code,
        'locationType' => 'site',
        'categoryId' => $this->category->id,
        'under_warranty' => true,
        'end_warranty_date' => Carbon::now()->addMonths(2)->toDateString(),
    ];

    $response = $this->postToTenant('api.assets.store', $formData);
    $response->assertStatus(200);
    $response->assertSessionHasNoErrors();

    $asset = Asset::first();

    $formData = [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'locationId' => $this->site->id,
        'locationReference' => $this->site->reference_code,
        'locationType' => 'site',
        'categoryId' => $this->category->id,
        'under_warranty' => false,
        'end_warranty_date' => Carbon::now()->addMonths(2)->toDateString(),
    ];

    $response = $this->patchToTenant('api.assets.update', $formData, $asset->reference_code);
    $response->assertStatus(200);
    $response->assertSessionHasNoErrors();

    assertDatabaseHas('maintainables', [
        'maintainable_type' => get_class($asset),
        'maintainable_id' => $asset->id,
        'name' => 'New asset',
        'description' => 'Description new asset',
        'under_warranty' => false,
        'end_warranty_date' => Carbon::now()->addMonths(2)->toDateString(),
    ]);
});

it('can have a end_warranty_date before today if under_warranty is false', function () {
    $formData = [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'locationId' => $this->site->id,
        'locationReference' => $this->site->reference_code,
        'locationType' => 'site',
        'categoryId' => $this->category->id,
        'under_warranty' => false,
        'end_warranty_date' => Carbon::now()->subMonths(2)->toDateString(),
    ];

    $response = $this->postToTenant('api.assets.store', $formData);
    $response->assertStatus(200);
    $response->assertSessionHasNoErrors();

    $asset = Asset::first();


    assertDatabaseHas('maintainables', [
        'maintainable_type' => get_class($asset),
        'maintainable_id' => $asset->id,
        'name' => 'New asset',
        'description' => 'Description new asset',
        'under_warranty' => false,
        'end_warranty_date' => Carbon::now()->subMonths(2)->toDateString(),
    ]);
});

it('fails when end_warranty_date is before purchase_date', function () {
    $formData = [
        'name' => fake()->text(50),
        'description' => fake()->text(250),
        'locationId' => $this->site->id,
        'locationReference' => $this->site->reference_code,
        'locationType' => 'site',
        'categoryId' => $this->category->id,
        'under_warranty' => true,
        'end_warranty_date' => Carbon::now()->subMonth()->toDateString(),
        'purchase_date' => Carbon::now()->toDateString()
    ];

    $response = $this->postToTenant('api.assets.store', $formData);
    $response->assertSessionHasErrors([
        'end_warranty_date' => 'The end warranty date field must be a date after purchase date.',
    ]);
});

it('passes when end_warranty_date is filled and under_warranty is true', function () {
    // $name = 
    $formData = [
        'name' => fake()->text(50),
        'description' => fake()->text(250),
        'locationId' => $this->site->id,
        'locationReference' => $this->site->reference_code,
        'locationType' => 'site',
        'categoryId' => $this->category->id,
        'under_warranty' => true,
        'end_warranty_date' => Carbon::now()->add(1, 'month')->toDateString()
    ];

    $response = $this->postToTenant('api.assets.store', $formData);

    $response->assertSessionHasNoErrors();
    $asset = Asset::first();
    // dump($asset);
    $this->assertNotNull($asset->maintainable->end_warranty_date);
});

it('fails when end_warranty_date is missing and under_warranty is true', function () {
    $formData = [
        'name' => fake()->text(50),
        'description' => fake()->text(250),
        'locationId' => $this->site->id,
        'locationReference' => $this->site->reference_code,
        'locationType' => 'site',
        'categoryId' => $this->category->id,
        'under_warranty' => true,
    ];

    $response = $this->postToTenant('api.assets.store', $formData);

    $response->assertSessionHasErrors([
        'end_warranty_date' => 'The end warranty date field is required when under warranty is accepted.',
    ]);
});
