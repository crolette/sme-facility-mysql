<?php

use Carbon\Carbon;
use App\Models\LocationType;
use App\Models\Tenants\Room;
use App\Models\Tenants\Site;
use App\Models\Tenants\User;
use App\Imports\AssetsImport;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Floor;
use App\Enums\NoticePeriodEnum;
use App\Models\Tenants\Building;

use App\Models\Tenants\Contract;
use App\Models\Tenants\Document;
use App\Models\Tenants\Provider;
use App\Enums\ContractStatusEnum;

use Illuminate\Http\UploadedFile;
use App\Enums\ContractDurationEnum;

use App\Models\Central\CategoryType;
use Maatwebsite\Excel\Facades\Excel;
use App\Enums\ContractRenewalTypesEnum;

use Illuminate\Support\Facades\Storage;
use function PHPUnit\Framework\assertCount;
use function Pest\Laravel\assertDatabaseHas;
use function PHPUnit\Framework\assertEquals;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseMissing;
use function PHPUnit\Framework\assertNotEmpty;
use function PHPUnit\Framework\assertNull;
use function PHPUnit\Framework\assertSame;

beforeEach(function () {
    $this->user = User::factory()->withRole('Admin')->create();
    $this->actingAs($this->user, 'tenant');

    $this->siteType = LocationType::factory()->create(['level' => 'site']);
    $this->buildingType = LocationType::factory()->create(['level' => 'building']);
    $this->floorType = LocationType::factory()->create(['level' => 'floor']);
    $this->roomType = LocationType::factory()->create(['level' => 'room']);
    CategoryType::factory()->count(2)->create(['category' => 'provider']);
    $this->securityCat = CategoryType::factory()->create(['category' => 'asset', 'slug' => 'Security']);
    $this->furnitureCat = CategoryType::factory()->create(['category' => 'asset', 'slug' => 'Furniture']);
    $this->site = Site::factory()->create();
    $this->building = Building::factory()->create();
    $this->floor = Floor::factory()->create();
    $this->provider = Provider::factory()->create();

    $this->room = Room::factory()
        ->for(LocationType::where('level', 'room')->first())
        ->for(Floor::first())
        ->create();

});

it('can import and create new assets', function() {

    Storage::fake('local');

    $file = UploadedFile::fake()->createWithContent('assets.xlsx', file_get_contents(base_path('tests/fixtures/assets.xlsx')));

    Excel::import(new AssetsImport, $file);

    assertDatabaseCount('assets', 2);

    assertDatabaseHas('assets', 
        [
            'code' => 'A0001',
            'brand' => 'Dell',
            'model' => 'Inspiron',
            'serial_number' => 'X36-AD-65',
            'category_type_id' => $this->furnitureCat->id,
            'depreciable' => 0,
            'depreciation_start_date' => null,
            'depreciation_end_date' => null,
            'depreciation_duration' => null,
        ],
    );

    $asset = Asset::first();

    assertNotEmpty($asset->qr_code);

    assertDatabaseHas(
        'assets',
        [
            'code' => 'A0002',
            'brand' => 'Ferrari',
            'model' => 'F40',
            'serial_number' => 'VROUMVROUM',
            'depreciable' => 1,
            'category_type_id' => $this->securityCat->id,
            'depreciation_start_date' => '2025-01-01',
            'depreciation_end_date' => '2029-01-01',
            'depreciation_duration' => 4,
            'surface' => 25
        ],
    );

    $secondAsset = Asset::find(2);
    assertNull($secondAsset->qr_code);

});