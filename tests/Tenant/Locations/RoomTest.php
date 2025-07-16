<?php

use App\Models\LocationType;
use App\Models\Tenants\Room;
use App\Models\Tenants\Site;
use App\Models\Tenants\User;
use App\Models\Tenants\Floor;
use App\Models\Tenants\Picture;

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
    LocationType::factory()->count(1)->create(['level' => 'site']);
    LocationType::factory()->count(1)->create(['level' => 'building']);
    LocationType::factory()->count(1)->create(['level' => 'floor']);
    LocationType::factory()->count(1)->create(['level' => 'room']);
    CategoryType::factory()->count(2)->create(['category' => 'document']);
    $this->site = Site::factory()->create();
    $this->building = Building::factory()->create();
    $this->floor = Floor::factory()->create();
});


it('can render the index floors page', function () {
    $room = Room::factory()
        ->for(LocationType::where('level', 'room')->first())
        ->for(Floor::first())
        ->create();
    Room::factory()
        ->for(LocationType::where('level', 'room')->first())
        ->for(Floor::first())
        ->create();

    $response = $this->getFromTenant('tenant.rooms.index');
    $response->assertOk();


    $response->assertInertia(
        fn($page) =>
        $page->component('tenants/locations/index')
            ->has('locations', 2)
            ->has('locations.0.maintainable')
            ->where('locations.0.floor.id', $room->floor->id)
    );
});


it('can render the create floor page', function () {
    LocationType::factory()->create(['level' => 'room']);
    Floor::factory()->count(3)->create();


    $response = $this->getFromTenant('tenant.rooms.create');
    $response->assertOk();


    $response->assertInertia(
        fn($page) => $page->component('tenants/locations/create')
            ->has('levelTypes', 4)
            ->has('levelTypes.0.maintainable.name')
            ->has('locationTypes', 2)
    );
});

it('can create a new room', function () {
    $location = LocationType::where('level', 'room')->first();

    $formData = [
        'name' => 'New room',
        'description' => 'Description new room',
        'levelType' => $this->floor->id,
        'locationType' => $location->id
    ];

    $response = $this->postToTenant('tenant.rooms.store', $formData);
    $response->assertStatus(302);
    $response->assertSessionHasNoErrors();

    $room = Room::first();

    assertDatabaseCount('sites', 1);
    assertDatabaseCount('buildings', 1);
    assertDatabaseCount('floors', 1);
    assertDatabaseCount('rooms', 1);
    assertDatabaseCount('maintainables', 4);


    assertDatabaseHas('rooms', [
        'location_type_id' => $location->id,
        'code' => $location->prefix . '001',
        'reference_code' => $this->floor->reference_code . '-' . $location->prefix . '001',
        'level_id' => $this->floor->id
    ]);

    assertDatabaseHas('maintainables', [
        'maintainable_type' => get_class($room),
        'maintainable_id' => $room->id,
        'name' => 'New room',
        'description' => 'Description new room',
    ]);
});

it('can upload several files to site', function () {


    $location = LocationType::where('level', 'room')->first();
    $file1 = UploadedFile::fake()->image('avatar.png');
    $file2 = UploadedFile::fake()->create('nomdufichier.pdf', 200, 'application/pdf');
    CategoryType::factory()->count(2)->create(['category' => 'document']);
    $categoryType = CategoryType::where('category', 'document')->first();

    $formData = [
        'name' => 'New room',
        'description' => 'Description new room',
        'levelType' => $this->floor->id,
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

    $response = $this->postToTenant('tenant.rooms.store', $formData);
    $response->assertSessionHasNoErrors();

    assertDatabaseCount('documents', 2);
    assertDatabaseHas('documentables', [
        'document_id' => 1,
        'documentable_type' => 'App\Models\Tenants\Room',
        'documentable_id' => 1
    ]);

    Storage::disk('tenants')->assertExists(Document::first()->path);
});

it('can render the show room page', function () {

    $room = Room::factory()
        ->for(LocationType::where('level', 'room')->first())
        ->for(Floor::first())
        ->create();

    $response = $this->getFromTenant('tenant.rooms.show', $room);
    $response->assertOk();

    $response->assertInertia(
        fn($page) => $page->component('tenants/locations/show')
            ->has('location')
            ->where('location.location_type.level', $room->locationType->level)
            ->where('location.maintainable.description', $room->maintainable->description)
            ->where('location.code', $room->code)
            ->where('location.reference_code', $room->reference_code)
            ->where('location.location_type.level', 'room')
    );
});


it('can render the update floor page', function () {
    LocationType::factory()->count(2)->create(['level' => 'room']);
    Floor::factory()->count(2)->create();
    $room = Room::factory()
        ->for(LocationType::where('level', 'room')->first())
        ->for(Floor::first())
        ->create();

    $response = $this->getFromTenant('tenant.rooms.edit', $room);
    $response->assertOk();


    $response->assertInertia(
        fn($page) => $page->component('tenants/locations/create')
            ->has('location')
            ->has('location.floor')
            ->has('levelTypes', 3)
            ->has('locationTypes', 3)
            ->where('location.reference_code', $room->reference_code)
    );
});


it('can update a room maintainable', function () {

    $locationType = LocationType::where('level', 'room')->first();
    $room = Room::factory()
        ->for(LocationType::where('level', 'room')->first())
        ->for(Floor::first())
        ->create();

    $oldName = $room->maintainable->name;
    $oldDescription = $room->maintainable->description;

    $formData = [
        'name' => 'New room',
        'description' => 'Description new room',
        'levelType' => $this->floor->id,
        'locationType' => $locationType->id
    ];

    $response = $this->patchToTenant('tenant.rooms.update', $formData, $room);
    $response->assertStatus(302);
    $response->assertSessionHasNoErrors();

    assertDatabaseCount('sites', 1);
    assertDatabaseCount('buildings', 1);
    assertDatabaseCount('floors', 1);
    assertDatabaseCount('rooms', 1);
    assertDatabaseCount('maintainables', 4);

    assertDatabaseHas('rooms', [
        'location_type_id' => $locationType->id,
        'level_id' => $this->floor->id,
        'code' => $locationType->prefix . '001',
        'reference_code' => $this->floor->reference_code . '-' . $locationType->prefix . '001',
    ]);

    assertDatabaseHas('maintainables', [
        'name' => 'New room',
        'description' => 'Description new room',
    ]);

    assertDatabaseMissing('maintainables', [
        'name' => $oldName,
        'description' => $oldDescription,
    ]);
});


it('cannot update a room type of an existing room', function () {

    LocationType::factory()->count(2)->create(['level' => 'room']);
    $floor = Floor::factory()->create();
    $room = Room::factory()
        ->for(LocationType::where('level', 'room')->first())
        ->for(Floor::first())
        ->create();

    $formData = [
        'name' => 'New site',
        'description' => 'Description new site',
        'levelType' => $floor->level_id,
        'locationType' => 5
    ];

    $response = $this->patchToTenant('tenant.rooms.update', $formData, $room);
    $response->assertRedirect();
    $response->assertSessionHasErrors([
        'locationType' => 'You cannot change the type of a location',
    ]);
});


it('can delete a room and his maintainable', function () {
    $room = Room::factory()
        ->for(LocationType::where('level', 'room')->first())
        ->for(Floor::first())
        ->create();

    assertDatabaseHas('rooms', [
        'level_id' => $this->floor->id,
        'code' => $room->code
    ]);

    $response = $this->deleteFromTenant('tenant.rooms.destroy', $room->id);
    $response->assertStatus(302);
    assertDatabaseMissing('rooms', [
        'reference_code' => $room->reference_code
    ]);

    assertDatabaseMissing('maintainables', [
        'maintainable_type' => get_class($room),
        'maintainable_id' => $room->id
    ]);

    assertDatabaseCount('sites', 1);
    assertDatabaseCount('buildings', 1);
    assertDatabaseCount('floors', 1);
    assertDatabaseEmpty('rooms');
    assertDatabaseCount('maintainables', 3);
});

it('can update name and description of a document from a site ', function () {
    $room = Room::factory()
        ->for(LocationType::where('level', 'room')->first())
        ->for(Floor::first())
        ->create();

    $document = Document::factory()->withCustomAttributes([
        'user' => $this->user,
        'directoryName' => 'room',
        'model' => $room,
    ])->create();
    $room->documents()->attach($document);

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

it('can upload a document to an existing room', function () {

    $file1 = UploadedFile::fake()->image('avatar.png');
    $file2 = UploadedFile::fake()->create('nomdufichier.pdf', 200, 'application/pdf');
    $locationType = LocationType::factory()->create(['level' => 'site']);
    CategoryType::factory()->count(2)->create(['category' => 'document']);
    $room = Room::factory()
        ->for(LocationType::where('level', 'room')->first())
        ->for(Floor::first())
        ->create();
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

    $response = $this->postToTenant('api.rooms.documents.post', $formData, $room);
    $response->assertSessionHasNoErrors();

    assertDatabaseCount('documents', 2);
    assertDatabaseHas('documentables', [
        'document_id' => 1,
        'documentable_type' => 'App\Models\Tenants\Room',
        'documentable_id' => 1
    ]);
    assertDatabaseHas('documentables', [
        'document_id' => 2,
        'documentable_type' => 'App\Models\Tenants\Room',
        'documentable_id' => 1
    ]);
});

it('can add pictures to a room', function () {
    $room = Room::factory()
        ->for(LocationType::where('level', 'room')->first())
        ->for(Floor::first())
        ->create();
    $file1 = UploadedFile::fake()->image('avatar.png');
    $file2 = UploadedFile::fake()->image('test.jpg');

    $formData = [
        'pictures' => [
            $file1,
            $file2
        ]
    ];

    $response = $this->postToTenant('api.rooms.pictures.post', $formData, $room);
    $response->assertSessionHasNoErrors();
    assertDatabaseCount('pictures', 2);
    assertDatabaseHas('pictures', [
        'imageable_type' => 'App\Models\Tenants\Room',
        'imageable_id' => 1
    ]);
});

it('can retrieve all pictures from a room', function () {
    $room = Room::factory()
        ->for(LocationType::where('level', 'room')->first())
        ->for(Floor::first())
        ->create();

    Picture::factory()->forModelAndUser($room, $this->user, 'rooms')->create();
    Picture::factory()->forModelAndUser($room, $this->user, 'rooms')->create();

    assertDatabaseCount('pictures', 2);

    $response = $this->getFromTenant('api.rooms.pictures', $room);
    $response->assertStatus(200);
    $data = $response->json('data');
    $this->assertCount(2, $data);
});
