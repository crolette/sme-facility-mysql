<?php

use App\Models\LocationType;
use App\Models\Tenants\Site;
use App\Models\Tenants\User;
use App\Models\Tenants\Floor;
use App\Models\Tenants\Picture;
use App\Models\Tenants\Building;
use App\Models\Tenants\Document;
use Illuminate\Http\UploadedFile;
use App\Models\Central\CategoryType;
use App\Models\Tenants\Asset;
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

it('can render the index buildings page', function () {
    LocationType::factory()->count(1)->create(['level' => 'site']);
    LocationType::factory()->count(2)->create(['level' => 'building']);
    Site::factory()->create();
    Building::factory()->count(3)->create();
    $response = $this->getFromTenant('tenant.buildings.index');
    $response->assertOk();


    $response->assertInertia(
        fn($page) =>
        $page->component('tenants/locations/index')
            ->has('locations', 3)
            ->has('locations.0.maintainable')
    );
});

it('can render the create building page', function () {

    LocationType::factory()->count(1)->create(['level' => 'site']);
    LocationType::factory()->count(2)->create(['level' => 'building']);

    $response = $this->getFromTenant('tenant.buildings.create');
    $response->assertOk();


    $response->assertInertia(
        fn($page) => $page->component('tenants/locations/create')
            ->has('levelTypes')
            ->has('locationTypes', 2)
    );
});

it('can create a new building', function () {

    $siteType = LocationType::factory()->create(['level' => 'site']);
    $buildingType = LocationType::factory()->create(['level' => 'building']);
    $site = Site::factory()->create();

    $formData = [
        'name' => 'New building',
        'surface_floor' => 2569.12,
        'surface_walls' => 256.9,
        'description' => 'Description new building',
        'levelType' => $site->id,
        'locationType' => $buildingType->id
    ];

    $response = $this->postToTenant('tenant.buildings.store', $formData);
    $response->assertStatus(302);
    $response->assertSessionHasNoErrors();

    assertDatabaseCount('buildings', 1);
    assertDatabaseCount('maintainables', 2);

    assertDatabaseHas('buildings', [
        'location_type_id' => $buildingType->id,
        'code' => $buildingType->prefix . '01',
        'surface_floor' => 2569.12,
        'surface_walls' => 256.9,
        'reference_code' => $site->reference_code . '-' . $buildingType->prefix . '01',
        'level_id' => $siteType->id
    ]);

    assertDatabaseHas('maintainables', [
        'name' => 'New building',
        'description' => 'Description new building',
    ]);
});

it('can upload several files to building', function () {

    LocationType::factory()->create(['level' => 'site']);
    $location = LocationType::factory()->create(['level' => 'building']);
    $site = Site::factory()->create();
    $file1 = UploadedFile::fake()->image('avatar.png');
    $file2 = UploadedFile::fake()->create('nomdufichier.pdf', 200, 'application/pdf');
    CategoryType::factory()->count(2)->create(['category' => 'document']);
    $categoryType = CategoryType::where('category', 'document')->first();

    $formData = [
        'name' => 'New room',
        'description' => 'Description new room',
        'levelType' => $site->id,
        'locationType' => $location->id,
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

    $response = $this->postToTenant('tenant.buildings.store', $formData);
    $response->assertSessionHasNoErrors();

    assertDatabaseCount('documents', 2);
    assertDatabaseHas('documentables', [
        'document_id' => 1,
        'documentable_type' => 'App\Models\Tenants\Building',
        'documentable_id' => 1
    ]);

    Storage::disk('tenants')->assertExists(Document::first()->path);
});

it('can render the show building page', function () {



    LocationType::factory()->count(3)->create(['level' => 'site']);
    LocationType::factory()->count(3)->create(['level' => 'building']);
    Site::factory()->create();
    $building = Building::factory()->create();

    $response = $this->getFromTenant('tenant.buildings.show', $building);
    $response->assertOk();

    $response->assertInertia(
        fn($page) => $page->component('tenants/locations/show')
            ->has('location')
            ->where('location.location_type.level', $building->locationType->level)
            ->where('location.maintainable.description', $building->maintainable->description)
            ->where('location.code', $building->code)
            ->where('location.reference_code', $building->reference_code)
            ->where('location.location_type.level', 'building')
    );
});

it('can render the update building page', function () {



    LocationType::factory()->count(3)->create(['level' => 'site']);
    LocationType::factory()->count(3)->create(['level' => 'building']);
    $site = Site::factory()->create();
    $building = Building::factory()->create();

    $response = $this->getFromTenant('tenant.buildings.edit', $building);
    $response->assertOk();


    $response->assertInertia(
        fn($page) => $page->component('tenants/locations/create')
            ->has('location')
            ->has('location.site')
            ->has('levelTypes', 1)
            ->has('locationTypes', 3)
            ->where('location.reference_code', $building->reference_code)
    );
});

it('can update a building', function () {



    $level = LocationType::factory()->create(['level' => 'site']);
    $buildingType = LocationType::factory()->create(['level' => 'building']);
    $site = Site::factory()->create();
    $building = Building::factory()->create();

    $oldName = $building->maintainable->name;
    $oldDescription = $building->maintainable->description;

    $formData = [
        'name' => 'New building',
        'surface_floor' => 2569.12,
        'surface_walls' => 256.9,
        'description' => 'Description new building',
        'levelType' => $level->id,
        'locationType' => $buildingType->id
    ];

    $response = $this->patchToTenant('tenant.buildings.update', $formData, $building);
    $response->assertStatus(302);
    $response->assertSessionHasNoErrors();


    assertDatabaseCount('sites', 1);
    assertDatabaseCount('buildings', 1);
    assertDatabaseCount('maintainables', 2);

    assertDatabaseHas('buildings', [
        'location_type_id' => $buildingType->id,
        'level_id' => $site->id,
        'surface_floor' => 2569.12,
        'surface_walls' => 256.9,
        'code' => $buildingType->prefix . '01',
        'reference_code' => $site->code . '-' . $buildingType->prefix . '01',
    ]);

    assertDatabaseHas('maintainables', [
        'name' => 'New building',
        'description' => 'Description new building',
    ]);

    assertDatabaseMissing('maintainables', [
        'name' => $oldName,
        'description' => $oldDescription,
    ]);
});

it('cannot update a building type of an existing building', function () {



    LocationType::factory()->count(2)->create(['level' => 'site']);
    LocationType::factory()->count(2)->create(['level' => 'building']);
    Site::factory()->count(2)->create();
    $building = Building::factory()->create();

    $formData = [
        'name' => 'New building updated',
        'description' => 'Description new building updated',
        'levelType' => $building->level_id,
        'locationType' => 4
    ];

    $response = $this->patchToTenant('tenant.buildings.update', $formData, $building);
    $response->assertRedirect();
    $response->assertSessionHasErrors([
        'locationType' => 'You cannot change the building type of a location',
    ]);
});

it('can delete a building', function () {

    LocationType::factory()->create(['level' => 'site']);
    LocationType::factory()->create(['level' => 'building']);
    $site = Site::factory()->create();
    $building = Building::factory()->create();

    assertDatabaseHas('buildings', [
        'level_id' => $site->id,
        'code' => $building->code
    ]);

    $response = $this->deleteFromTenant('tenant.buildings.destroy', $building->code);
    $response->assertStatus(302);
    assertDatabaseMissing('buildings', [
        'reference_code' => $building->reference_code
    ]);

    assertDatabaseMissing('maintainables', [
        'maintainable_type' => get_class($building),
        'maintainable_id' => $building->id
    ]);

    assertDatabaseCount('sites', 1);
    assertDatabaseEmpty('buildings');
    assertDatabaseCount('maintainables', 1);
});

it('cannot delete a building which has related floors', function () {

    LocationType::factory()->create(['level' => 'site']);
    LocationType::factory()->create(['level' => 'building']);
    LocationType::factory()->create(['level' => 'floor']);
    $site = Site::factory()->create();
    $building = Building::factory()->create();
    Floor::factory()->count(2)->create();

    assertDatabaseHas('buildings', [
        'level_id' => $site->id,
        'code' => $building->code
    ]);

    assertDatabaseCount('sites', 1);
    assertDatabaseCount('buildings', 1);
    assertDatabaseCount('floors', 2);
    assertDatabaseCount('maintainables', 4);

    $response = $this->deleteFromTenant('tenant.buildings.destroy', $building->code);
    $response->assertStatus(409);
});

it('can update name and description of a document from a building ', function () {
    CategoryType::factory()->count(2)->create(['category' => 'document']);
    LocationType::factory()->create(['level' => 'site']);
    LocationType::factory()->create(['level' => 'building']);
    LocationType::factory()->create(['level' => 'floor']);
    Site::factory()->create();
    $building = Building::factory()->create();

    $document = Document::factory()->withCustomAttributes([
        'user' => $this->user,
        'directoryName' => 'buildings',
        'model' => $building,
    ])->create();
    $building->documents()->attach($document);

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
        'category_type_id' => $categoryType->id,
    ]);
});

it('can add pictures to a building', function () {
    LocationType::factory()->create(['level' => 'site']);
    LocationType::factory()->create(['level' => 'building']);
    LocationType::factory()->create(['level' => 'floor']);
    Site::factory()->create();
    $building = Building::factory()->create();

    $file1 = UploadedFile::fake()->image('avatar.png');
    $file2 = UploadedFile::fake()->image('test.jpg');

    $formData = [
        'pictures' => [
            $file1,
            $file2
        ]
    ];

    $response = $this->postToTenant('api.buildings.pictures.post', $formData, $building);
    $response->assertSessionHasNoErrors();
    assertDatabaseCount('pictures', 2);
    assertDatabaseHas('pictures', [
        'imageable_type' => 'App\Models\Tenants\Building',
        'imageable_id' => 1
    ]);
});

it('can retrieve all pictures from a building', function () {
    LocationType::factory()->create(['level' => 'site']);
    LocationType::factory()->create(['level' => 'building']);
    LocationType::factory()->create(['level' => 'floor']);
    Site::factory()->create();
    $building = Building::factory()->create();

    Picture::factory()->forModelAndUser($building, $this->user, 'buildings')->create();
    Picture::factory()->forModelAndUser($building, $this->user, 'buildings')->create();

    assertDatabaseCount('pictures', 2);

    $response = $this->getFromTenant('api.buildings.pictures', $building);
    $response->assertStatus(200);
    $data = $response->json('data');
    $this->assertCount(2, $data);
});

it('can retrieve all assets from a building', function () {
    LocationType::factory()->create(['level' => 'site']);
    LocationType::factory()->create(['level' => 'building']);
    LocationType::factory()->create(['level' => 'floor']);
    Site::factory()->create();
    $building = Building::factory()->create();

    CategoryType::factory()->create(['category' => 'asset']);
    Asset::factory()->forLocation($building)->create();
    Asset::factory()->forLocation($building)->create();


    $response = $this->getFromTenant('api.buildings.assets', $building);
    $response->assertStatus(200);
    $data = $response->json('data');
    $this->assertCount(2, $data);
});
