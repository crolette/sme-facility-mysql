<?php

use App\Models\Central\CentralUser;
use App\Models\SiteType;
use App\Enums\LevelTypes;
use App\Models\LocationType;
use Database\Factories\SiteTypeFactory;

use function Pest\Laravel\assertDatabaseHas;
use function PHPUnit\Framework\assertEquals;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseMissing;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

beforeEach(function () {
    $this->withoutMiddleware([
        \Stancl\Tenancy\Middleware\InitializeTenancyByDomain::class,
        \Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain::class,
        \Stancl\Tenancy\Middleware\InitializeTenancyByPath::class,
        \Stancl\Tenancy\Middleware\InitializeTenancyByRequestData::class,
    ]);
});


it('renders the index location pages', function () {
    $this->actingAs($user = CentralUser::factory()->create());
    LocationType::factory()->count(1)->create(['level' => 'site']);
    LocationType::factory()->count(2)->create(['level' => 'building']);
    LocationType::factory()->count(3)->create(['level' => 'floor']);
    LocationType::factory()->count(4)->create(['level' => 'room']);
    $response = $this->get(route('central.locations.index'));
    $response->assertOk();

    try {
        $response->assertInertia(
            fn($page) =>
            $page->component('central/types/index')
                ->has('types.site', 1)
                ->has('types.building', 2)
                ->has('types.floor', 3)
                ->has('types.room', 4)
        );
    } finally {
        $user->delete();
    }
});

it('renders the create location type page', function () {
    $this->actingAs($user = CentralUser::factory()->create());
    $types = array_map(fn($case) => "{$case->value}", LevelTypes::cases());

    try {
        $response = $this->get(route('central.locations.create'));
        $response->assertOk();
        $response->assertInertia(
            fn($page) =>
            $page->component('central/types/create')
                ->has('types', count($types))
        );
    } finally {
        $user->delete();
    }
});

it('creates a new location type in the database', function () {
    $this->actingAs($user = User::factory()->create());
    // dump($type);

    $formData = [
        'prefix' => 's',
        'level' => 'site',
        'translations' => [
            'en' => 'site',
            'fr' => 'site',
            'de' => 'site',
            'nl' => 'site',
        ],
    ];

    try {
        $response = $this->post(route('central.locations.store'), $formData);
        $response->assertStatus(302);

        $locationType = LocationType::where('prefix', 'S')->first();

        assertDatabaseCount('location_types', 1);
        assertDatabaseHas('location_types', [
            'prefix' => 'S',
            'slug' => 'site'
        ]);


        assertDatabaseHas('translations', [
            'translatable_id' => $locationType->id,
            'locale' => 'en',
            'label' => 'Site'

        ]);
        assertDatabaseHas('translations', [
            'translatable_id' => $locationType->id,
            'locale' => 'fr',
            'label' => 'Site'

        ]);
    } finally {
        $user->delete();
    }
});

it('show the location type page', function () {

    $this->actingAs($user = CentralUser::factory()->create());
    $type = LocationType::factory()->create();

    try {
        $response = $this->get(route('central.locations.show', $type->slug));
        $response->assertOk();
        $response->assertInertia(
            fn($page) =>
            $page->component('central/types/show')
                ->has('type')
                ->has('type.translations', 4)
                ->where('type.id', $type->id)
                ->where('type.slug', $type->slug)
                ->where('type.prefix', $type->prefix)
                ->where('type.level', $type->level)
        );
    } finally {
        $user->delete();
    }
});

it('renders the location type edit page', function () {

    $this->actingAs($user = CentralUser::factory()->create());
    $type = LocationType::factory()->create();

    try {
        $response = $this->get(route('central.locations.edit', $type->slug));
        $response->assertOk();
        $response->assertInertia(
            fn($page) =>
            $page->component('central/types/create')
                ->has('type')
                ->has('type.translations', 4)
                ->where('type.id', $type->id)
                ->where('type.slug', $type->slug)
                ->where('type.prefix', $type->prefix)
                ->where('type.level', $type->level)
        );
    } finally {
        $user->delete();
    }
});

it('updates the location translations', function () {

    $this->actingAs($user = CentralUser::factory()->create());
    $locationType = LocationType::factory()->create();

    $formData = [
        'prefix' => $locationType->prefix,
        'level' => $locationType->level,
        'translations' => [
            'en' => 'Building',
            'fr' => 'bâtiment',
            'de' => 'Gebäude',
            'nl' => 'Gebouw',
        ],
    ];

    try {
        $response = $this->patch(route('central.locations.update', $locationType->slug), $formData);
        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();
        assertDatabaseCount('location_types', 1);

        $locationTypeDB = LocationType::where('prefix', $locationType->prefix)->first();

        assertDatabaseHas('translations', [
            'translatable_id' => $locationTypeDB->id,
            'locale' => 'en',
            'label' => 'Building'
        ]);

        assertDatabaseHas('location_types', [
            'prefix' => $locationTypeDB->prefix,
            'slug' => 'building',
            'level' => $locationTypeDB->level,
        ]);
    } finally {
        // $user->delete();
    }
});

it('cannot update an existing location prefix', function () {

    $this->actingAs($user = CentralUser::factory()->create());
    $type = LocationType::factory()->create();

    $prefix = $type->prefix;
    $level = $type->level;

    $formData = [
        'prefix' => 'b',
        'level' => $level,
        'translations' => [
            'en' => 'Building',
            'fr' => 'bâtiment',
            'de' => 'Gebäude',
            'nl' => 'Gebouw',
        ],
    ];
    try {
        $response = $this->patch(route('central.locations.update', $type->slug), $formData);
        // dump($response);
        $response->assertRedirect();
        $response->assertSessionHasErrors([
            'prefix' => 'You cannot change the prefix of a location',
        ]);
        $type->refresh();
        $this->assertEquals($prefix, $type->prefix);
    } finally {
        $user->delete();
    }
});

it('deletes a location type and translations', function () {
    $this->actingAs($user = CentralUser::factory()->create());

    $type = LocationType::factory()->create();

    try {
        $response = $this->delete(route('central.locations.destroy', $type->slug));
        $response->assertStatus(302);
        assertDatabaseMissing('location_types', [
            'prefix' => $type->prefix
        ]);

        assertDatabaseMissing('translations', [
            'translatable_id' => $type->id
        ]);
    } finally {
        $user->delete();
    }
});
