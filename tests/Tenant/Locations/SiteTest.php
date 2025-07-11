<?php

use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\LocationType;
use App\Models\Tenants\Site;
use App\Models\Tenants\User;
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
    $this->user = User::factory()->create();
    $this->actingAs($this->user, 'tenant');
});

it('can render the index sites page', function () {
    $this->assertAuthenticated();

    LocationType::factory()->count(3)->create(['level' => 'site']);
    LocationType::factory()->count(3)->create(['level' => 'building']);
    Site::factory()->count(3)->create();
    $response = $this->getFromTenant('tenant.sites.index');
    $response->assertOk();


    $response->assertInertia(
        fn($page) =>
        $page->component('tenants/locations/index')
            ->has('locations', 3)
    );
});

it('can render the create site page', function () {

    LocationType::factory()->count(3)->create(['level' => 'site']);

    $response = $this->getFromTenant('tenant.sites.create');
    $response->assertOk();


    $response->assertInertia(
        fn($page) => $page->component('tenants/locations/create')
            ->has('locationTypes', 3)
    );
});

it('can create a new site', function () {

    $locationType = LocationType::factory()->create(['level' => 'site']);

    $formData = [
        'name' => 'New site',
        'description' => 'Description new site',
        'locationType' => $locationType->id,
    ];

    $response = $this->postToTenant('tenant.sites.store', $formData);
    $response->assertStatus(302);
    $response->assertSessionHasNoErrors();

    assertDatabaseCount('sites', 1);
    assertDatabaseCount('maintainables', 1);

    assertDatabaseHas('sites', [
        'location_type_id' => $locationType->id,
        'code' => $locationType->prefix . '01',
        'reference_code' => $locationType->prefix . '01',
    ]);

    assertDatabaseHas('maintainables', [
        'name' => 'New site',
        'description' => 'Description new site',
    ]);
});

it('can upload several files to site', function () {

    $file1 = UploadedFile::fake()->image('avatar.png');
    $file2 = UploadedFile::fake()->create('nomdufichier.pdf', 200, 'application/pdf');
    $locationType = LocationType::factory()->create(['level' => 'site']);
    CategoryType::factory()->count(2)->create(['category' => 'document']);
    $categoryType = CategoryType::where('category', 'document')->first();

    $formData = [
        'name' => 'New site',
        'description' => 'Description new site',
        'locationType' => $locationType->id,
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

    $response = $this->postToTenant('tenant.sites.store', $formData);
    $response->assertSessionHasNoErrors();

    assertDatabaseCount('documents', 2);
    assertDatabaseHas('documentables', [
        'document_id' => 1,
        'documentable_type' => 'App\Models\Tenants\Site',
        'documentable_id' => 1
    ]);

    Storage::disk('tenants')->assertExists(Document::first()->path);
});

it('can render the show site page', function () {



    LocationType::factory()->count(3)->create(['level' => 'site']);
    $site = Site::factory()->create();

    $response = $this->getFromTenant('tenant.sites.show', $site);
    $response->assertOk();

    $response->assertInertia(
        fn($page) => $page->component('tenants/locations/show')
            ->has('location')
            ->where('location.location_type.level', $site->locationType->level)
            ->where('location.maintainable.description', $site->maintainable->description)
            ->where('location.code', $site->code)
            ->where('location.location_type.level', 'site')
    );
});

it('can render the update site page', function () {



    LocationType::factory()->count(3)->create(['level' => 'site']);
    $site = Site::factory()->create();

    $response = $this->getFromTenant('tenant.sites.edit', $site);
    $response->assertOk();


    $response->assertInertia(
        fn($page) => $page->component('tenants/locations/create')
            ->has('location')
            ->has('locationTypes', 3)
            ->where('location.reference_code', $site->reference_code)
    );
});

it('can update a site maintainable and his name and description', function () {



    $locationType = LocationType::factory()->create(['level' => 'site']);
    $site = Site::factory()->create();

    $oldName = $site->maintainable->name;
    $oldDescription = $site->maintainable->description;

    $formData = [
        'name' => 'New site',
        'description' => 'Description new site',
        'locationType' => $locationType->id
    ];

    $response = $this->patchToTenant('tenant.sites.update', $formData, $site);
    $response->assertStatus(302);
    $response->assertSessionHasNoErrors();

    assertDatabaseCount('sites', 1);
    assertDatabaseCount('maintainables', 1);

    assertDatabaseHas('sites', [
        'location_type_id' => $locationType->id,
        'code' => $locationType->prefix . '01',
        'reference_code' => $locationType->prefix . '01',
    ]);

    assertDatabaseHas('maintainables', [
        'name' => 'New site',
        'description' => 'Description new site',
    ]);

    assertDatabaseMissing('maintainables', [
        'name' => $oldName,
        'description' => $oldDescription,
    ]);
});

it('cannot update a site type of an existing site', function () {



    LocationType::factory()->count(2)->create(['level' => 'site']);
    $site = Site::factory()->create();

    $formData = [
        'name' => 'New site',
        'description' => 'Description new site',
        'locationType' => 2
    ];

    $response = $this->patchToTenant('tenant.sites.update', $formData, $site);
    $response->assertRedirect();
    $response->assertSessionHasErrors([
        'locationType' => 'You cannot change the site of a location',
    ]);
});

it('can delete a site', function () {

    LocationType::factory()->count(3)->create(['level' => 'site']);
    $site = Site::factory()->create();

    $response = $this->deleteFromTenant('tenant.sites.destroy', $site->id);
    $response->assertStatus(302);
    assertDatabaseMissing('sites', [
        'reference_code' => $site->reference_code
    ]);

    assertDatabaseMissing('maintainables', [
        'maintainable_id' => $site->id
    ]);
});

it('can delete a site and the related buildings', function () {



    LocationType::factory()->create(['level' => 'site']);
    LocationType::factory()->create(['level' => 'building']);
    $site = Site::factory()->create();
    Building::factory()->create();

    $response = $this->deleteFromTenant('tenant.sites.destroy', $site->id);
    $response->assertStatus(302);
    assertDatabaseEmpty('sites');
    assertDatabaseEmpty('buildings');
    assertDatabaseEmpty('maintainables');
});

it('can delete a site and the related buildings and related floors', function () {



    LocationType::factory()->create(['level' => 'site']);
    LocationType::factory()->create(['level' => 'building']);
    LocationType::factory()->create(['level' => 'floor']);
    $site = Site::factory()->create();
    Building::factory()->create();
    Floor::factory()->count(3)->create();

    assertDatabaseCount('sites', 1);
    assertDatabaseCount('buildings', 1);
    assertDatabaseCount('floors', 3);
    assertDatabaseCount('maintainables', 5);

    $response = $this->deleteFromTenant('tenant.sites.destroy', $site->id);
    $response->assertStatus(302);

    assertDatabaseEmpty('sites');
    assertDatabaseEmpty('buildings');
    assertDatabaseEmpty('floors');
    assertDatabaseEmpty('maintainables');
});

it('can update name and description of a document from a site ', function () {
    LocationType::factory()->count(3)->create(['level' => 'site']);
    CategoryType::factory()->count(2)->create(['category' => 'document']);
    $site = Site::factory()->create();
    $document = Document::factory()->withCustomAttributes([
        'user' => $this->user,
        'directoryName' => 'site',
        'model' => $site,
    ])->create();
    $site->documents()->attach($document);

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
