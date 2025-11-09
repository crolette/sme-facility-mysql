<?php

use Carbon\Carbon;
use App\Models\LocationType;
use App\Models\Tenants\Room;
use App\Models\Tenants\Site;
use App\Models\Tenants\User;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Floor;
use App\Models\Tenants\Company;

use App\Jobs\CompressPictureJob;
use App\Models\Tenants\Building;
use App\Models\Tenants\Document;
use Illuminate\Http\UploadedFile;
use App\Models\Central\CategoryType;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use function Pest\Laravel\assertDatabaseHas;
use function PHPUnit\Framework\assertEquals;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseMissing;

beforeEach(function () {
    $this->user = User::factory()->withRole('Admin')->create();
    $this->actingAs($this->user, 'tenant');

    LocationType::factory()->create(['level' => 'site']);
    LocationType::factory()->create(['level' => 'building']);
    LocationType::factory()->create(['level' => 'floor']);
    LocationType::factory()->create(['level' => 'room']);
    CategoryType::factory()->count(2)->create(['category' => 'document']);
    $this->categoryType = CategoryType::factory()->create(['category' => 'asset']);
    CategoryType::factory()->count(2)->create(['category' => 'asset']);



    // on créée les différentes "locations" possibles pour attacher un asset
    $this->site = Site::factory()->create();
    $this->building = Building::factory()->create();
    $this->floor = Floor::factory()->create();
    $this->room = Room::factory()
        ->for(LocationType::where('level', 'room')->first())
        ->for(Floor::first())
        ->create();

    // on créé un asset qu'on attache à une room
    // $this->asset = Asset::factory()->forLocation($this->room)->create();
});

it('can upload a new document (document) without attaching to a model', function () {

    $file = UploadedFile::fake()->create('nomdufichier.pdf', 200, 'application/pdf');
    $categoryType = CategoryType::where('category', 'document')->first();

    $formData = [
        'file' => $file,
        'name' => 'FILE 1 - PDF Document',
        'description' => 'description PDF',
        'typeId' => $categoryType->id,
        'typeSlug' => $categoryType->slug
    ];

    $response = $this->postToTenant('api.documents.store', $formData);
    $response->assertSessionHasNoErrors();

    assertDatabaseCount('documents', 1);
    assertDatabaseHas('documents', [
        'name' => 'FILE 1 - PDF Document',
        'description' => 'description PDF',
    ]);

    Storage::disk('tenants')->assertExists(Document::first()->path);
});

it('can upload a new document (image) without attaching to a model', function () {

    $file = UploadedFile::fake()->image('nomdufichier.png');
    $categoryType = CategoryType::where('category', 'document')->first();

    $formData = [
        'file' => $file,
        'name' => 'FILE 1 - Image Document',
        'description' => 'description image',
        'typeId' => $categoryType->id,
        'typeSlug' => $categoryType->slug
    ];

    $response = $this->postToTenant('api.documents.store', $formData);
    $response->assertSessionHasNoErrors();

    assertDatabaseCount('documents', 1);
    assertDatabaseHas('documents', [
        'name' => 'FILE 1 - Image Document',
        'description' => 'description image',
    ]);

    Storage::disk('tenants')->assertExists(Document::first()->path);
});

it('can upload a new document (image) and compress picture', function () {

    Queue::fake();

    $file = UploadedFile::fake()->image('nomdufichier.png');
    $categoryType = CategoryType::where('category', 'document')->first();

    $formData = [
        'file' => $file,
        'name' => 'FILE 1 - Image Document',
        'description' => 'description image',
        'typeId' => $categoryType->id,
        'typeSlug' => $categoryType->slug
    ];

    $response = $this->postToTenant('api.documents.store', $formData);
    $response->assertSessionHasNoErrors();

    Queue::assertPushed(CompressPictureJob::class, function ($job) {
        $job->handle(); // Exécute manuellement
        return true;
    });

    assertDatabaseCount('documents', 1);
    assertDatabaseHas('documents', [
        'name' => 'FILE 1 - Image Document',
        'description' => 'description image',
        'mime_type' => 'image/webp'
    ]);

    Storage::disk('tenants')->assertExists(Document::first()->path);
});

it('increments the disk size in the company table', function () {

    $file = UploadedFile::fake()->create('nomdufichier.pdf', 2000, 'application/pdf');
    $categoryType = CategoryType::where('category', 'document')->first();

    $formData = [
        'file' => $file,
        'name' => 'FILE 1 - PDF Document',
        'description' => 'description PDF',
        'typeId' => $categoryType->id,
        'typeSlug' => $categoryType->slug
    ];

    $response = $this->postToTenant('api.documents.store', $formData);
    $response->assertSessionHasNoErrors();

    $company = Company::first();
    assertEquals(round($company->disk_size / 1024), 2000);
});

it('decrements the disk size when a document is deleted', function () {

    $file = UploadedFile::fake()->create('nomdufichier.pdf', 150, 'application/pdf');
    $categoryType = CategoryType::where('category', 'document')->first();

    $formData = [
        'file' => $file,
        'name' => 'FILE 1 - PDF Document',
        'description' => 'description PDF',
        'typeId' => $categoryType->id,
        'typeSlug' => $categoryType->slug
    ];

    $response = $this->postToTenant('api.documents.store', $formData);
    $response->assertSessionHasNoErrors();

    $company = Company::first();
    assertEquals(round($company->disk_size / 1024), 150);

    $document = Document::first();

    $response = $this->deleteFromTenant('api.documents.delete', $document->id);

    $company->refresh();
    assertEquals($company->disk_size, 0);
});
