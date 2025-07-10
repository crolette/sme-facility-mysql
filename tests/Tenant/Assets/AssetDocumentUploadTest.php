<?php

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
    LocationType::factory()->create(['level' => 'room']);
    CategoryType::factory()->count(2)->create(['category' => 'document']);
    $this->categoryType = CategoryType::factory()->create(['category' => 'asset']);
    CategoryType::factory()->count(2)->create(['category' => 'asset']);
    $this->site = Site::factory()->create();
    $this->building = Building::factory()->create();
    $this->floor = Floor::factory()->create();

    $this->room = Room::factory()
        ->for(LocationType::where('level', 'room')->first())
        ->for(Floor::first())
        ->create();
    $this->user = User::factory()->create();
    $this->actingAs($this->user, 'tenant');
});

it('can upload several files', function () {

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

    $response = $this->postToTenant('tenant.assets.store', $formData);
    $response->assertSessionHasNoErrors();
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

    $response = $this->postToTenant('tenant.assets.store', $formData);
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

    $response = $this->postToTenant('tenant.assets.store', $formData);
    $response->assertSessionHasErrors([
        'files.0.file' => "The files.0.file field must not be greater than " . Document::maxUploadSizeKB() . " kilobytes.",
    ]);
});

it('can upload a file and is saved in the DB', function () {

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

    $response = $this->postToTenant('tenant.assets.store', $formData);
    $response->assertSessionHasNoErrors();
});
