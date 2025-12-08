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
    $this->site = Site::factory()->withMaintainableData()->create();
    $this->building = Building::factory()->withMaintainableData()->create();
    $this->floor = Floor::factory()->withMaintainableData()->create();
    $this->room = Room::factory()->withMaintainableData()->create();
});


it('can create a new depreciable asset without depreciation_end_date which will be calculated automatically based on depreciation duration', function () {

    $formData = [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'depreciable' => true,
        'depreciation_start_date' => Carbon::now()->toDateString(),
        'depreciation_duration' => 3,
        'depreciation_end_date' => null,
        'residual_value' => 1250.69,
        'locationId' => $this->site->id,
        'locationType' => 'site',
        'locationReference' => $this->site->reference_code,
        'categoryId' => $this->categoryType->id,
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
        'depreciation_start_date' => Carbon::now()->toDateString(),
        'depreciation_end_date' => Carbon::now()->addYear(3)->toDateString(),
        'depreciation_duration' => 3,
        'residual_value' => 1250.69,
    ]);

    assertDatabaseHas('maintainables', [
        'maintainable_type' => get_class($asset),
        'maintainable_id' => $asset->id,
        'name' => 'New asset',
        'description' => 'Description new asset',
    ]);
});
