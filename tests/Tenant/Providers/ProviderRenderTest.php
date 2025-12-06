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

    $this->site = Site::factory()->create();
    $this->building = Building::factory()->create();
    $this->floor = Floor::factory()->create();
    $this->room = Room::factory()->create();

    $this->assetSite = Asset::factory()->forLocation($this->site)->create();
    $this->assetBuilding = Asset::factory()->forLocation($this->building)->create();
    $this->assetFloor = Asset::factory()->forLocation($this->floor)->create();
    $this->assetRoom = Asset::factory()->forLocation($this->room)->create();
});


it('can render the index providers page', function () {
    Provider::factory()->count(3)->create();
    $response = $this->getFromTenant('tenant.providers.index');
    $response->assertOk()
        ->assertInertia(
            fn($page) =>
            $page->component('tenants/providers/IndexProviders')
                ->has('items.data', 3)
        );
});

it('can render the show provider page', function () {
    $provider = Provider::factory()->create();
    $response = $this->getFromTenant('tenant.providers.show', $provider);
    $response->assertOk()
        ->assertInertia(
            fn($page) =>
            $page->component('tenants/providers/ShowProvider')
                ->has('item')
                ->where('item.id', $provider->id)
        );
});

it('can render the create provider page', function () {
    $response = $this->getFromTenant('tenant.providers.create');
    $response->assertOk()
        ->assertInertia(
            fn($page) =>
            $page->component('tenants/providers/CreateUpdateProvider')
        );
});


it('can render the edit provider page', function () {
    $provider = Provider::factory()->create();
    $response = $this->getFromTenant('tenant.providers.edit', $provider);
    $response->assertOk()
        ->assertInertia(
            fn($page) =>
            $page->component('tenants/providers/CreateUpdateProvider')
                ->has('provider')
                ->where('provider.id', $provider->id)
        );
});
