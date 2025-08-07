<?php

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
use function PHPUnit\Framework\assertCount;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseMissing;


beforeEach(function () {
    $this->user = User::factory()->create();
    $this->user->assignRole('Admin');
    $this->actingAs($this->user, 'tenant');
    $this->siteType = LocationType::factory()->create(['level' => 'site']);
    $this->buildingType = LocationType::factory()->create(['level' => 'building']);
    $this->floorType = LocationType::factory()->create(['level' => 'floor']);
    Site::factory()->create();
    $this->building = Building::factory()->create();
});

it('can render the index floors page', function () {
    Floor::factory()->count(3)->create();
    $response = $this->getFromTenant('tenant.floors.index');
    $response->assertOk();


    $response->assertInertia(
        fn($page) =>
        $page->component('tenants/locations/index')
            ->has('locations', 3)
            ->has('locations.0.maintainable')
    );
});

it('can render the create floor page', function () {
    LocationType::factory()->count(2)->create(['level' => 'floor']);
    Building::factory()->count(2)->create();


    $response = $this->getFromTenant('tenant.floors.create');
    $response->assertOk();


    $response->assertInertia(
        fn($page) => $page->component('tenants/locations/create')
            ->has('levelTypes', 3)
            ->has('locationTypes', 3)
    );
});

it('can create a new floor', function () {
    $formData = [
        'name' => 'New floor',
        'surface_floor' => 2569.12,
        'surface_walls' => 256.9,
        'description' => 'Description new floor',
        'levelType' => $this->building->id,
        'locationType' => $this->floorType->id
    ];

    $response = $this->postToTenant('api.floors.store', $formData);
    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);

    $floor = Floor::first();

    assertDatabaseCount('sites', 1);
    assertDatabaseCount('buildings', 1);
    assertDatabaseCount('floors', 1);
    assertDatabaseCount('maintainables', 3);


    assertDatabaseHas('floors', [
        'location_type_id' => $this->floorType->id,
        'code' => $this->floorType->prefix . '01',
        'surface_floor' => 2569.12,
        'surface_walls' => 256.9,
        'reference_code' => $this->building->reference_code . '-' . $this->floorType->prefix . '01',
        'level_id' => $this->building->id
    ]);

    assertDatabaseHas('maintainables', [
        'maintainable_type' => get_class($floor),
        'maintainable_id' => $floor->id,
        'name' => 'New floor',
        'description' => 'Description new floor',
    ]);
});

it('can attach a provider to a floor\'s maintainable', function () {
    $provider = Provider::factory()->create();

    $formData = [
        'name' => 'New floor',
        'description' => 'Description new floor',
        'levelType' => $this->building->id,
        'locationType' => $this->floorType->id,
        'providers' => [['id' => $provider->id]]
    ];

    $response = $this->postToTenant('api.floors.store', $formData);
    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);

    $floor = Floor::first();
    assertCount(1, $floor->maintainable->providers);
});

it('can upload several files to building', function () {

    $file1 = UploadedFile::fake()->image('avatar.png');
    $file2 = UploadedFile::fake()->create('nomdufichier.pdf', 200, 'application/pdf');
    CategoryType::factory()->count(2)->create(['category' => 'document']);
    $categoryType = CategoryType::where('category', 'document')->first();

    $formData = [
        'name' => 'New room',
        'description' => 'Description new room',
        'levelType' => $this->building->id,
        'locationType' => $this->floorType->id,
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

    $response = $this->postToTenant('api.floors.store', $formData);
    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);

    assertDatabaseCount('documents', 2);
    assertDatabaseHas('documentables', [
        'document_id' => 1,
        'documentable_type' => 'App\Models\Tenants\Floor',
        'documentable_id' => 1
    ]);

    Storage::disk('tenants')->assertExists(Document::first()->path);
});

it('can render the show floor page', function () {
    $floor = Floor::factory()->create();

    $response = $this->getFromTenant('tenant.floors.show', $floor);
    $response->assertOk();

    $response->assertInertia(
        fn($page) => $page->component('tenants/locations/show')
            ->has('item')
            ->where('item.location_type.level', $floor->locationType->level)
            ->where('item.maintainable.description', $floor->maintainable->description)
            ->where('item.code', $floor->code)
            ->where('item.reference_code', $floor->reference_code)
            ->where('item.location_type.level', 'floor')
    );
});

it('can render the update floor page', function () {
    LocationType::factory()->count(2)->create(['level' => 'building']);
    LocationType::factory()->count(2)->create(['level' => 'floor']);
    Building::factory()->count(2)->create();
    $floor = Floor::factory()->create();

    $response = $this->getFromTenant('tenant.floors.edit', $floor);
    $response->assertOk();


    $response->assertInertia(
        fn($page) => $page->component('tenants/locations/create')
            ->has('location')
            ->has('location.building')
            ->has('levelTypes', 3)
            ->has('locationTypes', 3)
            ->where('location.reference_code', $floor->reference_code)
    );
});

it('can update a floor maintainable', function () {

    $floor = Floor::factory()->create();

    $oldName = $floor->maintainable->name;
    $oldDescription = $floor->maintainable->description;

    $formData = [
        'name' => 'New floor',
        'surface_floor' => 2569.12,
        'surface_walls' => 256.9,
        'description' => 'Description new floor',
        'levelType' => $this->building->id,
        'locationType' => $this->floorType->id
    ];

    $response = $this->patchToTenant('api.floors.update', $formData, $floor);
    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);

    assertDatabaseCount('sites', 1);
    assertDatabaseCount('buildings', 1);
    assertDatabaseCount('floors', 1);
    assertDatabaseCount('maintainables', 3);

    assertDatabaseHas('floors', [
        'location_type_id' => $this->floorType->id,
        'level_id' => $this->building->id,
        'code' => $this->floorType->prefix . '01',
        'surface_floor' => 2569.12,
        'surface_walls' => 256.9,
        'reference_code' => $this->building->reference_code . '-' . $this->floorType->prefix . '01',
    ]);

    assertDatabaseHas('maintainables', [
        'name' => 'New floor',
        'description' => 'Description new floor',
    ]);

    assertDatabaseMissing('maintainables', [
        'name' => $oldName,
        'description' => $oldDescription,
    ]);
});

it('fails when update of an existing floor with a non existing floor type', function () {
    $floor = Floor::factory()->create();

    $formData = [
        'name' => 'New site',
        'description' => 'Description new site',
        'levelType' => $this->building->level_id,
        'locationType' => 5
    ];

    $response = $this->patchToTenant('api.floors.update', $formData, $floor);
    $response->assertSessionHasErrors([
        'locationType' => 'The selected location type is invalid.',
    ]);
});

it('cannot update a floor type of an existing floor', function () {
    LocationType::factory()->create(['level' => 'floor']);

    $floor = Floor::factory()->create();

    $formData = [
        'name' => 'New site',
        'description' => 'Description new site',
        'levelType' => $this->building->level_id,
        'locationType' => 4
    ];

    $response = $this->patchToTenant('api.floors.update', $formData, $floor);
    $response->assertStatus(400)
        ->assertJson(['status' => 'error']);
});

it('can delete a floor and his maintainable', function () {
    $floor = Floor::factory()->create();

    assertDatabaseHas('floors', [
        'level_id' => $this->building->id,
        'code' => $floor->code
    ]);

    $response = $this->deleteFromTenant('api.floors.destroy', $floor->reference_code);
    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);
    assertDatabaseMissing('floors', [
        'reference_code' => $floor->reference_code
    ]);

    assertDatabaseMissing('maintainables', [
        'maintainable_type' => get_class($floor),
        'maintainable_id' => $floor->id
    ]);

    assertDatabaseCount('sites', 1);
    assertDatabaseCount('buildings', 1);
    assertDatabaseEmpty('floors');
    assertDatabaseCount('maintainables', 2);
});

it('can add pictures to a floor', function () {
    $floor = Floor::factory()->create();
    $file1 = UploadedFile::fake()->image('avatar.png');
    $file2 = UploadedFile::fake()->image('test.jpg');

    $formData = [
        'pictures' => [
            $file1,
            $file2
        ]
    ];

    $response = $this->postToTenant('api.floors.pictures.post', $formData, $floor);
    $response->assertSessionHasNoErrors();
    assertDatabaseCount('pictures', 2);
    assertDatabaseHas('pictures', [
        'imageable_type' => 'App\Models\Tenants\Floor',
        'imageable_id' => 1
    ]);
});

it('can retrieve all pictures from a floor', function () {
    $floor = Floor::factory()->create();

    Picture::factory()->forModelAndUser($floor, $this->user, 'floors')->create();
    Picture::factory()->forModelAndUser($floor, $this->user, 'floors')->create();

    $response = $this->getFromTenant('api.floors.pictures', $floor);
    $response->assertStatus(200);
    $data = $response->json('data');
    $this->assertCount(2, $data);
});

it('can retrieve all assets from a floor', function () {
    $floor = Floor::factory()->create();
    CategoryType::factory()->create(['category' => 'asset']);

    Asset::factory()->forLocation($floor)->create();
    Asset::factory()->forLocation($floor)->create();

    $response = $this->getFromTenant('api.floors.assets', $floor);
    $response->assertStatus(200);
    $data = $response->json('data');
    $this->assertCount(2, $data);
});
