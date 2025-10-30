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
    $this->site = Site::factory()->create();
    $this->building = Building::factory()->create();
    $this->floor = Floor::factory()->create();
    $this->room = Room::factory()->create();
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
