<?php

use App\Models\Tenants\Room;

use App\Models\Tenants\Site;
use App\Models\Tenants\User;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Floor;
use App\Models\Tenants\Company;
use App\Models\Tenants\Country;
use App\Models\Tenants\Building;
use App\Models\Tenants\Provider;
use Illuminate\Http\UploadedFile;
use App\Models\Central\CategoryType;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use function Pest\Laravel\assertDatabaseHas;
use function PHPUnit\Framework\assertEquals;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;

beforeEach(function () {
    $this->user = User::factory()->withRole('Admin')->create();
    $this->actingAs($this->user, 'tenant');
    $this->categoryType = CategoryType::factory()->create(['category' => 'provider']);


    CategoryType::factory()->count(2)->create(['category' => 'document']);

    $this->site = Site::factory()->withMaintainableData()->create();
    $this->building = Building::factory()->create();
    $this->floor = Floor::factory()->withMaintainableData()->create();
    $this->room = Room::factory()->withMaintainableData()->create();

    $this->assetSite = Asset::factory()->withMaintainableData()->forLocation($this->site)->create();
    $this->assetBuilding = Asset::factory()->withMaintainableData()->forLocation($this->building)->create();
    $this->assetFloor = Asset::factory()->withMaintainableData()->forLocation($this->floor)->create();
    $this->assetRoom = Asset::factory()->withMaintainableData()->forLocation($this->room)->create();
});

it('can retrieve all assets linked to a provider', function () {

    $provider = Provider::factory()->create();

    $this->assetRoom->refresh();
    $this->assetSite->refresh();

    $provider->maintainables()->sync([$this->assetRoom->maintainable, $this->assetSite->maintainable]);

    $response = $this->getFromTenant('api.providers.assets', $provider);
    $response->assertJsonCount(2, 'data.data');
});

it('can retrieve all locations linked to a provider', function () {

    $provider = Provider::factory()->create();

    $this->site->refresh();
    $this->floor->refresh();

    $provider->maintainables()->sync([$this->site->maintainable, $this->floor->maintainable]);

    $response = $this->getFromTenant('api.providers.locations', $provider);
    $response->assertJsonCount(2, 'data.data');
});
