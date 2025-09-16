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
use function Pest\Laravel\assertDatabaseHas;
use function PHPUnit\Framework\assertEquals;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseMissing;

beforeEach(function () {
    LocationType::factory()->create(['level' => 'site']);
    LocationType::factory()->create(['level' => 'building']);
    LocationType::factory()->create(['level' => 'floor']);
    $this->locationType = LocationType::factory()->create(['level' => 'room']);
    $this->documentCategory = CategoryType::factory()->create(['category' => 'document']);

    $this->user = User::factory()->withRole('Admin')->create();
    $this->actingAs($this->user, 'tenant');

    Site::factory()->create();
    Building::factory()->create();
    $this->floor = Floor::factory()->create();
    $this->location = Room::factory()->create();
});

it('can upload several files when creating room', function () {

    $file1 = UploadedFile::fake()->image('avatar.png');
    $file2 = UploadedFile::fake()->create('nomdufichier.pdf', 200, 'application/pdf');

    $formData = [
        'name' => 'New room',
        'description' => 'Description new room',
        'levelType' => $this->floor->id,
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

    $response = $this->postToTenant('api.rooms.store', $formData);
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

it('can upload documents to an existing room', function () {
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

    $response = $this->postToTenant('api.rooms.documents.post', $formData, $this->location->reference_code);
    $response->assertSessionHasNoErrors();

    $document = Document::first();
    expect(Storage::disk('tenants')->exists($document->directory))->toBeTrue();
    expect(Storage::disk('tenants')->exists($document->path))->toBeTrue();

    assertDatabaseCount('documents', 2);
    assertDatabaseHas('documentables', [
        'document_id' => 1,
        'documentable_type' => get_class($this->location),
        'documentable_id' => $this->location->id
    ]);

    assertDatabaseHas('documentables', [
        'document_id' => 2,
        'documentable_type' => get_class($this->location),
        'documentable_id' => $this->location->id
    ]);
});

it('can remove/detach a document from a room', function () {
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

    $this->postToTenant('api.rooms.documents.post', $formData, $this->location->reference_code);

    $document = Document::first();

    $formData = [
        'document_id' => $document->id
    ];

    $response = $this->patchToTenant('api.rooms.documents.detach', $formData, $this->location->reference_code);
    $response->assertOk();

    $this->assertDatabaseHas('documents', [
        'id' => $document->id,
        'filename' => $document->filename
    ]);

    $this->assertDatabaseMissing('documentables', [
        'document_id' => $document->id,
        'documentable_id' => $this->location->id,
        'documentable_type' => get_class($this->location)
    ]);

    expect(Storage::disk('tenants')->exists($document->path))->toBeTrue();
});

it('can delete a document from a room', function () {

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

    $response = $this->postToTenant('api.rooms.documents.post', $formData, $this->location->reference_code);
    $response->assertSessionHasNoErrors();

    $document = $this->location->documents()->first();

    $response = $this->deleteFromTenant('api.documents.delete', $document->id);
    $response->assertOk();

    $this->assertDatabaseMissing('documents', [
        'id' => $document->id,
        'filename' => $document->filename
    ]);

    $this->assertDatabaseMissing('documentables', [
        'documentable_id' =>  $this->location->id,
        'documentable_type' => get_class($this->location)
    ]);

    expect(Storage::disk('tenants')->exists($document->path))->toBeFalse();
});

it('fails when upload wrong image mime (ie. webp)', function () {

    $file1 = UploadedFile::fake()->image('avatar.webp');
    $file2 = UploadedFile::fake()->create('report.docx', 100, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

    $formData = [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'levelType' => $this->floor->id,
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

    $response = $this->postToTenant('api.rooms.store', $formData);
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
        'levelType' => $this->floor->id,
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

    $response = $this->postToTenant('api.rooms.store', $formData);
    $response->assertSessionHasErrors([
        'files.0.file' => "The files.0.file field must not be greater than " . Document::maxUploadSizeKB() . " kilobytes.",
    ]);
});



it('can update name and description a document from a room ', function () {

    $document = Document::factory()->withCustomAttributes([
        'user' => $this->user,
        'directoryName' => 'rooms',
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

    $response = $this->postToTenant('api.rooms.documents.post', $formData, $this->location->reference_code);
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

it('do not delete the documents directory if it is not empty', function () {
    $file1 = UploadedFile::fake()->image('avatar.png');
    $file2 = UploadedFile::fake()->create('nomdufichier.pdf', 200, 'application/pdf');

    $formData = [

        'files' => [
            [
                'file' => $file1,
                'name' => 'FILE 1 - First file',
                'description' => 'descriptionIMG',
                'typeId' => $this->documentCategory->id,
                'typeSlug' => $this->documentCategory->slug
            ],
            [
                'file' => $file2,
                'name' => 'FILE 2 - Second file',
                'description' => 'descriptionIMG',
                'typeId' => $this->documentCategory->id,
                'typeSlug' => $this->documentCategory->slug
            ],

        ]
    ];

    $response = $this->postToTenant('api.rooms.documents.post', $formData, $this->location->reference_code);
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
        'documentable_id' => $this->location->id,
        'documentable_type' => get_class($this->location)
    ]);

    expect(Storage::disk('tenants')->exists($document->directory))->toBeTrue();
    assertEquals(1, count(Storage::disk('tenants')->files($document->directory)));
});
