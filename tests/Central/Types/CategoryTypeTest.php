<?php

use App\Models\User;
use App\Enums\CategoryTypes;
use App\Models\Central\CategoryType;
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

it('renders the index document types page', function () {
    $this->actingAs($user = User::factory()->create());

    CategoryType::factory()->count(3)->create();
    $response = $this->get(route('central.types.index'));
    $response->assertOk();

    try {
        $response->assertInertia(
            fn($page) =>
            $page->component('central/types/index')
                ->has('types', 3)
        );
    } finally {
        $user->delete();
    }
});

it('renders the create document type page', function () {
    $this->actingAs($user = User::factory()->create());

    $categories = array_map(fn($case) => "{$case->value}", CategoryTypes::cases());

    try {
        $response = $this->get(route('central.types.create'));
        $response->assertOk();
        $response->assertInertia(
            fn($page) =>
            $page->component('central/types/create')
                ->has('categories', count($categories))
        );
    } finally {
        $user->delete();
    }
});

it('creates a new category type in the database', function () {
    $this->actingAs($user = User::factory()->create());

    $formData = [
        'category' => 'document',
        'translations' => [
            'en' => 'category_en',
            'fr' => 'category_fr',
            'de' => 'category_de',
            'nl' => 'category_nl',
        ],
    ];

    try {
        $response = $this->post(route('central.types.store'), $formData);
        $response->assertStatus(302);

        $category = CategoryType::first();

        assertDatabaseCount('category_types', 1);
        assertDatabaseHas('category_types', [
            'slug' => 'category-en'
        ]);


        assertDatabaseHas('translations', [
            'translatable_id' => $category->id,
            'locale' => 'en',
            'label' => 'Category_en'

        ]);
    } finally {
        $user->delete();
    };
});

it('fails to create a new category type with a non existing type', function () {
    $this->actingAs($user = User::factory()->create());

    $formData = [
        'category' => 'schtroumpf',
        'translations' => [
            'en' => 'category_en',
            'fr' => 'category_fr',
            'de' => 'category_de',
            'nl' => 'category_nl',
        ],
    ];

    try {
        $response = $this->post(route('central.types.store'), $formData);
        $response->assertSessionHasErrors(
            ['category' => "The selected category is invalid."]
        );
    } finally {
        $user->delete();
    };
});

it('shows the category type page', function () {
    $this->actingAs($user = User::factory()->create());

    $category = CategoryType::factory()->create();;

    try {
        $response = $this->get(route('central.types.show', $category->slug));
        $response->assertOk();
        $response->assertInertia(
            fn($page) =>
            $page->component('central/types/show')
                ->has('type')
                ->where('type.id', $category->id)
                ->where('type.slug', $category->slug)
                ->has('type.translations', count(config('app.locales')))
        );
    } finally {
        $user->delete();
    }
});

it('renders the update asset category page', function () {
    $this->actingAs($user = User::factory()->create());

    $category = CategoryType::factory()->create();
    $categories = array_map(fn($case) => "{$case->value}", CategoryTypes::cases());


    try {
        $response = $this->get(route('central.types.edit', $category->slug));
        $response->assertOk();
        $response->assertInertia(
            fn($page) =>
            $page->component('central/types/create')
                ->has('type')
                ->has('categories', count($categories))
                ->where('type.id', $category->id)
                ->where('type.slug', $category->slug)
                ->has('type.translations', count(config('app.locales')))
        );
    } finally {
        $user->delete();
    }
});


it('can update an existing category type', function () {
    $this->actingAs($user = User::factory()->create());

    $category = CategoryType::factory()->create(['category' => 'document']);

    assertDatabaseHas('category_types', [
        'category' => 'document',
        'slug' => $category->slug
    ]);

    $oldSlug = $category->slug;
    $oldEnglishTranslation = $category->translations()->where('locale', 'en')->first()->label;

    $formData = [
        'category' => 'asset',
        'translations' => [
            'en' => 'newcategory_en',
            'fr' => 'newcategory_fr',
            'de' => 'newcategory_de',
            'nl' => 'newcategory_nl',
        ],
    ];

    try {
        $response = $this->patch(route('central.types.update', $category->slug), $formData);
        $response->assertStatus(302);

        $category = CategoryType::first();

        assertDatabaseCount('category_types', 1);
        assertDatabaseHas('category_types', [
            'category' => 'asset',
            'slug' => 'newcategory-en'
        ]);

        assertDatabaseHas('translations', [
            'translatable_id' => $category->id,
            'locale' => 'en',
            'label' => 'Newcategory_en'
        ]);

        assertDatabaseMissing('category_types', [
            'slug' => $oldSlug
        ]);

        assertDatabaseMissing('translations', [
            'translatable_id' => $category->id,
            'locale' => 'en',
            'label' => $oldEnglishTranslation
        ]);
    } finally {
        $user->delete();
    };
});



it('deletes a location type and translations', function () {
    $this->actingAs($user = User::factory()->create());

    $category = CategoryType::factory()->create();

    try {
        $response = $this->delete(route('central.types.destroy', $category->slug));
        $response->assertStatus(302);
        assertDatabaseMissing('asset_categories', [
            'slug' => $category->slug
        ]);

        assertDatabaseMissing('translations', [
            'translatable_id' => $category->id
        ]);
    } finally {
        $user->delete();
    }
});
