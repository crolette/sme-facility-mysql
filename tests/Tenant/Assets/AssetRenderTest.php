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
    $this->site = Site::factory()->withMaintainableData()->create();
    $this->building = Building::factory()->withMaintainableData()->create();
    $this->floor = Floor::factory()->withMaintainableData()->create();
    $this->room = Room::factory()->withMaintainableData()->create();
});

it('can render the index assets page', function () {

    $asset = Asset::factory()->withMaintainableData()->forLocation($this->site)->create();
    Asset::factory()->withMaintainableData()->forLocation($this->building)->create();
    Asset::factory()->withMaintainableData()->forLocation($this->floor)->create();
    Asset::factory()->withMaintainableData()->forLocation($this->room)->create();

    $response = $this->getFromTenant('tenant.assets.index');
    $response->assertOk();

    $asset = Asset::find($asset->id);

    $response->assertInertia(
        fn($page) =>
        $page->component('tenants/assets/IndexAssets')
            ->has('items.data', 4)
            ->where('items.data.0.maintainable.name', $asset->maintainable->name)
            ->where('items.data.0.category', $asset->assetCategory->label)
            ->where('items.data.0.location_type', get_class($this->site))
            ->where('items.data.0.location_id', $this->site->id)
    );
});


it('can show the asset page', function () {

    $asset = Asset::factory()->withMaintainableData()->forLocation($this->room)->create(['category_type_id' => $this->categoryType->id]);

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

it('can render the create asset page', function () {

    $response = $this->getFromTenant('tenant.assets.create');
    $response->assertOk();

    $response->assertInertia(
        fn($page) => $page->component('tenants/assets/CreateUpdateAsset')
            ->has('categories', 3)
    );
    $response->assertOk();
});


it('can render the update asset page', function () {

    $asset = Asset::factory()->withMaintainableData()->forLocation($this->room)->create();

    $response = $this->getFromTenant('tenant.assets.edit', $asset);

    $response->assertInertia(
        fn($page) => $page->component('tenants/assets/CreateUpdateAsset')
            ->has('asset')
            ->where('asset.reference_code', $asset->reference_code)
            ->where('asset.location_type', get_class($this->room))
    );
});
