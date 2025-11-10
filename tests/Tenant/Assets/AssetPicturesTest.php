<?php

use Carbon\Carbon;
use App\Models\LocationType;
use App\Models\Tenants\Room;
use App\Models\Tenants\Site;
use App\Models\Tenants\User;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Floor;
use App\Models\Tenants\Company;

use App\Models\Tenants\Building;
use App\Models\Tenants\Provider;
use Illuminate\Http\UploadedFile;
use App\Models\Central\CategoryType;
use Illuminate\Support\Facades\Queue;
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


it('can create an asset with uploaded pictures and increment disk size', function () {

    Queue::fake();
    $file1 = UploadedFile::fake()->image('avatar.png')->size(1500);
    $file2 = UploadedFile::fake()->image('test.jpg')->size(500);

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

    $pictures = Asset::first()->pictures;

    foreach ($pictures as $picture) {
        expect(Storage::disk('tenants')->exists($picture->path))->toBeTrue();
    }


    assertEquals(round(Company::first()->disk_size / 1024), 2000);
});

it('can add pictures to an asset', function () {
    Queue::fake();
    $asset = Asset::factory()->forLocation($this->room)->create();
    $file1 = UploadedFile::fake()->image('avatar.png')->size(1000);
    $file2 = UploadedFile::fake()->image('test.jpg')->size(1000);

    $formData = [
        'pictures' => [
            $file1,
            $file2
        ]
    ];

    $response = $this->postToTenant('api.assets.pictures.post', $formData, $asset);
    $response->assertSessionHasNoErrors();
    assertDatabaseCount('pictures', 2);
    assertDatabaseHas('pictures', [
        'imageable_type' => 'App\Models\Tenants\Asset',
        'imageable_id' => 1
    ]);

    $pictures = Asset::first()->pictures;

    foreach ($pictures as $picture) {
        expect(Storage::disk('tenants')->exists($picture->path))->toBeTrue();
    }

    assertEquals(round(Company::first()->disk_size / 1024), 2000);
});

it('does not delete pictures when asset is soft deleted', function () {
    $asset = Asset::factory()->forLocation($this->room)->create();
    $file1 = UploadedFile::fake()->image('avatar.png')->size(1000);
    $file2 = UploadedFile::fake()->image('test.jpg')->size(1000);

    $formData = [
        'pictures' => [
            $file1,
            $file2
        ]
    ];

    $response = $this->postToTenant('api.assets.pictures.post', $formData, $asset);
    $response->assertSessionHasNoErrors();
    assertDatabaseCount('pictures', 2);
    assertDatabaseHas('pictures', [
        'imageable_type' => 'App\Models\Tenants\Asset',
        'imageable_id' => 1
    ]);

    $pictures = Asset::first()->pictures;

    foreach ($pictures as $picture) {
        expect(Storage::disk('tenants')->exists($picture->path))->toBeTrue();
    }

    $response = $this->deleteFromTenant('api.assets.destroy', $asset->reference_code);

    foreach ($pictures as $picture) {
        expect(Storage::disk('tenants')->exists($picture->path))->toBeTrue();
    }
});

it('deletes pictures and directory when asset is force deleted', function () {
    Queue::fake();
    $asset = Asset::factory()->forLocation($this->room)->create();
    $file1 = UploadedFile::fake()->image('avatar.png')->size(1000);
    $file2 = UploadedFile::fake()->image('test.jpg')->size(1000);

    $formData = [
        'pictures' => [
            $file1,
            $file2
        ]
    ];

    $response = $this->postToTenant('api.assets.pictures.post', $formData, $asset);
    $response->assertSessionHasNoErrors();
    assertDatabaseCount('pictures', 2);
    assertDatabaseHas('pictures', [
        'imageable_type' => 'App\Models\Tenants\Asset',
        'imageable_id' => 1
    ]);

    $pictures = Asset::first()->pictures;

    foreach ($pictures as $picture) {
        expect(Storage::disk('tenants')->exists($picture->path))->toBeTrue();
    }

    $response = $this->deleteFromTenant('api.assets.force', $asset->reference_code);

    assertDatabaseCount('pictures', 0);
    assertDatabaseMissing('pictures', [
        'imageable_type' => 'App\Models\Tenants\Asset',
        'imageable_id' => 1
    ]);

    assertDatabaseMissing('pictures', [
        'imageable_type' => 'App\Models\Tenants\Asset',
        'imageable_id' => 2
    ]);

    foreach ($pictures as $picture) {
        expect(Storage::disk('tenants')->exists($picture->path))->toBeFalse();
    }

    assertEquals(round(Company::first()->disk_size / 1024), 0);

    Storage::disk('tenants')->assertMissing($asset->directory);
});
