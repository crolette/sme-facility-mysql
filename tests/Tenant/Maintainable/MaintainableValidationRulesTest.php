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

it('fails when name is more than 100 chars', function () {
    $formData = [
        'name' => str_repeat('A', 101),
        'description' => 'Description new asset',
        'locationId' => $this->site->id,
        'locationReference' => $this->site->reference_code,
        'locationType' => 'site',
        'categoryId' => $this->category->id,
    ];

    $response = $this->postToTenant('api.assets.store', $formData);

    $response->assertSessionHasErrors([
        'name' => 'The name field must not be greater than 100 characters.',
    ]);
});

it('fails when description is more than 255 chars', function () {
    $formData = [
        'name' => fake()->text(50),
        'description' => str_repeat('A', 256),
        'locationId' => $this->site->id,
        'locationReference' => $this->site->reference_code,
        'locationType' => 'site',
        'categoryId' => $this->category->id,
    ];

    $response = $this->postToTenant('api.assets.store', $formData);

    $response->assertSessionHasErrors([
        'description' => 'The description field must not be greater than 255 characters.',
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

it('passes when purchase_cost has max 2 decimals and 7 digits', function () {
    $formData = [
        'name' => fake()->text(50),
        'description' => fake()->text(250),
        'locationId' => $this->site->id,
        'locationReference' => $this->site->reference_code,
        'locationType' => 'site',
        'categoryId' => $this->category->id,
        'purchase_cost' => 9999999.2
    ];

    $response = $this->postToTenant('api.assets.store', $formData);
    $response->assertSessionHasNoErrors();
    $asset = Asset::first();
    $this->assertNotNull($asset->maintainable->purchase_cost);
});

it('fails when purchase_cost is negative', function () {
    $formData = [
        'name' => fake()->text(50),
        'description' => fake()->text(250),
        'locationId' => $this->site->id,
        'locationReference' => $this->site->reference_code,
        'locationType' => 'site',
        'categoryId' => $this->category->id,
        'purchase_cost' => -10
    ];

    $response = $this->postToTenant('api.assets.store', $formData);
    $response->assertSessionHasErrors([
        'purchase_cost' => 'The purchase cost field must be greater than 0.',
    ]);
});

it('fails when purchase_cost has more than 2 decimals', function () {
    $formData = [
        'name' => fake()->text(50),
        'description' => fake()->text(250),
        'locationId' => $this->site->id,
        'locationReference' => $this->site->reference_code,
        'locationType' => 'site',
        'categoryId' => $this->category->id,
        'purchase_cost' => 123456.123
    ];

    $response = $this->postToTenant('api.assets.store', $formData);
    $response->assertSessionHasErrors([
        'purchase_cost' => 'The purchase cost field must have 0-2 decimal places.',
    ]);
});

it('passes when purchase_date is equal today', function () {
    $formData = [
        'name' => fake()->text(50),
        'description' => fake()->text(250),
        'locationId' => $this->site->id,
        'locationReference' => $this->site->reference_code,
        'locationType' => 'site',
        'categoryId' => $this->category->id,
        'purchase_date' => Carbon::now()->toDateString()
    ];

    $response = $this->postToTenant('api.assets.store', $formData);
    $response->assertSessionHasNoErrors();
    $asset = Asset::first();
    $this->assertNotNull($asset->maintainable->purchase_date);
});

it('fails when purchase_date is after today', function () {
    $formData = [
        'name' => fake()->text(50),
        'description' => fake()->text(250),
        'locationId' => $this->site->id,
        'locationReference' => $this->site->reference_code,
        'locationType' => 'site',
        'categoryId' => $this->category->id,
        'purchase_date' => Carbon::now()->add(1, 'month')->toDateString()
    ];

    $response = $this->postToTenant('api.assets.store', $formData);
    $response->assertSessionHasErrors([
        'purchase_date' => 'The purchase date field must be a date before or equal to today.',
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
