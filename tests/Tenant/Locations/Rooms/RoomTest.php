<?php

use App\Models\LocationType;
use App\Models\Tenants\Room;
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
use function PHPUnit\Framework\assertJson;
use function Pest\Laravel\assertDatabaseHas;
use function PHPUnit\Framework\assertEquals;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseMissing;
use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertNull;

beforeEach(function () {
    $this->user = User::factory()->withRole('Admin')->create();
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


it('can render the index rooms page', function () {
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
            ->has('items', 2)
            ->has('items.0.maintainable')
            ->where('items.0.floor.id', $room->floor->id)
    );
});


it('can render the create room page', function () {
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
    $wallMaterial = CategoryType::factory()->create(['category' => 'wall_materials']);
    $floorMaterial = CategoryType::factory()->create(['category' => 'floor_materials']);

    $formData = [
        'name' => 'New room',
        'surface_floor' => 2569.12,
        'floor_material_id' => $floorMaterial->id,
        'surface_walls' => 256.9,
        'wall_material_id' => $wallMaterial->id,
        'description' => 'Description new room',
        'levelType' => $this->floor->id,
        'locationType' => $location->id
    ];

    $response = $this->postToTenant('api.rooms.store', $formData);
    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);

    $room = Room::first();

    assertDatabaseCount('rooms', 1);
    assertDatabaseCount('maintainables', 4);


    assertDatabaseHas('rooms', [
        'location_type_id' => $location->id,
        'code' => $location->prefix . '001',
        'surface_floor' => 2569.12,
        'floor_material_id' => $floorMaterial->id,
        'surface_walls' => 256.9,
        'wall_material_id' => $wallMaterial->id,
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

it('can create a new room with other materials', function () {
    $location = LocationType::where('level', 'room')->first();

    $formData = [
        'name' => 'New room',
        'surface_floor' => 2569.12,
        'floor_material_id' => 'other',
        'floor_material_other' => 'Concrete',
        'surface_walls' => 256.9,
        'wall_material_id' => 'other',
        'wall_material_other' => 'Van Gogh',
        'description' => 'Description new room',
        'levelType' => $this->floor->id,
        'locationType' => $location->id
    ];

    $response = $this->postToTenant('api.rooms.store', $formData);
    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);

    $room = Room::first();

    assertDatabaseCount('rooms', 1);
    assertDatabaseCount('maintainables', 4);

    assertNull($room->floor_material_id);
    assertNull($room->wall_material_id);

    assertDatabaseHas('rooms', [
        'location_type_id' => $location->id,
        'code' => $location->prefix . '001',
        'surface_floor' => 2569.12,
        'floor_material_other' => 'Concrete',
        'surface_walls' => 256.9,
        'wall_material_other' => 'Van Gogh',
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

it('can attach a provider to a building\'s maintainable', function () {
    CategoryType::factory()->create(['category' => 'provider']);
    $provider = Provider::factory()->create();

    $location = LocationType::where('level', 'room')->first();

    $formData = [
        'name' => 'New room',
        'description' => 'Description new room',
        'levelType' => $this->floor->id,
        'locationType' => $location->id,
        'providers' => [['id' => $provider->id]]
    ];

    $response = $this->postToTenant('api.rooms.store', $formData);
    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);

    $room = Room::first();
    assertCount(1, $room->maintainable->providers);
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

    $response = $this->postToTenant('api.rooms.store', $formData);
    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);

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
            ->has('item')
            ->where('item.location_type.level', $room->locationType->level)
            ->where('item.maintainable.description', $room->maintainable->description)
            ->where('item.code', $room->code)
            ->where('item.reference_code', $room->reference_code)
            ->where('item.location_type.level', 'room')
    );
});


it('can render the update room page', function () {
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
    $wallMaterial = CategoryType::factory()->create(['category' => 'wall_materials']);
    $floorMaterial = CategoryType::factory()->create(['category' => 'floor_materials']);
    $locationType = LocationType::where('level', 'room')->first();
    $room = Room::factory()
        ->for(LocationType::where('level', 'room')->first())
        ->for(Floor::first())
        ->create();

    $oldName = $room->maintainable->name;
    $oldDescription = $room->maintainable->description;

    $formData = [
        'name' => 'New room',
        'surface_floor' => 2569.12,
        'surface_walls' => 256.9,
        'wall_material_id' => $wallMaterial->id,
        'floor_material_id' => $floorMaterial->id,
        'description' => 'Description new room',
        'levelType' => $this->floor->id,
        'locationType' => $locationType->id
    ];

    $response = $this->patchToTenant('api.rooms.update', $formData, $room);
    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);

    assertDatabaseCount('rooms', 1);
    assertDatabaseCount('maintainables', 4);

    assertDatabaseHas('rooms', [
        'location_type_id' => $locationType->id,
        'level_id' => $this->floor->id,
        'surface_floor' => 2569.12,
        'surface_walls' => 256.9,
        'wall_material_id' => $wallMaterial->id,
        'floor_material_id' => $floorMaterial->id,
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

    $response = $this->patchToTenant('api.rooms.update', $formData, $room->reference_code);
    $response->assertStatus(400)
        ->assertJson(['status' => 'error']);

    // $response->assertSessionHasErrors([
    //     'locationType' => 'You cannot change the type of a location',
    // ]);
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

    $response = $this->deleteFromTenant('api.rooms.destroy', $room->reference_code);
    $response->assertStatus(200)
        ->assertJson(['status' => 'success']);
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
    $response->assertStatus(200)->assertJson(['status' => 'success']);
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
    $response->assertStatus(200)->assertJson(['status' => 'success']);
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
    $response->assertStatus(200)->assertJson(['status' => 'success']);

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
    $response->assertStatus(200)->assertJson(['status' => 'success'])
        ->assertJsonCount(2, 'data');
});

it('can retrieve all assets from a room', function () {
    $room = Room::factory()
        ->for(LocationType::where('level', 'room')->first())
        ->for(Floor::first())
        ->create();

    CategoryType::factory()->create(['category' => 'asset']);

    Asset::factory()->forLocation($room)->create();
    Asset::factory()->forLocation($room)->create();

    $response = $this->getFromTenant('api.rooms.assets', $room);
    $response->assertStatus(200)->assertJson([
        'status' => 'success',
    ])
        ->assertJsonCount(2, 'data');
});

it('can change location type of a room and related assets', function () {
    $roomOne = Room::factory()
        ->for(LocationType::where('level', 'room')->first())
        ->for(Floor::first())
        ->create();

    $roomTwo = Room::factory()
        ->for(LocationType::where('level', 'room')->first())
        ->for(Floor::first())
        ->create();

    CategoryType::factory()->create(['category' => 'asset']);
    $assetOne = Asset::factory()->forLocation($roomTwo)->create();
    $assetTwo = Asset::factory()->forLocation($roomOne)->create();
    $assetThree = Asset::factory()->forLocation($roomOne)->create();
    $assetFour = Asset::factory()->forLocation($roomOne)->create();

    $newLocationType = LocationType::factory()->create(['level' => 'room']);
    $newLocationType = LocationType::factory()->create(['level' => 'room']);
    // dump($newLocationType);

    $formData = [
        'locationType' => $newLocationType->id,
        'assets' => [
            [
                'assetId' => $assetOne->id,
                'change' => 'change',
                'locationType' => 'room',
                'locationId' => $roomOne->id,
            ],
            [
                'assetId' => $assetTwo->id,
                'change' => 'change',
                'locationType' => 'room',
                'locationId' => $roomTwo->id,
            ],
            [
                'assetId' => $assetThree->id,
                'change' => 'follow',
                'locationType' => 'room',
                'locationId' => $roomOne->id,
            ],
            [
                'assetId' => $assetFour->id,
                'change' => 'delete',
            ]

        ]
    ];

    $response = $this->patchToTenant('api.rooms.relocate', $formData, $roomOne);
    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
        ]);

    $roomOneChanged = Room::find($roomOne->id);

    assertDatabaseHas(
        'rooms',
        [
            'id' => $roomOne->id,
            'location_type_id' => $newLocationType->id,
            'code' => $newLocationType->prefix . '001',
            'reference_code' => $roomOne->floor->reference_code . '-' . $newLocationType->prefix . '001'
        ]
    );

    assertDatabaseHas(
        'assets',
        [
            'id' => $assetOne->id,
            'location_id' => $roomOneChanged->id,
            'code' => $assetOne->code,
            'reference_code' => $roomOneChanged->reference_code . '-' . $assetOne->code
        ],
    );

    assertDatabaseHas(
        'assets',
        [
            'id' => $assetTwo->id,
            'location_id' => $roomTwo->id,
            'code' => $assetTwo->code,
            'reference_code' => $roomTwo->reference_code . '-' . $assetTwo->code
        ]
    );

    assertDatabaseHas(
        'assets',
        [
            'id' => $assetThree->id,
            'location_id' => $roomOneChanged->id,
            'code' => $assetThree->code,
            'reference_code' => $roomOneChanged->reference_code . '-' . $assetThree->code
        ]
    );
    $assetFour = Asset::withTrashed()->find(4);

    $this->assertNotNull($assetFour->deleted_at);
    assertDatabaseHas(
        'assets',
        [
            'id' => $assetFour->id,
            'location_id' => $roomOneChanged->id,
            'reference_code' => $roomOne->reference_code . '-' . $assetFour->code
        ]
    );
});
