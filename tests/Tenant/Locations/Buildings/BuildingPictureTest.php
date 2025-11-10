<?php

use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\LocationType;
use App\Models\Tenants\Site;
use App\Models\Tenants\User;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Floor;
use App\Models\Tenants\Picture;
use App\Models\Tenants\Building;
use App\Models\Tenants\Document;
use App\Models\Tenants\Provider;
use Illuminate\Http\UploadedFile;
use App\Models\Central\CategoryType;
use Illuminate\Support\Facades\Storage;
use function Pest\Laravel\assertDatabaseHas;
use function PHPUnit\Framework\assertEquals;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseMissing;
use function PHPUnit\Framework\assertCount;


beforeEach(function () {
    $this->user = User::factory()->withRole('Admin')->create();
    $this->actingAs($this->user, 'tenant');
    LocationType::factory()->create(['level' => 'site']);
    $this->locationType = LocationType::factory()->create(['level' => 'building']);
    Site::factory()->create();
    $this->location = Building::factory()->create();
});

it('can add pictures to a building', function () {

    $file1 = UploadedFile::fake()->image('avatar.png');
    $file2 = UploadedFile::fake()->image('test.jpg');

    $formData = [
        'pictures' => [
            $file1,
            $file2
        ]
    ];

    $response = $this->postToTenant('api.buildings.pictures.post', $formData, $this->location);
    $response->assertSessionHasNoErrors();


    $picture = Picture::first();
    expect(Storage::disk('tenants')->exists($picture->directory))->toBeTrue();
    expect(Storage::disk('tenants')->exists($picture->path))->toBeTrue();

    assertDatabaseCount('pictures', 2);
    assertDatabaseHas('pictures', [
        'imageable_type' => get_class($this->location),
        'imageable_id' => 1
    ]);
});

it('deletes directory and pictures if a building is deleted', function () {

    $file1 = UploadedFile::fake()->image('avatar.png');
    $file2 = UploadedFile::fake()->image('test.jpg');

    $formData = [
        'pictures' => [
            $file1,
            $file2
        ]
    ];

    $response = $this->postToTenant('api.buildings.pictures.post', $formData, $this->location);

    $response->assertSessionHasNoErrors();

    $pictures = $this->location->pictures;
    foreach ($pictures as $picture) {
        expect(Storage::disk('tenants')->exists($picture->path))->toBeTrue();
    }

    assertDatabaseCount('pictures', 2);
    Storage::disk('tenants')->assertExists($this->location->directory);

    $this->deleteFromTenant('api.buildings.destroy', $this->location->reference_code);
    assertDatabaseCount('pictures', 0);

    Storage::disk('tenants')->assertMissing($this->location->directory);
    foreach ($pictures as $picture) {
        expect(Storage::disk('tenants')->exists($picture->path))->toBeFalse();
    }
});

it('can retrieve all pictures from a building', function () {
    Picture::factory()->forModelAndUser($this->location, $this->user, 'buildings')->create();
    Picture::factory()->forModelAndUser($this->location, $this->user, 'buildings')->create();

    $response = $this->getFromTenant('api.buildings.pictures', $this->location);
    $response->assertStatus(200);
    $data = $response->json('data');
    $this->assertCount(2, $data);
});


it('can delete a picture from a building', function () {
    $file1 = UploadedFile::fake()->image('avatar.png');
    $file2 = UploadedFile::fake()->image('test.jpg');

    $formData = [
        'pictures' => [
            $file1,
            $file2
        ]
    ];

    $response = $this->postToTenant('api.buildings.pictures.post', $formData, $this->location);
    $response->assertSessionHasNoErrors();
    assertDatabaseCount('pictures', 2);
    assertDatabaseHas('pictures', [
        'id' => 1,
        'imageable_type' => get_class($this->location),
        'imageable_id' => 1
    ]);
    assertDatabaseHas('pictures', [
        'id' => 2,
        'imageable_type' => get_class($this->location),
        'imageable_id' => 1
    ]);


    $picture = Picture::first();
    expect(Storage::disk('tenants')->exists($picture->directory))->toBeTrue();

    $response = $this->deleteFromTenant('api.pictures.delete', $picture);
    assertDatabaseCount('pictures', 1);
    assertDatabaseMissing('pictures', [
        'id' => 1,
        'imageable_type' => get_class($this->location),
        'imageable_id' => 1
    ]);
    assertDatabaseHas('pictures', [
        'id' => 2,
        'imageable_type' => get_class($this->location),
        'imageable_id' => 1
    ]);

    expect(Storage::disk('tenants')->exists($picture->path))->toBeFalse();
});

it('does not delete picture directory if directory is not empty', function () {

    $file1 = UploadedFile::fake()->image('avatar.png');
    $file2 = UploadedFile::fake()->image('test.jpg');

    $formData = [
        'pictures' => [
            $file1,
            $file2
        ]
    ];

    $response = $this->postToTenant('api.buildings.pictures.post', $formData, $this->location);
    $response->assertSessionHasNoErrors();
    assertDatabaseCount('pictures', 2);
    assertDatabaseHas('pictures', [
        'id' => 1,
        'imageable_type' => get_class($this->location),
        'imageable_id' => 1
    ]);

    $picture = Picture::first();
    expect(Storage::disk('tenants')->exists($picture->directory))->toBeTrue();

    $response = $this->deleteFromTenant('api.pictures.delete', $picture);
    assertDatabaseCount('pictures', 1);
    assertDatabaseMissing('pictures', [
        'id' => 1,
        'imageable_type' => get_class($this->location),
        'imageable_id' => 1
    ]);

    assertDatabaseHas('pictures', [
        'id' => 2,
        'imageable_type' => get_class($this->location),
        'imageable_id' => 1
    ]);

    expect(Storage::disk('tenants')->exists($picture->directory))->toBeTrue();
});

it('deletes picture directory if directory is empty', function () {

    $file1 = UploadedFile::fake()->image('avatar.png');

    $formData = [
        'pictures' => [
            $file1,
        ]
    ];

    $response = $this->postToTenant('api.buildings.pictures.post', $formData, $this->location);
    $response->assertSessionHasNoErrors();
    assertDatabaseCount('pictures', 1);
    assertDatabaseHas('pictures', [
        'id' => 1,
        'imageable_type' => get_class($this->location),
        'imageable_id' => 1
    ]);

    $picture = Picture::first();
    expect(Storage::disk('tenants')->exists($picture->directory))->toBeTrue();

    $response = $this->deleteFromTenant('api.pictures.delete', $picture);
    assertDatabaseCount('pictures', 0);
    assertDatabaseMissing('pictures', [
        'id' => 1,
        'imageable_type' => get_class($this->location),
        'imageable_id' => 1
    ]);

    expect(Storage::disk('tenants')->exists($picture->directory))->toBeFalse();
});
