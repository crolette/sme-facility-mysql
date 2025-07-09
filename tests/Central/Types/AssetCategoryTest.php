<?php

use App\Models\User;
use App\Models\SiteType;
use App\Enums\LevelTypes;
use App\Models\Central\AssetCategory;
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

it('renders the index asset categories page', function () {
    $this->actingAs($user = User::factory()->create());

    AssetCategory::factory()->count(3)->create();
    $response = $this->get(route('central.assets.index'));
    $response->assertOk();

    try {
        $response->assertInertia(
            fn($page) =>
            $page->component('central/assets/index')
                ->has('categories', 3)
        );
    } finally {
        $user->delete();
    }
});

it('renders the create asset categories page', function () {
    $this->actingAs($user = User::factory()->create());

    try {
        $response = $this->get(route('central.assets.create'));
        $response->assertOk();
        $response->assertInertia(
            fn($page) =>
            $page->component('central/assets/create')
        );
    } finally {
        $user->delete();
    }
});

it('creates a new asset category in the database', function () {
    $this->actingAs($user = User::factory()->create());

    $formData = [
        'translations' => [
            'en' => 'category_en',
            'fr' => 'category_fr',
            'de' => 'category_de',
            'nl' => 'category_nl',
        ],
    ];

    try {
        $response = $this->post(route('central.assets.store'), $formData);
        $response->assertStatus(302);

        $category = AssetCategory::first();

        assertDatabaseCount('asset_categories', 1);
        assertDatabaseHas('asset_categories', [
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

it('shows the asset category page', function () {
    $this->actingAs($user = User::factory()->create());

    $category = AssetCategory::factory()->create();;

    try {
        $response = $this->get(route('central.assets.show', $category->slug));
        $response->assertOk();
        $response->assertInertia(
            fn($page) =>
            $page->component('central/assets/show')
                ->has('category')
                ->where('category.id', $category->id)
                ->where('category.slug', $category->slug)
                ->has('category.translations', count(config('app.locales')))
        );
    } finally {
        $user->delete();
    }
});

it('renders the update asset category page', function () {
    $this->actingAs($user = User::factory()->create());

    $category = AssetCategory::factory()->create();;

    try {
        $response = $this->get(route('central.assets.edit', $category->slug));
        $response->assertOk();
        $response->assertInertia(
            fn($page) =>
            $page->component('central/assets/create')
                ->has('category')
                ->where('category.id', $category->id)
                ->where('category.slug', $category->slug)
                ->has('category.translations', count(config('app.locales')))
        );
    } finally {
        $user->delete();
    }
});


it('can update an existing asset category', function () {
    $this->actingAs($user = User::factory()->create());

    $category = AssetCategory::factory()->create();

    $oldSlug = $category->slug;
    $oldEnglishTranslation = $category->translations()->where('locale', 'en')->first()->label;

    $formData = [
        'translations' => [
            'en' => 'newcategory_en',
            'fr' => 'newcategory_fr',
            'de' => 'newcategory_de',
            'nl' => 'newcategory_nl',
        ],
    ];

    try {
        $response = $this->patch(route('central.assets.update', $category->slug), $formData);
        $response->assertStatus(302);

        $category = AssetCategory::first();

        assertDatabaseCount('asset_categories', 1);
        assertDatabaseHas('asset_categories', [
            'slug' => 'newcategory-en'
        ]);

        assertDatabaseHas('translations', [
            'translatable_id' => $category->id,
            'locale' => 'en',
            'label' => 'Newcategory_en'
        ]);

        assertDatabaseMissing('asset_categories', [
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

    $category = AssetCategory::factory()->create();

    try {
        $response = $this->delete(route('central.assets.destroy', $category->slug));
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
