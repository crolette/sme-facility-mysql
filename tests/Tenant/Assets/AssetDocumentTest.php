<?php

use Carbon\Carbon;
use App\Models\LocationType;
use App\Models\Tenants\Room;
use App\Models\Tenants\Site;
use App\Models\Tenants\User;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Floor;
use App\Models\Tenants\Building;

use App\Models\Tenants\Document;
use Illuminate\Http\UploadedFile;
use App\Models\Central\CategoryType;
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

it('can attach existing documents to asset', function () {
    $asset = Asset::factory()->forLocation($this->room)->create();

    CategoryType::where('category', 'document')->first();
    $documents = Document::factory()->count(2)->withCustomAttributes([
                'user' => $this->user,
                'directoryName' => 'assets',
                'model' => $asset,
            ])->create();

    $formData = [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'locationId' => $this->room->id,
        'locationReference' => $this->room->reference_code,
        'locationType' => 'room',
        'categoryId' => $this->categoryType->id,
        'existing_documents' => [...$documents->pluck('id')]
        
    ];

    $response = $this->postToTenant('api.assets.store', $formData);
    $response->assertSessionHasNoErrors();

    assertDatabaseCount('documents', 2);
    assertDatabaseHas('documentables', [
        'document_id' => 1,
        'documentable_type' => 'App\Models\Tenants\Asset',
        'documentable_id' => 2
    ]);
    assertDatabaseHas('documentables', [
        'document_id' => 2,
        'documentable_type' => 'App\Models\Tenants\Asset',
        'documentable_id' => 2
    ]);

});

it('can upload several files to asset', function () {

    $file1 = UploadedFile::fake()->image('avatar.png');
    $file2 = UploadedFile::fake()->create('nomdufichier.pdf', 200, 'application/pdf');
    $categoryType = CategoryType::where('category', 'document')->first();

    $formData = [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'locationId' => $this->room->id,
        'locationReference' => $this->room->reference_code,
        'locationType' => 'room',
        'categoryId' => $this->categoryType->id,
        'files' => [
            [
                'file' => $file1,
                'name' => 'FILE 1 - Long name of more than 10 chars',
                'description' => 'descriptionIMG',
                'typeId' => $categoryType->id,
                'typeSlug' => $categoryType->slug
            ],
            [
                'file' => $file2,
                'name' => 'FILE 2 - Long name of more than 10 chars',
                'description' => 'descriptionPDF',
                'typeId' => $categoryType->id,
                'typeSlug' => $categoryType->slug
            ]
        ]
    ];

    $response = $this->postToTenant('api.assets.store', $formData);
    $response->assertSessionHasNoErrors();

    assertDatabaseCount('documents', 2);
    assertDatabaseHas('documentables', [
        'document_id' => 1,
        'documentable_type' => 'App\Models\Tenants\Asset',
        'documentable_id' => 1
    ]);
    assertDatabaseHas('documentables', [
        'document_id' => 2,
        'documentable_type' => 'App\Models\Tenants\Asset',
        'documentable_id' => 1
    ]);

    Storage::disk('tenants')->assertExists(Document::first()->path);
});


it('fails when upload wrong image mime (ie. webp)', function () {

    $file1 = UploadedFile::fake()->image('avatar.webp');
    $file2 = UploadedFile::fake()->create('report.docx', 100, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    $categoryType = CategoryType::where('category', 'document')->first();

    $formData = [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'locationId' => $this->room->id,
        'locationReference' => $this->room->reference_code,
        'locationType' => 'room',
        'categoryId' => $this->categoryType->id,
        'files' => [
            [
                'file' => $file1,
                'name' => 'Long description of more than 10 chars',
                'description' => 'descriptionIMG',
                'typeId' => $categoryType->id,
                'typeName' => $categoryType->slug
            ],
            [
                'file' => $file2,
                'name' => 'Long description of more than 10 chars',
                'description' => 'descriptionPDF',
                'typeId' => $categoryType->id,
                'typeName' => $categoryType->slug
            ]
        ]
    ];

    $response = $this->postToTenant('api.assets.store', $formData);
    $response->assertSessionHasErrors([
        'files.0.file' => "The files.0.file field must be a file of type: jpg, jpeg, png, pdf.",
        'files.1.file' => "The files.1.file field must be a file of type: jpg, jpeg, png, pdf."
    ]);
});

it('fails when upload exceeding document size : ' . Document::maxUploadSizeKB() . "kb", function () {

    $file1 = UploadedFile::fake()->create('nomdufichier.pdf', Document::maxUploadSizeKB() * 2, 'application/pdf');
    $categoryType = CategoryType::where('category', 'document')->first();

    $formData = [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'locationId' => $this->room->id,
        'locationReference' => $this->room->reference_code,
        'locationType' => 'room',
        'categoryId' => $this->categoryType->id,
        'files' => [
            [
                'file' => $file1,
                'name' => 'Long description of more than 10 chars',
                'description' => 'descriptionIMG',
                'typeId' => $categoryType->id,
                'typeName' => $categoryType->slug
            ],
        ]
    ];

    $response = $this->postToTenant('api.assets.store', $formData);
    $response->assertSessionHasErrors([
        'files.0.file' => "The files.0.file field must not be greater than " . Document::maxUploadSizeKB() . " kilobytes.",
    ]);
});

it('can delete a document from an asset', function () {

    $file1 = UploadedFile::fake()->image('avatar.png');
    $file2 = UploadedFile::fake()->create('nomdufichier.pdf', 200, 'application/pdf');
    $categoryType = CategoryType::where('category', 'document')->first();

    $formData = [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'locationId' => $this->room->id,
        'locationReference' => $this->room->reference_code,
        'locationType' => 'room',
        'categoryId' => $this->categoryType->id,
        'files' => [
            [
                'file' => $file1,
                'name' => 'FILE 1 - Long name of more than 10 chars',
                'description' => 'descriptionIMG',
                'typeId' => $categoryType->id,
                'typeSlug' => $categoryType->slug
            ],
            [
                'file' => $file2,
                'name' => 'FILE 2 - Long name of more than 10 chars',
                'description' => 'descriptionPDF',
                'typeId' => $categoryType->id,
                'typeSlug' => $categoryType->slug
            ]
        ]
    ];

    $response = $this->postToTenant('api.assets.store', $formData);

    $asset = Asset::first();
    $document = Document::first();

    $response = $this->deleteFromTenant('api.documents.delete', $document->id);
    $response->assertOk();

    $this->assertDatabaseMissing('documents', [
        'id' => $document->id,
        'filename' => $document->filename
    ]);

    $this->assertDatabaseMissing('documentables', [
        'document_id' => $document->id,
        'documentable_id' => $asset->id,
        'documentable_type' => Asset::class
    ]);

    expect(Storage::disk('tenants')->exists($document->path))->toBeFalse();
});

it('can remove/detach a document from an asset', function () {

    $file1 = UploadedFile::fake()->image('avatar.png');
    $file2 = UploadedFile::fake()->create('nomdufichier.pdf', 200, 'application/pdf');
    $categoryType = CategoryType::where('category', 'document')->first();

    $formData = [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'locationId' => $this->room->id,
        'locationReference' => $this->room->reference_code,
        'locationType' => 'room',
        'categoryId' => $this->categoryType->id,
        'files' => [
            [
                'file' => $file1,
                'name' => 'FILE 1 - Long name of more than 10 chars',
                'description' => 'descriptionIMG',
                'typeId' => $categoryType->id,
                'typeSlug' => $categoryType->slug
            ],
            [
                'file' => $file2,
                'name' => 'FILE 2 - Long name of more than 10 chars',
                'description' => 'descriptionPDF',
                'typeId' => $categoryType->id,
                'typeSlug' => $categoryType->slug
            ]
        ]
    ];

    $response = $this->postToTenant('api.assets.store', $formData);

    $asset = Asset::first();
    $document = Document::first();

    $formData = [
        'document_id' => $document->id
    ];

    $response = $this->patchToTenant('api.assets.documents.detach', $formData, $asset->reference_code);
    $response->assertOk();

    $this->assertDatabaseHas('documents', [
        'id' => $document->id,
        'filename' => $document->filename
    ]);

    $this->assertDatabaseMissing('documentables', [
        'document_id' => $document->id,
        'documentable_id' => $asset->id,
        'documentable_type' => Asset::class
    ]);

    expect(Storage::disk('tenants')->exists($document->path))->toBeTrue();

});

it('can update name and description a document from an asset ', function () {

    $asset = Asset::factory()->forLocation($this->room)->create();
    $document = Document::factory()->withCustomAttributes([
        'user' => $this->user,
        'directoryName' => 'assets',
        'model' => $asset,
    ])->create();
    $asset->documents()->attach($document);

    $categoryType = CategoryType::where('category', 'document')->get()->last();

    $formData =  [
        'name' => 'New document name',
        'description' =>  'New description of the new document',
        'typeId' => $categoryType->id,
        'typeSlug' => $categoryType->slug
    ];

    $response = $this->patchToTenant('api.documents.update', $formData, $document->id);
    $response->assertOk();
    $this->assertDatabaseHas('documents', [
        'id' => $document->id,
        'name' => 'New document name',
        'description' => 'New description of the new document',
        'category_type_id' => $categoryType->id
    ]);
});

it('can upload a document to an asset', function () {
    $file1 = UploadedFile::fake()->image('avatar.png');
    $categoryType = CategoryType::where('category', 'document')->first();

    $asset = Asset::factory()->forLocation($this->room)->create();

    $formData = [

        'files' => [
            [
                'file' => $file1,
                'name' => 'FILE 1 - Long name of more than 10 chars',
                'description' => 'descriptionIMG',
                'typeId' => $categoryType->id,
                'typeSlug' => $categoryType->slug
            ],

        ]
    ];

    $response = $this->postToTenant('api.assets.documents.post', $formData, $asset->reference_code);
    $response->assertSessionHasNoErrors();

    $document = $asset->documents()->first();

    expect(Storage::disk('tenants')->exists($document->directory))->toBeTrue();
});


it('deletes the documents directory if it is empty', function () {

    $file1 = UploadedFile::fake()->image('avatar.png');
    $categoryType = CategoryType::where('category', 'document')->first();

    $asset = Asset::factory()->forLocation($this->room)->create();

    $formData = [

        'files' => [
            [
                'file' => $file1,
                'name' => 'FILE 1 - Long name of more than 10 chars',
                'description' => 'descriptionIMG',
                'typeId' => $categoryType->id,
                'typeSlug' => $categoryType->slug
            ],
        ]
    ];

    $response = $this->postToTenant('api.assets.documents.post', $formData, $asset->reference_code);
    $response->assertSessionHasNoErrors();

    $document = $asset->documents()->first();

    expect(Storage::disk('tenants')->exists($document->directory))->toBeTrue();

    $response = $this->deleteFromTenant('api.documents.delete', $document->id);
    $response->assertOk();

    $this->assertDatabaseMissing('documents', [
        'id' => $document->id,
        'filename' => $document->filename
    ]);

    $this->assertDatabaseMissing('documentables', [
        'documentable_id' => $asset->id,
        'documentable_type' => Asset::class
    ]);

    expect(Storage::disk('tenants')->exists($document->directory))->toBeFalse();
});

it('does not delete the documents directory if it is not empty', function () {
    $file1 = UploadedFile::fake()->image('avatar.png');
    $categoryType = CategoryType::where('category', 'document')->first();

    $asset = Asset::factory()->forLocation($this->room)->create();

    $formData = [

        'files' => [
            [
                'file' => $file1,
                'name' => 'FILE 1 - First file',
                'description' => 'descriptionIMG',
                'typeId' => $categoryType->id,
                'typeSlug' => $categoryType->slug
            ],

        ]
    ];

    $response = $this->postToTenant('api.assets.documents.post', $formData, $asset->reference_code);
    $response->assertSessionHasNoErrors();
    $file2 = UploadedFile::fake()->create('nomdufichier.pdf', 200, 'application/pdf');
    $formData = [

        'files' => [
            [
                'file' => $file2,
                'name' => 'FILE 2 - Second file',
                'description' => 'descriptionIMG',
                'typeId' => $categoryType->id,
                'typeSlug' => $categoryType->slug
            ],

        ]
    ];

    $response = $this->postToTenant('api.assets.documents.post', $formData, $asset->reference_code);
    $response->assertSessionHasNoErrors();

    $document = $asset->documents()->first();


    $response = $this->deleteFromTenant('api.documents.delete', $document->id);
    $response->assertOk();

    $this->assertDatabaseMissing('documents', [
        'id' => $document->id,
        'filename' => $document->filename
    ]);

    $this->assertDatabaseMissing('documentables', [
        'document_id' => $document->id,
        'documentable_id' => $asset->id,
        'documentable_type' => Asset::class
    ]);

    expect(Storage::disk('tenants')->exists($document->directory))->toBeTrue();
    assertEquals(1, count(Storage::disk('tenants')->files($document->directory)));
});
