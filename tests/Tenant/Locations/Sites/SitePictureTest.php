<?php

use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\LocationType;
use App\Models\Tenants\Site;
use App\Models\Tenants\User;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Floor;
use App\Models\Tenants\Company;
use App\Models\Tenants\Picture;
use App\Models\Tenants\Building;
use App\Models\Tenants\Document;
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
    $this->siteType = LocationType::factory()->create(['level' => 'site']);
});

it('can add pictures to a site', function () {
    $site = Site::factory()->create();
    $file1 = UploadedFile::fake()->image('avatar.png')->size(1000);
    $file2 = UploadedFile::fake()->image('test.jpg')->size(1200);

    $formData = [
        'pictures' => [
            $file1,
            $file2
        ]
    ];

    $response = $this->postToTenant('api.sites.pictures.post', $formData, $site);
    $response->assertSessionHasNoErrors();


    $picture = Picture::first();
    expect(Storage::disk('tenants')->exists($picture->directory))->toBeTrue();
    expect(Storage::disk('tenants')->exists($picture->path))->toBeTrue();

    assertDatabaseCount('pictures', 2);
    assertDatabaseHas('pictures', [
        'imageable_type' => 'App\Models\Tenants\Site',
        'imageable_id' => 1
    ]);
});

// it('increments disk size in company table when picture are added to a site', function () {
//     $site = Site::factory()->create();
//     $file1 = UploadedFile::fake()->image('avatar.png')->size(6144);
//     $file2 = UploadedFile::fake()->image('test.jpg')->size(6144);

//     $formData = [
//         'pictures' => [
//             $file1,
//             $file2
//         ]
//     ];

//     $response = $this->postToTenant('api.sites.pictures.post', $formData, $site);
//     $response->assertSessionHasNoErrors();

//     $company = Company::first();
//     assertEquals(round($company->disk_size / 1024), Picture::find(1)->size + Picture::find(2)->size);
// });

it('can retrieve all pictures from a site', function () {
    $site = Site::factory()->create();

    Picture::factory()->forModelAndUser($site, $this->user, 'sites')->create();
    Picture::factory()->forModelAndUser($site, $this->user, 'sites')->create();

    $response = $this->getFromTenant('api.sites.pictures', $site);
    $response->assertStatus(200);
    $data = $response->json('data');
    $this->assertCount(2, $data);
});


it('can delete a picture from a site', function () {
    $site = Site::factory()->create();
    $file1 = UploadedFile::fake()->image('avatar.png');
    $file2 = UploadedFile::fake()->image('test.jpg');

    $formData = [
        'pictures' => [
            $file1,
            $file2
        ]
    ];

    $response = $this->postToTenant('api.sites.pictures.post', $formData, $site);
    $response->assertSessionHasNoErrors();
    assertDatabaseCount('pictures', 2);
    assertDatabaseHas('pictures', [
        'id' => 1,
        'imageable_type' => 'App\Models\Tenants\Site',
        'imageable_id' => 1
    ]);
    assertDatabaseHas('pictures', [
        'id' => 2,
        'imageable_type' => 'App\Models\Tenants\Site',
        'imageable_id' => 1
    ]);


    $picture = Picture::first();
    expect(Storage::disk('tenants')->exists($picture->directory))->toBeTrue();

    $response = $this->deleteFromTenant('api.pictures.delete', $picture);
    assertDatabaseCount('pictures', 1);
    assertDatabaseMissing('pictures', [
        'id' => 1,
        'imageable_type' => 'App\Models\Tenants\Site',
        'imageable_id' => 1
    ]);
    assertDatabaseHas('pictures', [
        'id' => 2,
        'imageable_type' => 'App\Models\Tenants\Site',
        'imageable_id' => 1
    ]);

    expect(Storage::disk('tenants')->exists($picture->path))->toBeFalse();
});

// it('decrements disk size in company table when picture are added to a site', function () {
//     $site = Site::factory()->create();
//     $file1 = UploadedFile::fake()->image('avatar.png')->size(1000);
//     $file2 = UploadedFile::fake()->image('test.jpg')->size(1200);

//     $formData = [
//         'pictures' => [
//             $file1,
//             $file2
//         ]
//     ];

//     $response = $this->postToTenant('api.sites.pictures.post', $formData, $site);
//     $response->assertSessionHasNoErrors();


//     $company = Company::first();
//     assertEquals(round($company->disk_size / 1024), Picture::find(1)->size + Picture::find(2)->size);

//     $picture = Picture::first();

//     $response = $this->deleteFromTenant('api.pictures.delete', $picture);

//     assertEquals(round($company->disk_size / 1024), 1200);
// });

it('deletes picture directory if directory is empty', function () {

    $site = Site::factory()->create();
    $file1 = UploadedFile::fake()->image('avatar.png');

    $formData = [
        'pictures' => [
            $file1,
        ]
    ];

    $response = $this->postToTenant('api.sites.pictures.post', $formData, $site);
    $response->assertSessionHasNoErrors();
    assertDatabaseCount('pictures', 1);
    assertDatabaseHas('pictures', [
        'id' => 1,
        'imageable_type' => get_class($site),
        'imageable_id' => 1
    ]);

    $picture = Picture::first();
    expect(Storage::disk('tenants')->exists($picture->directory))->toBeTrue();

    $response = $this->deleteFromTenant('api.pictures.delete', $picture);
    assertDatabaseCount('pictures', 0);
    assertDatabaseMissing('pictures', [
        'id' => 1,
        'imageable_type' => get_class($site),
        'imageable_id' => 1
    ]);

    expect(Storage::disk('tenants')->exists($picture->directory))->toBeFalse();
});

it('does not delete picture directory if directory is not empty', function () {

    $site = Site::factory()->create();
    $file1 = UploadedFile::fake()->image('avatar.png');
    $file2 = UploadedFile::fake()->image('test.jpg');

    $formData = [
        'pictures' => [
            $file1,
            $file2
        ]
    ];

    $response = $this->postToTenant('api.sites.pictures.post', $formData, $site);
    $response->assertSessionHasNoErrors();
    assertDatabaseCount('pictures', 2);
    assertDatabaseHas('pictures', [
        'id' => 1,
        'imageable_type' => get_class($site),
        'imageable_id' => 1
    ]);

    $picture = Picture::first();
    expect(Storage::disk('tenants')->exists($picture->directory))->toBeTrue();

    $response = $this->deleteFromTenant('api.pictures.delete', $picture);
    assertDatabaseCount('pictures', 1);
    assertDatabaseMissing('pictures', [
        'id' => 1,
        'imageable_type' => get_class($site),
        'imageable_id' => 1
    ]);
    assertDatabaseHas('pictures', [
        'id' => 2,
        'imageable_type' => get_class($site),
        'imageable_id' => 1
    ]);

    expect(Storage::disk('tenants')->exists($picture->directory))->toBeTrue();
});
