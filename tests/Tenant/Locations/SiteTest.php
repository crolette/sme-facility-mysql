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
    $this->siteType = LocationType::factory()->create(['level' => 'site']);
});

it('can render the index sites page', function () {

    Site::factory()->count(3)->create();
    $response = $this->getFromTenant('tenant.sites.index');
    $response->assertOk();


    $response->assertInertia(
        fn($page) =>
        $page->component('tenants/locations/index')
            ->has('items', 3)
    );
});

it('can render the create site page', function () {

    LocationType::factory()->count(2)->create(['level' => 'site']);

    $response = $this->getFromTenant('tenant.sites.create');
    $response->assertOk();


    $response->assertInertia(
        fn($page) => $page->component('tenants/locations/create')
            ->has('locationTypes', 3)
    );
});

it('can create a new site', function () {

    $wallMaterial = CategoryType::factory()->create(['category' => 'wall_materials']);
    $floorMaterial = CategoryType::factory()->create(['category' => 'floor_materials']);

    $formData = [
        'name' => 'New site',
        'surface_floor' => 2569.12,
        'address' => 'Rue du Buisson 22, 4000 Liège, Belgique',
        'floor_material_id' => $floorMaterial->id,
        'surface_walls' => 256.9,
        'wall_material_id' => $wallMaterial->id,
        'description' => 'Description new site',
        'locationType' => $this->siteType->id,
        'need_maintenance' => false
    ];

    $response = $this->postToTenant('api.sites.store', $formData);
    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);

    assertDatabaseCount('sites', 1);
    assertDatabaseCount('maintainables', 1);

    assertDatabaseHas('sites', [
        'location_type_id' => $this->siteType->id,
        'code' => $this->siteType->prefix . '01',
        'address' => 'Rue du Buisson 22, 4000 Liège, Belgique',
        'surface_floor' => 2569.12,
        'floor_material_id' => $floorMaterial->id,
        'surface_walls' => 256.9,
        'wall_material_id' => $wallMaterial->id,
        'reference_code' => $this->siteType->prefix . '01',
    ]);

    assertDatabaseHas('maintainables', [
        'name' => 'New site',
        'description' => 'Description new site',
        'need_maintenance' => false
    ]);
});

it('can create a new site with other matherials', function () {

    $formData = [
        'name' => 'New site',
        'surface_floor' => 2569.12,
        'floor_material_id' => 'other',
        'floor_material_other' => 'Concrete',
        'surface_walls' => 256.9,
        'wall_material_id' => 'other',
        'wall_material_other' => 'Van Gogh',
        'description' => 'Description new site',
        'locationType' => $this->siteType->id,
        'need_maintenance' => false
    ];

    $response = $this->postToTenant('api.sites.store', $formData);
    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);

    assertDatabaseCount('sites', 1);
    assertDatabaseCount('maintainables', 1);

    assertDatabaseHas('sites', [
        'location_type_id' => $this->siteType->id,
        'code' => $this->siteType->prefix . '01',
        'surface_floor' => 2569.12,
        'floor_material_other' => 'Concrete',
        'surface_walls' => 256.9,
        'wall_material_other' => 'Van Gogh',
        'reference_code' => $this->siteType->prefix . '01',
    ]);

    assertDatabaseHas('maintainables', [
        'name' => 'New site',
        'description' => 'Description new site',
        'need_maintenance' => false
    ]);
});

it('can upload several files to site', function () {

    $file1 = UploadedFile::fake()->image('avatar.png');
    $file2 = UploadedFile::fake()->create('nomdufichier.pdf', 200, 'application/pdf');
    CategoryType::factory()->count(2)->create(['category' => 'document']);
    $categoryType = CategoryType::where('category', 'document')->first();

    $formData = [
        'name' => 'New site',
        'description' => 'Description new site',
        'locationType' => $this->siteType->id,
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

    $response = $this->postToTenant('api.sites.store', $formData);
    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);

    assertDatabaseCount('documents', 2);
    assertDatabaseHas('documentables', [
        'document_id' => 1,
        'documentable_type' => 'App\Models\Tenants\Site',
        'documentable_id' => 1
    ]);

    Storage::disk('tenants')->assertExists(Document::first()->path);
});

it('can render the show site page', function () {
    $site = Site::factory()->create();

    $response = $this->getFromTenant('tenant.sites.show', $site);
    $response->assertOk();

    $response->assertInertia(
        fn($page) => $page->component('tenants/locations/show')
            ->has('item')
            ->where('item.location_type.level', $site->locationType->level)
            ->where('item.maintainable.description', $site->maintainable->description)
            ->where('item.code', $site->code)
            ->where('item.location_type.level', 'site')
    );
});

it('can render the update site page', function () {
    LocationType::factory()->count(2)->create(['level' => 'site']);
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
    $site = Site::factory()->create();

    $oldName = $site->maintainable->name;
    $oldDescription = $site->maintainable->description;
    $wallMaterial = CategoryType::factory()->create(['category' => 'wall_materials']);
    $floorMaterial = CategoryType::factory()->create(['category' => 'floor_materials']);

    $formData = [
        'name' => 'New site',
        'address' => 'Rue du Buisson 22, 4000 Liège, Belgique',
        'surface_floor' => 2569.12,
        'surface_walls' => 256.9,
        'wall_material_id' => $wallMaterial->id,
        'floor_material_id' => $floorMaterial->id,
        'description' => 'Description new site',
        'locationType' => $this->siteType->id
    ];

    $response = $this->patchToTenant('api.sites.update', $formData, $site);
    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);

    assertDatabaseCount('sites', 1);
    assertDatabaseCount('maintainables', 1);

    assertDatabaseHas('sites', [
        'location_type_id' => $this->siteType->id,
        'code' => $this->siteType->prefix . '01',
        'address' => 'Rue du Buisson 22, 4000 Liège, Belgique',
        'surface_floor' => 2569.12,
        'surface_walls' => 256.9,
        'wall_material_id' => $wallMaterial->id,
        'floor_material_id' => $floorMaterial->id,
        'reference_code' => $this->siteType->prefix . '01',
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
    LocationType::factory()->create(['level' => 'site']);
    $site = Site::factory()->create();

    $formData = [
        'name' => 'New site',
        'description' => 'Description new site',
        'locationType' => 2
    ];

    $response = $this->patchToTenant('api.sites.update', $formData, $site);
    $response->assertStatus(400)
        ->assertJson(['status' => 'error']);
});

it('can delete a site', function () {
    $site = Site::factory()->create();

    $response = $this->deleteFromTenant('api.sites.destroy', $site->reference_code);
    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);
    assertDatabaseMissing('sites', [
        'reference_code' => $site->reference_code
    ]);

    assertDatabaseMissing('maintainables', [
        'maintainable_id' => $site->id
    ]);
});

it('cannot delete a site which has buildings', function () {
    LocationType::factory()->create(['level' => 'building']);
    $site = Site::factory()->create();
    Building::factory()->create();

    $response = $this->deleteFromTenant('api.sites.destroy', $site->reference_code);
    $response->assertStatus(409);
});

it('cannot delete a site which has related buildings and related floors', function () {
    LocationType::factory()->create(['level' => 'building']);
    LocationType::factory()->create(['level' => 'floor']);
    $site = Site::factory()->create();
    Building::factory()->create();
    Floor::factory()->count(3)->create();

    assertDatabaseCount('sites', 1);
    assertDatabaseCount('buildings', 1);
    assertDatabaseCount('floors', 3);
    assertDatabaseCount('maintainables', 5);

    $response = $this->deleteFromTenant('api.sites.destroy', $site->reference_code);
    $response->assertStatus(409);
});

it('can update name and description of a document from a site ', function () {
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

it('can upload a document to an existing site', function () {
    $file1 = UploadedFile::fake()->image('avatar.png');
    $file2 = UploadedFile::fake()->create('nomdufichier.pdf', 200, 'application/pdf');
    CategoryType::factory()->count(2)->create(['category' => 'document']);
    $site = Site::factory()->create();
    $categoryType = CategoryType::where('category', 'document')->first();

    $formData = [
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

    $response = $this->postToTenant('api.sites.documents.post', $formData, $site);
    $response->assertSessionHasNoErrors();

    assertDatabaseCount('documents', 2);
    assertDatabaseHas('documentables', [
        'document_id' => 1,
        'documentable_type' => 'App\Models\Tenants\Site',
        'documentable_id' => 1
    ]);
});

it('can add pictures to a site', function () {
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
        'imageable_type' => 'App\Models\Tenants\Site',
        'imageable_id' => 1
    ]);
});

it('can retrieve all pictures from a site', function () {
    $site = Site::factory()->create();

    Picture::factory()->forModelAndUser($site, $this->user, 'sites')->create();
    Picture::factory()->forModelAndUser($site, $this->user, 'sites')->create();

    $response = $this->getFromTenant('api.sites.pictures', $site);
    $response->assertStatus(200);
    $data = $response->json('data');
    $this->assertCount(2, $data);
});

it('can retrieve all assets from a site', function () {
    $site = Site::factory()->create();
    CategoryType::factory()->create(['category' => 'asset']);

    Asset::factory()->forLocation($site)->create();
    Asset::factory()->forLocation($site)->create();

    $response = $this->getFromTenant('api.sites.assets', $site);
    $response->assertStatus(200);
    $data = $response->json('data');
    $this->assertCount(2, $data);
});

it('can attach a provider to a site\'s maintainable', function () {
    CategoryType::factory()->create(['category' => 'provider']);
    $provider = Provider::factory()->create();

    $formData = [
        'name' => 'New site',
        'description' => 'Description new site',
        'locationType' => $this->siteType->id,
        'providers' => [['id' => $provider->id]]
    ];

    $response = $this->postToTenant('api.sites.store', $formData);
    $response->assertSessionHasNoErrors();

    $site = Site::first();
    assertCount(1, $site->maintainable->providers);
});
