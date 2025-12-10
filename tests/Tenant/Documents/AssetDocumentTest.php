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

    CategoryType::factory()->create(['category' => 'document']);
    $this->categoryType = CategoryType::factory()->create(['category' => 'asset']);
    CategoryType::factory()->count(2)->create(['category' => 'asset']);

    // on créée les différentes "locations" possibles pour attacher un asset
    $this->site = Site::factory()->withMaintainableData()->create();
    $this->building = Building::factory()->withMaintainableData()->create();
    $this->floor = Floor::factory()->withMaintainableData()->create();
    $this->room = Room::factory()->withMaintainableData()->create();

    Queue::fake();
});

it('can attach existing documents to asset', function () {
    $asset = Asset::factory()->withMaintainableData()->forLocation($this->room)->create();

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

    $file1 = UploadedFile::fake()->image('avatar.png')->size(1000);
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

it('can upload several files to asset and increment disk space accordingly (before compressing picture)', function () {



    $file1 = UploadedFile::fake()->image('avatar.png')->size(4000);
    $file2 = UploadedFile::fake()->create('nomdufichier.pdf', 2000, 'application/pdf');
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

    $company = Company::first();
    assertEquals(round($company->disk_size / 1024), 6000);
});

it('fails when upload wrong image mime (ie. webp)', function () {

    $file1 = UploadedFile::fake()->image('avatar.webp');
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
                'typeSlug' => $categoryType->slug
            ],
        ]
    ];

    $response = $this->postToTenant('api.assets.store', $formData);
    $response->assertSessionHasErrors([
        'files.0.file' => "The files.0.file field must be a file of type: jpg, jpeg, png, pdf.",
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

    $asset = Asset::factory()->withMaintainableData()->forLocation($this->room)->create();
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

    $asset = Asset::factory()->withMaintainableData()->forLocation($this->room)->create();

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

    $asset = Asset::factory()->withMaintainableData()->forLocation($this->room)->create();

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

it('deletes the document if an asset is deleted and document is not linked to another asset/location', function () {
    $file1 = UploadedFile::fake()->image('avatar.png')->size(1000);
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
    $response->assertSessionHasNoErrors();


    assertDatabaseCount('documents', 2);
    assertDatabaseHas('documentables', [
        'document_id' => 1,
        'documentable_type' => get_class($asset),
        'documentable_id' => $asset->id
    ]);
    assertDatabaseHas('documentables', [
        'document_id' => 2,
        'documentable_type' => get_class($asset),
        'documentable_id' => $asset->id
    ]);

    $firstDocumentPath = Document::first()->path;
    $secondDocumentPath = Document::find(2)->path;
    Storage::disk('tenants')->assertExists($firstDocumentPath);
    Storage::disk('tenants')->assertExists($secondDocumentPath);

    $response = $this->deleteFromTenant('api.assets.force', $asset->reference_code);
    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);


    assertDatabaseMissing('documentables', [
        'document_id' => 1,
        'documentable_type' => get_class($asset),
        'documentable_id' => $asset->id
    ]);
    assertDatabaseMissing('documentables', [
        'document_id' => 2,
        'documentable_type' => get_class($asset),
        'documentable_id' => $asset->id
    ]);

    assertDatabaseCount('documents', 0);

    Storage::disk('tenants')->assertMissing($firstDocumentPath);
    Storage::disk('tenants')->assertMissing($secondDocumentPath);
});

it('deletes the document if an asset is deleted and document is not linked to another asset/location and decrease disk size accordingly', function () {

    Queue::fake();
    $file1 = UploadedFile::fake()->image('avatar.png')->size(1000);
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

    $this->postToTenant('api.assets.store', $formData);
    $asset = Asset::first();
    $company = Company::first();
    assertEquals(round($company->disk_size / 1024), 1200);

    $this->deleteFromTenant('api.assets.force', $asset->reference_code);
    $company->refresh();
    assertEquals(round($company->disk_size / 1024), 0);
});

it('deletes only document if the document is not linked to another asset/location', function () {
    $file1 = UploadedFile::fake()->image('avatar.png')->size(1000);
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
    $response->assertSessionHasNoErrors();

    $document = Document::first();

    $formData = [
        'name' => 'New site',
        'description' => 'Description new site',
        'locationType' => LocationType::where('level', 'site')->first()->id,
        'existing_documents' => [$document->id]

    ];

    $response = $this->postToTenant('api.sites.store', $formData);

    $site = Site::find(2);

    assertDatabaseCount('documents', 2);
    assertDatabaseHas('documentables', [
        'document_id' => 1,
        'documentable_type' => get_class($asset),
        'documentable_id' => $asset->id
    ]);

    assertDatabaseHas('documentables', [
        'document_id' => 1,
        'documentable_type' => get_class($site),
        'documentable_id' => $site->id
    ]);

    assertDatabaseHas('documentables', [
        'document_id' => 2,
        'documentable_type' => get_class($asset),
        'documentable_id' => $asset->id
    ]);

    $firstDocumentPath = Document::first()->path;
    $secondDocumentPath = Document::find(2)->path;
    Storage::disk('tenants')->assertExists($firstDocumentPath);
    Storage::disk('tenants')->assertExists($secondDocumentPath);

    $response = $this->deleteFromTenant('api.assets.force', $asset->reference_code);
    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);


    assertDatabaseMissing('documentables', [
        'document_id' => 1,
        'documentable_type' => get_class($asset),
        'documentable_id' => $asset->id
    ]);

    assertDatabaseHas('documentables', [
        'document_id' => 1,
        'documentable_type' => get_class($site),
        'documentable_id' => $site->id
    ]);

    assertDatabaseMissing('documentables', [
        'document_id' => 2,
        'documentable_type' => get_class($asset),
        'documentable_id' => $asset->id
    ]);

    assertDatabaseCount('documents', 1);

    Storage::disk('tenants')->assertExists($firstDocumentPath);
    Storage::disk('tenants')->assertMissing($secondDocumentPath);
});

it('deletes only document if the document is not linked to another asset/location and decrease disk size accordingly', function () {
    Queue::fake();
    $file1 = UploadedFile::fake()->image('avatar.png')->size(1000);
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
        'name' => 'New site',
        'description' => 'Description new site',
        'locationType' => LocationType::where('level', 'site')->first()->id,
        'existing_documents' => [$document->id]

    ];

    $response = $this->postToTenant('api.sites.store', $formData);

    $company = Company::first();
    assertEquals(round($company->disk_size / 1024), 1200);

    $response = $this->deleteFromTenant('api.assets.force', $asset->reference_code);
    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);

    $company->refresh();
    assertEquals(round($company->disk_size / 1024), 1000);
});
