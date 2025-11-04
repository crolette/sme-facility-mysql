<?php

use Carbon\Carbon;
use App\Models\LocationType;
use App\Models\Tenants\Room;
use App\Models\Tenants\Site;
use App\Models\Tenants\User;
use App\Imports\AssetsImport;
use App\Imports\ProvidersImport;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Floor;
use App\Models\Tenants\Building;
use App\Models\Tenants\Provider;
use Illuminate\Http\UploadedFile;
use App\Models\Central\CategoryType;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use function PHPUnit\Framework\assertNull;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseCount;
use function PHPUnit\Framework\assertNotEmpty;

beforeEach(function () {
    $this->user = User::factory()->withRole('Admin')->create();
    User::factory()->withRole('Maintenance Manager')->create(['email' => 'crolweb@gmail.com']);
    $this->actingAs($this->user, 'tenant');

    CategoryType::factory()->create([
        'category' => 'provider',
        'slug' => 'provider-horeca'
    ]);
    CategoryType::factory()->create([
        'category' => 'provider',
        'slug' => 'provider-security'
    ]);

    $this->provider = Provider::factory()->create([
        'name' => 'Company A',
        'email' => 'companya@companya.com',
        'website' => 'https://www.companya.com',
        'vat_number' => 'BE0123456789',
        'phone_number' => '+32123456789',
        'street' => 'Rue du Test',
        'house_number' => '69',
        'postal_code' => '1234',
        'city' => 'Test',
        'country_id' => 17
    ]);
});

it('can import and create new providers', function () {

    Storage::fake('local');

    $file = UploadedFile::fake()->createWithContent('providers.xlsx', file_get_contents(base_path('tests/fixtures/providers.xlsx')));

    Excel::import(new ProvidersImport, $file);

    assertDatabaseCount('providers', 3);

    assertDatabaseHas(
        'providers',
        [
            'name' => 'Test complet',
            'email' => 'testcomplet@testcomplet.com',
            'website' => 'https://www.testcomplet.com',
            'vat_number' => 'BE9876543210',
            'phone_number' => '+32987654321',
            'street' => 'Rue du test complet',
            'house_number' => '69',
            'postal_code' => '1234',
            'city' => 'Test',
            'country_id' => 17,
            'category_id' => 1
        ],
    );

    assertDatabaseHas(
        'providers',
        [
            'name' => 'Test sans maison',
            'email' => 'testsansmaison@testsansmaison.com',
            'website' => 'https://www.testsansmaison.com',
            'vat_number' => 'BE6549873210',
            'phone_number' => '+32987654321',
            'street' => 'Rue du test complet',
            'house_number' => null,
            'postal_code' => '1234',
            'city' => 'Test',
            'country_id' => 17,
            'category_id' => 1
        ],
    );

    assertDatabaseHas(
        'providers',
        [
            'name' => 'Test sans vat',
            'email' => 'testsansvat@testsansvat.com',
            'website' => 'https://www.testsansvat.com',
            'vat_number' => 'BE9876543210',
            'phone_number' => '+32321654987',
            'street' => 'Rue du test sans vat',
            'house_number' => '69',
            'postal_code' => '1234',
            'city' => 'Test',
            'country_id' => 65,
            'category_id' => 2
        ],
    );
});

// it('can import and update providers', function() {

// });
