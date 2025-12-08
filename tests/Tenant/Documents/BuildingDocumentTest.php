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
use function Pest\Laravel\assertDatabaseHas;
use function PHPUnit\Framework\assertEquals;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseMissing;

beforeEach(function () {
    LocationType::factory()->create(['level' => 'site']);
    $this->locationType = LocationType::factory()->create(['level' => 'building']);
    $this->documentCategory = CategoryType::factory()->create(['category' => 'document']);

    $this->user = User::factory()->withRole('Admin')->create();
    $this->actingAs($this->user, 'tenant');

    $this->site = Site::factory()->withMaintainableData()->create();
    $this->location = Building::factory()->create();
    Floor::factory()->withMaintainableData()->create();
    $this->room = Room::factory()->withMaintainableData()->create();
});

it('can attach existing documents to building', function () {
    CategoryType::where('category', 'document')->first();
    $documents = Document::factory()->count(2)->withCustomAttributes([
        'user' => $this->user,
        'directoryName' => 'buildings',
        'model' => $this->location,
    ])->create();

    $formData = [
        'name' => 'New building',
        'description' => 'Description new building',
        'levelType' => $this->site->id,
        'locationType' => $this->locationType->id,
        'existing_documents' => [...$documents->pluck('id')]

    ];

    $response = $this->postToTenant('api.buildings.store', $formData);
    $response->assertSessionHasNoErrors();

    assertDatabaseCount('documents', 2);
    assertDatabaseHas('documentables', [
        'document_id' => 1,
        'documentable_type' => 'App\Models\Tenants\Building',
        'documentable_id' => 2
    ]);
    assertDatabaseHas('documentables', [
        'document_id' => 2,
        'documentable_type' => 'App\Models\Tenants\Building',
        'documentable_id' => 2
    ]);
});

it('can upload several files when creating building', function () {

    $file1 = UploadedFile::fake()->image('avatar.png');
    $file2 = UploadedFile::fake()->create('nomdufichier.pdf', 200, 'application/pdf');

    $formData = [
        'name' => 'New building',
        'description' => 'Description new building',
        'levelType' => $this->site->id,
        'locationType' => $this->locationType->id,
        'files' => [
            [
                'file' => $file1,
                'name' => 'FILE 1 - Long name of more than 10 chars',
                'description' => 'descriptionIMG',
                'typeId' => $this->documentCategory->id,
                'typeSlug' => $this->documentCategory->slug
            ],
            [
                'file' => $file2,
                'name' => 'FILE 2 - Long name of more than 10 chars',
                'description' => 'descriptionPDF',
                'typeId' => $this->documentCategory->id,
                'typeSlug' => $this->documentCategory->slug
            ]
        ]
    ];

    $response = $this->postToTenant('api.buildings.store', $formData);
    $response->assertSessionHasNoErrors();

    assertDatabaseCount('documents', 2);
    assertDatabaseHas('documentables', [
        'document_id' => 1,
        'documentable_type' => get_class($this->location),
        'documentable_id' => 2
    ]);
    assertDatabaseHas('documentables', [
        'document_id' => 2,
        'documentable_type' => get_class($this->location),
        'documentable_id' => 2
    ]);

    Storage::disk('tenants')->assertExists(Document::first()->path);
    Storage::disk('tenants')->assertExists(Document::first()->directory);
});

it('can upload several files when creating building and increment disk space accordingly (before compressing picture)', function () {

    Queue::fake();

    $file1 = UploadedFile::fake()->image('avatar.png')->size(1000);
    $file2 = UploadedFile::fake()->create('nomdufichier.pdf', 200, 'application/pdf');

    $formData = [
        'name' => 'New building',
        'description' => 'Description new building',
        'levelType' => $this->site->id,
        'locationType' => $this->locationType->id,
        'files' => [
            [
                'file' => $file1,
                'name' => 'FILE 1 - Long name of more than 10 chars',
                'description' => 'descriptionIMG',
                'typeId' => $this->documentCategory->id,
                'typeSlug' => $this->documentCategory->slug
            ],
            [
                'file' => $file2,
                'name' => 'FILE 2 - Long name of more than 10 chars',
                'description' => 'descriptionPDF',
                'typeId' => $this->documentCategory->id,
                'typeSlug' => $this->documentCategory->slug
            ]
        ]
    ];

    $this->postToTenant('api.buildings.store', $formData);

    $company = Company::first();
    assertEquals(round($company->disk_size / 1024), 1200);
});

it('can upload documents to an existing building', function () {
    $file1 = UploadedFile::fake()->image('avatar.png');
    $file2 = UploadedFile::fake()->create('nomdufichier.pdf', 200, 'application/pdf');

    $formData = [
        'files' => [
            [
                'file' => $file1,
                'name' => 'FILE 1 - Long name of more than 10 chars',
                'description' => 'descriptionIMG',
                'typeId' => $this->documentCategory->id,
                'typeSlug' => $this->documentCategory->slug
            ],
            [
                'file' => $file2,
                'name' => 'FILE 2 - Long name of more than 10 chars',
                'description' => 'descriptionPDF',
                'typeId' => $this->documentCategory->id,
                'typeSlug' => $this->documentCategory->slug
            ]
        ]
    ];

    $response = $this->postToTenant('api.buildings.documents.post', $formData, $this->location->reference_code);
    $response->assertSessionHasNoErrors();

    $document = Document::first();
    expect(Storage::disk('tenants')->exists($document->directory))->toBeTrue();
    expect(Storage::disk('tenants')->exists($document->path))->toBeTrue();

    assertDatabaseCount('documents', 2);
    assertDatabaseHas('documentables', [
        'document_id' => 1,
        'documentable_type' => 'App\Models\Tenants\Building',
        'documentable_id' => 1
    ]);

    assertDatabaseHas('documentables', [
        'document_id' => 2,
        'documentable_type' => 'App\Models\Tenants\Building',
        'documentable_id' => 1
    ]);
});

it('can delete a document from a building', function () {

    $file1 = UploadedFile::fake()->image('avatar.png');

    $formData = [
        'files' => [
            [
                'file' => $file1,
                'name' => 'FILE 1 - Long name of more than 10 chars',
                'description' => 'descriptionIMG',
                'typeId' => $this->documentCategory->id,
                'typeSlug' => $this->documentCategory->slug
            ],
        ]
    ];

    $response = $this->postToTenant('api.buildings.documents.post', $formData, $this->location->reference_code);
    $response->assertSessionHasNoErrors();

    $document = $this->location->documents()->first();

    $response = $this->deleteFromTenant('api.documents.delete', $document->id);
    $response->assertOk();

    $this->assertDatabaseMissing('documents', [
        'id' => $document->id,
        'filename' => $document->filename
    ]);

    $this->assertDatabaseMissing('documentables', [
        'document_id' => $document->id,
        'documentable_id' =>  $this->location->id,
        'documentable_type' => get_class($this->location)
    ]);

    expect(Storage::disk('tenants')->exists($document->path))->toBeFalse();
});

it('can remove/detach a document from a building', function () {
    $file1 = UploadedFile::fake()->image('avatar.png');
    $file2 = UploadedFile::fake()->create('nomdufichier.pdf', 200, 'application/pdf');

    $formData = [
        'files' => [
            [
                'file' => $file1,
                'name' => 'FILE 1 - Long name of more than 10 chars',
                'description' => 'descriptionIMG',
                'typeId' => $this->documentCategory->id,
                'typeSlug' => $this->documentCategory->slug
            ],
            [
                'file' => $file2,
                'name' => 'FILE 2 - Long name of more than 10 chars',
                'description' => 'descriptionPDF',
                'typeId' => $this->documentCategory->id,
                'typeSlug' => $this->documentCategory->slug
            ]
        ]
    ];

    $this->postToTenant('api.buildings.documents.post', $formData, $this->location->reference_code);

    $document = Document::first();

    $formData = [
        'document_id' => $document->id
    ];

    $response = $this->patchToTenant('api.buildings.documents.detach', $formData, $this->location->reference_code);
    $response->assertOk();

    $this->assertDatabaseHas('documents', [
        'id' => $document->id,
        'filename' => $document->filename
    ]);

    $this->assertDatabaseMissing('documentables', [
        'document_id' => $document->id,
        'documentable_id' => $this->location->id,
        'documentable_type' => Building::class
    ]);

    expect(Storage::disk('tenants')->exists($document->path))->toBeTrue();
});

it('fails when upload wrong image mime (ie. webp)', function () {

    $file1 = UploadedFile::fake()->image('avatar.webp');
    $file2 = UploadedFile::fake()->create('report.docx', 100, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

    $formData = [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'levelType' => $this->site->id,
        'locationType' => $this->locationType->id,
        'files' => [
            [
                'file' => $file1,
                'name' => 'Long description of more than 10 chars',
                'description' => 'descriptionIMG',
                'typeId' => $this->documentCategory->id,
                'typeName' => $this->documentCategory->slug
            ],
            [
                'file' => $file2,
                'name' => 'Long description of more than 10 chars',
                'description' => 'descriptionPDF',
                'typeId' => $this->documentCategory->id,
                'typeName' => $this->documentCategory->slug
            ]
        ]
    ];

    $response = $this->postToTenant('api.buildings.store', $formData);
    $response->assertSessionHasErrors([
        'files.0.file' => "The files.0.file field must be a file of type: jpg, jpeg, png, pdf.",
        'files.1.file' => "The files.1.file field must be a file of type: jpg, jpeg, png, pdf."
    ]);
});

it('fails when upload exceeding document size : ' . Document::maxUploadSizeKB() . "kb", function () {

    $file1 = UploadedFile::fake()->create('nomdufichier.pdf', Document::maxUploadSizeKB() * 2, 'application/pdf');

    $formData = [
        'name' => 'New building',
        'description' => 'Description new building',
        'levelType' => $this->site->id,
        'locationType' => $this->locationType->id,
        'files' => [
            [
                'file' => $file1,
                'name' => 'Long description of more than 10 chars',
                'description' => 'descriptionIMG',
                'typeId' => $this->documentCategory->id,
                'typeName' => $this->documentCategory->slug
            ],
        ]
    ];

    $response = $this->postToTenant('api.buildings.store', $formData);
    $response->assertSessionHasErrors([
        'files.0.file' => "The files.0.file field must not be greater than " . Document::maxUploadSizeKB() . " kilobytes.",
    ]);
});

it('can update name and description a document from a building ', function () {

    $document = Document::factory()->withCustomAttributes([
        'user' => $this->user,
        'directoryName' => 'buildings',
        'model' =>  $this->location,
    ])->create();
    $this->location->documents()->attach($document);

    $formData =  [
        'name' => 'New document name',
        'description' =>  'New description of the new document',
        'typeId' => $this->documentCategory->id,
        'typeSlug' => $this->documentCategory->slug
    ];

    $response = $this->patchToTenant('api.documents.update', $formData, $document->id);
    $response->assertOk();
    $this->assertDatabaseHas('documents', [
        'id' => $document->id,
        'name' => 'New document name',
        'description' => 'New description of the new document',
        'category_type_id' => $this->documentCategory->id
    ]);
});

it('deletes the documents directory if it is empty', function () {

    $file1 = UploadedFile::fake()->image('avatar.png');

    $formData = [

        'files' => [
            [
                'file' => $file1,
                'name' => 'FILE 1 - Long name of more than 10 chars',
                'description' => 'descriptionIMG',
                'typeId' => $this->documentCategory->id,
                'typeSlug' => $this->documentCategory->slug
            ],

        ]
    ];

    $response = $this->postToTenant('api.buildings.documents.post', $formData, $this->location->reference_code);
    $response->assertSessionHasNoErrors();

    $document = $this->location->documents()->first();

    expect(Storage::disk('tenants')->exists($document->directory))->toBeTrue();
    expect(Storage::disk('tenants')->exists($document->path))->toBeTrue();

    $response = $this->deleteFromTenant('api.documents.delete', $document->id);
    $response->assertOk();

    $this->assertDatabaseMissing('documents', [
        'id' => $document->id,
        'filename' => $document->filename
    ]);

    $this->assertDatabaseMissing('documentables', [
        'documentable_id' => $this->location->id,
        'documentable_type' => get_class($this->location)
    ]);

    expect(Storage::disk('tenants')->exists($document->directory))->toBeFalse();
    expect(Storage::disk('tenants')->exists($document->path))->toBeFalse();
});

it('deletes the document if a building is deleted and document is not linked to another asset/location', function () {
    $file1 = UploadedFile::fake()->image('avatar.png')->size(1000);
    $file2 = UploadedFile::fake()->create('nomdufichier.pdf', 200, 'application/pdf');

    $formData = [
        'name' => 'New building',
        'description' => 'Description new building',
        'levelType' => $this->site->id,
        'locationType' => $this->locationType->id,
        'files' => [
            [
                'file' => $file1,
                'name' => 'FILE 1 - Long name of more than 10 chars',
                'description' => 'descriptionIMG',
                'typeId' => $this->documentCategory->id,
                'typeSlug' => $this->documentCategory->slug
            ],
            [
                'file' => $file2,
                'name' => 'FILE 2 - Long name of more than 10 chars',
                'description' => 'descriptionPDF',
                'typeId' => $this->documentCategory->id,
                'typeSlug' => $this->documentCategory->slug
            ]
        ]
    ];

    $response = $this->postToTenant('api.buildings.store', $formData);
    $location = Building::find(2);
    $response->assertSessionHasNoErrors();

    assertDatabaseCount('documents', 2);
    assertDatabaseHas('documentables', [
        'document_id' => 1,
        'documentable_type' => get_class($location),
        'documentable_id' => $location->id
    ]);
    assertDatabaseHas('documentables', [
        'document_id' => 2,
        'documentable_type' => get_class($location),
        'documentable_id' => $location->id
    ]);

    $firstDocumentPath = Document::first()->path;
    Storage::disk('tenants')->assertExists($firstDocumentPath);
    $secondDocumentPath = Document::find(2)->path;
    Storage::disk('tenants')->assertExists($secondDocumentPath);

    $response = $this->deleteFromTenant('api.buildings.destroy', $location->reference_code);
    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);

    assertDatabaseMissing('documentables', [
        'document_id' => 1,
        'documentable_type' => get_class($location),
        'documentable_id' => $location->id
    ]);
    assertDatabaseMissing('documentables', [
        'document_id' => 2,
        'documentable_type' => get_class($location),
        'documentable_id' => $location->id
    ]);

    assertDatabaseCount('documents', 0);

    Storage::disk('tenants')->assertMissing($firstDocumentPath);
    Storage::disk('tenants')->assertMissing($secondDocumentPath);
});

it('deletes the document if a building is deleted and document is not linked to another asset/location and decrease disk size accordingly', function () {

    Queue::fake();


    $file1 = UploadedFile::fake()->image('avatar.png')->size(1000);
    $file2 = UploadedFile::fake()->create('nomdufichier.pdf', 200, 'application/pdf');

    $formData = [
        'name' => 'New building',
        'description' => 'Description new building',
        'levelType' => $this->site->id,
        'locationType' => $this->locationType->id,
        'files' => [
            [
                'file' => $file1,
                'name' => 'FILE 1 - Long name of more than 10 chars',
                'description' => 'descriptionIMG',
                'typeId' => $this->documentCategory->id,
                'typeSlug' => $this->documentCategory->slug
            ],
            [
                'file' => $file2,
                'name' => 'FILE 2 - Long name of more than 10 chars',
                'description' => 'descriptionPDF',
                'typeId' => $this->documentCategory->id,
                'typeSlug' => $this->documentCategory->slug
            ]
        ]
    ];

    $this->postToTenant('api.buildings.store', $formData);
    $location = Building::find(2);

    $company = Company::first();
    assertEquals(round($company->disk_size / 1024), 1200);

    $this->deleteFromTenant('api.buildings.destroy', $location->reference_code);

    $company->refresh();
    assertEquals(round($company->disk_size / 1024), 0);
});

it('deletes only document if the document is not linked to another asset/location', function () {
    $file1 = UploadedFile::fake()->image('avatar.png')->size(1000);
    $file2 = UploadedFile::fake()->create('nomdufichier.pdf', 200, 'application/pdf');

    $formData = [
        'name' => 'New building',
        'description' => 'Description new building',
        'levelType' => $this->site->id,
        'locationType' => $this->locationType->id,
        'files' => [
            [
                'file' => $file1,
                'name' => 'FILE 1 - Long name of more than 10 chars',
                'description' => 'descriptionIMG',
                'typeId' => $this->documentCategory->id,
                'typeSlug' => $this->documentCategory->slug
            ],
            [
                'file' => $file2,
                'name' => 'FILE 2 - Long name of more than 10 chars',
                'description' => 'descriptionPDF',
                'typeId' => $this->documentCategory->id,
                'typeSlug' => $this->documentCategory->slug
            ]
        ]
    ];

    $response = $this->postToTenant('api.buildings.store', $formData);
    $location = Building::find(2);
    $response->assertSessionHasNoErrors();

    $document = Document::first();

    $categoryType = CategoryType::factory()->create(['category' => 'asset']);

    $formData = [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'locationId' => $this->room->id,
        'locationReference' => $this->room->reference_code,
        'locationType' => 'room',
        'categoryId' => $categoryType->id,
        'existing_documents' => [$document->id]

    ];

    $response = $this->postToTenant('api.assets.store', $formData);

    $asset = Asset::first();

    assertDatabaseCount('documents', 2);
    assertDatabaseHas('documentables', [
        'document_id' => 1,
        'documentable_type' => get_class($location),
        'documentable_id' => $location->id
    ]);
    assertDatabaseHas('documentables', [
        'document_id' => 2,
        'documentable_type' => get_class($location),
        'documentable_id' => $location->id
    ]);
    assertDatabaseHas('documentables', [
        'document_id' => 1,
        'documentable_type' => get_class($asset),
        'documentable_id' => $asset->id
    ]);

    $firstDocumentPath = Document::first()->path;
    Storage::disk('tenants')->assertExists($firstDocumentPath);
    $secondDocumentPath = Document::find(2)->path;
    Storage::disk('tenants')->assertExists($secondDocumentPath);

    $response = $this->deleteFromTenant('api.buildings.destroy', $location->reference_code);
    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);

    assertDatabaseMissing('documentables', [
        'document_id' => 1,
        'documentable_type' => get_class($location),
        'documentable_id' => $location->id
    ]);
    assertDatabaseMissing('documentables', [
        'document_id' => 2,
        'documentable_type' => get_class($location),
        'documentable_id' => $location->id
    ]);

    assertDatabaseHas('documentables', [
        'document_id' => 1,
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

    $formData = [
        'name' => 'New building',
        'description' => 'Description new building',
        'levelType' => $this->site->id,
        'locationType' => $this->locationType->id,
        'files' => [
            [
                'file' => $file1,
                'name' => 'FILE 1 - Long name of more than 10 chars',
                'description' => 'descriptionIMG',
                'typeId' => $this->documentCategory->id,
                'typeSlug' => $this->documentCategory->slug
            ],
            [
                'file' => $file2,
                'name' => 'FILE 2 - Long name of more than 10 chars',
                'description' => 'descriptionPDF',
                'typeId' => $this->documentCategory->id,
                'typeSlug' => $this->documentCategory->slug
            ]
        ]
    ];

    $this->postToTenant('api.buildings.store', $formData);
    $location = Building::find(2);
    $document = Document::first();

    $categoryType = CategoryType::factory()->create(['category' => 'asset']);

    $formData = [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'locationId' => $this->room->id,
        'locationReference' => $this->room->reference_code,
        'locationType' => 'room',
        'categoryId' => $categoryType->id,
        'existing_documents' => [$document->id]

    ];

    $this->postToTenant('api.assets.store', $formData);

    $company = Company::first();
    assertEquals(round($company->disk_size / 1024), 1200);

    $this->deleteFromTenant('api.buildings.destroy', $location->reference_code);

    $company->refresh();
    assertEquals(round($company->disk_size / 1024), 1000);
});
