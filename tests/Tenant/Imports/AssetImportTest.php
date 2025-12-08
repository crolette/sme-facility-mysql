<?php

use Carbon\Carbon;
use App\Models\LocationType;
use App\Models\Tenants\Room;
use App\Models\Tenants\Site;
use App\Models\Tenants\User;
use App\Imports\AssetsImport;
use App\Jobs\ImportExcelAssetsJob;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Floor;
use App\Models\Tenants\Building;
use App\Models\Tenants\Provider;
use App\Jobs\ImportExcelUsersJob;
use Illuminate\Http\UploadedFile;
use App\Models\Central\CategoryType;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use function PHPUnit\Framework\assertNull;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseCount;
use function PHPUnit\Framework\assertNotEmpty;

beforeEach(function () {
    $this->user = User::factory()->withRole('Admin')->create();
    $this->manager = User::factory()->withRole('Maintenance Manager')->create(['email' => 'crolweb@gmail.com']);
    $this->actingAs($this->user, 'tenant');

    CategoryType::factory()->count(2)->create(['category' => 'provider']);
    $this->securityCat = CategoryType::factory()->create(['category' => 'asset', 'slug' => 'Security']);
    $this->furnitureCat = CategoryType::factory()->create(['category' => 'asset', 'slug' => 'Furniture']);
    $this->site = Site::factory()->withMaintainableData()->create();
    $this->building = Building::factory()->create();
    $this->floor = Floor::factory()->withMaintainableData()->create();
    $this->room = Room::factory()->withMaintainableData()->create();
    $this->provider = Provider::factory()->create();
});

it('can upload assets and dispatch import assets job', function () {

    Storage::fake('local');
    Queue::fake();

    $file = UploadedFile::fake()->createWithContent('assets.xlsx', file_get_contents(base_path('tests/fixtures/assets.xlsx')));

    $formData = ['file' => $file];

    $response = $this->postToTenant('api.tenant.import', $formData, [], [
        'Content-Type' => 'multipart/form-data'
    ]);
    Queue::assertPushed(ImportExcelAssetsJob::class, function ($job) {
        return $job->user->id === Auth::id();
    });
});

it('can import and create new assets', function () {

    Storage::fake('local');

    $file = UploadedFile::fake()->createWithContent('assets.xlsx', file_get_contents(base_path('tests/fixtures/assets.xlsx')));

    Excel::import(new AssetsImport, $file);

    assertDatabaseCount('assets', 5);

    $asset = Asset::first();
    assertDatabaseHas(
        'assets',
        [
            'code' => 'A0001',
            'brand' => 'Dell',
            'model' => 'Inspiron',
            'serial_number' => 'X36-AD-65',
            'is_mobile' => 1,
            'location_type' => User::class,
            'category_type_id' => $this->securityCat->id,
            'depreciable' => 0,
            'depreciation_start_date' => null,
            'depreciation_end_date' => null,
            'depreciation_duration' => null,
        ],
    );
    assertNotEmpty($asset->qr_code);

    assertDatabaseHas(
        'maintainables',
        [
            'maintainable_type' => get_class($asset),
            'maintainable_id' => $asset->id,
            'name' => 'PC Gaming',
            'description' => 'PC Gaming Crolweb',
            'purchase_date' => null,
            'purchase_cost' => 2500.00,
            'under_warranty' => 1,
            'end_warranty_date' => '2026-08-08',
            'need_maintenance' => 0
        ]
    );

    $secondAsset = Asset::find(2);
    assertDatabaseHas(
        'assets',
        [
            'code' => 'A0002',
            'brand' => 'Ferrari',
            'model' => 'F40',
            'serial_number' => 'VROUMVROUM',
            'depreciable' => 1,
            'location_type' => Site::class,
            'location_id' => $this->site->id,
            'category_type_id' => $this->furnitureCat->id,
            'depreciation_start_date' => '2025-01-01',
            'depreciation_end_date' => '2029-01-01',
            'depreciation_duration' => 4,
            'surface' => 25
        ],
    );

    assertDatabaseHas(
        'maintainables',
        [
            'maintainable_type' => get_class($secondAsset),
            'maintainable_id' => $secondAsset->id,
            'name' => 'Bureau gaming',
            'description' => 'Le bureau du boss',
            'purchase_cost' => 1234.00,
            'purchase_date' => '2025-01-01',
            'under_warranty' => 1,
            'end_warranty_date' => '2027-01-01',
            'need_maintenance' => 1,
            'maintenance_frequency' => 'annual',
            'next_maintenance_date' => '2026-09-02',
            'last_maintenance_date' => '2025-09-02'
        ]
    );

    assertNull($secondAsset->qr_code);

    assertDatabaseHas(
        'assets',
        [
            'code' => 'A0003',
            'location_type' => Building::class,
            'location_id' => $this->building->id,
        ],
    );

    assertDatabaseHas(
        'assets',
        [
            'code' => 'A0004',
            'location_type' => Floor::class,
            'location_id' => $this->floor->id,
        ],
    );

    assertDatabaseHas(
        'assets',
        [
            'code' => 'A0005',
            'location_type' => Room::class,
            'location_id' => $this->room->id,
        ],
    );
});

it('can import and create new asset with maintenance', function () {

    Storage::fake('local');

    $file = UploadedFile::fake()->createWithContent('assets.xlsx', file_get_contents(base_path('tests/fixtures/assets_maintenance.xlsx')));

    Excel::import(new AssetsImport, $file);

    assertDatabaseCount('assets', 4);

    assertDatabaseHas(
        'maintainables',
        [
            'need_maintenance' => 1,
            'maintenance_frequency' => 'annual',
            'next_maintenance_date' => Carbon::now()->addDays(365)->toDateString(),
            'last_maintenance_date' => null
        ]
    );

    assertDatabaseHas(
        'maintainables',
        [
            'need_maintenance' => 1,
            'maintenance_frequency' => 'monthly',
            'next_maintenance_date' => Carbon::now()->addDays(30)->toDateString(),
            'last_maintenance_date' => null
        ]
    );

    assertDatabaseHas(
        'maintainables',
        [
            'need_maintenance' => 1,
            'maintenance_frequency' => 'biannual',
            'next_maintenance_date' => Carbon::instance(new DateTime('2025-10-06'))->addDays(180)->toDateString(),
            'last_maintenance_date' => '2025-10-06'
        ]
    );

    assertDatabaseHas(
        'maintainables',
        [
            'maintainable_type' => Asset::class,
            'maintainable_id' => 4,
            'need_maintenance' => 1,
            'maintenance_frequency' => 'biennial',
            'next_maintenance_date' => '2026-12-24',
            'last_maintenance_date' => '2024-12-24'
        ]
    );
});

it('can import and create new asset with depreciable', function () {

    Storage::fake('local');

    $file = UploadedFile::fake()->createWithContent('assets.xlsx', file_get_contents(base_path('tests/fixtures/assets_depreciable.xlsx')));

    Excel::import(new AssetsImport, $file);

    assertDatabaseCount('assets', 2);

    assertDatabaseHas(
        'assets',
        [
            'id' => 1,
            'depreciable' => 0,
        ]
    );

    assertDatabaseHas(
        'assets',
        [
            'id' => 2,
            'depreciable' => 1,
            'depreciation_start_date' => '2025-01-01',
            'depreciation_end_date' => '2029-01-01',
            'depreciation_duration' => 4
        ]
    );
});

it('fails when the name of the file does not contain users', function () {

    Storage::fake('local');

    $file = UploadedFile::fake()->createWithContent('users.xlsx', file_get_contents(base_path('tests/fixtures/users.xlsx')));

    $formData = ['file' => $file];

    $response = $this->postToTenant('api.tenant.import', $formData, [], [
        'Content-Type' => 'multipart/form-data'
    ]);
    $response->assertJson(['status' => 'error', 'message' => 'Wrong file.']);
});

it('can update location of assets', function () {

    Asset::factory()->withMaintainableData()->forLocation($this->site)->create();
    Asset::factory()->withMaintainableData()->forLocation($this->building)->create();
    Asset::factory()->withMaintainableData()->forLocation($this->floor)->create();
    Asset::factory()->withMaintainableData()->forLocation($this->room)->create();
    Asset::factory()->withMaintainableData()->forLocation($this->manager)->create(['is_mobile' => true]);

    Storage::fake('local');

    $file = UploadedFile::fake()->createWithContent('assets.xlsx', file_get_contents(base_path('tests/fixtures/assets_update.xlsx')));

    Excel::import(new AssetsImport, $file);

    assertDatabaseHas(
        'assets',
        [
            'code' => 'A0001',
            'reference_code' => 'S01-B01-L01-R001-A0001',
            'is_mobile' => 0,
            'location_type' => get_class($this->room),
        ],
    );

    assertDatabaseHas(
        'assets',
        [
            'code' => 'A0002',
            'reference_code' => 'S01-A0002',
            'is_mobile' => 0,
            'location_type' => get_class($this->site),
        ],
    );

    assertDatabaseHas(
        'assets',
        [
            'code' => 'A0003',
            'reference_code' => 'S01-B01-A0003',
            'is_mobile' => 0,
            'location_type' => get_class($this->building),
        ],
    );

    assertDatabaseHas(
        'assets',
        [
            'code' => 'A0004',
            'reference_code' => 'S01-B01-L01-A0004',
            'is_mobile' => 0,
            'location_type' => get_class($this->floor),
        ],
    );

    assertDatabaseHas(
        'assets',
        [
            'code' => 'A0005',
            'reference_code' => 'A0005',
            'is_mobile' => 1,
            'location_type' => get_class($this->manager),
        ],
    );
});

it('can update maintainable & information of assets', function () {

    Asset::factory()->withMaintainableData()->forLocation($this->site)->create();
    Asset::factory()->withMaintainableData()->forLocation($this->building)->create();
    Asset::factory()->withMaintainableData()->forLocation($this->floor)->create();
    Asset::factory()->withMaintainableData()->forLocation($this->room)->create();
    Asset::factory()->withMaintainableData()->forLocation($this->manager)->create(['is_mobile' => true]);

    Storage::fake('local');

    $file = UploadedFile::fake()->createWithContent('assets.xlsx', file_get_contents(base_path('tests/fixtures/assets_update.xlsx')));

    Excel::import(new AssetsImport, $file);

    $asset = Asset::find(1);
    assertDatabaseHas(
        'assets',
        [
            'code' => 'A0001',
            'reference_code' => 'S01-B01-L01-R001-A0001',

            'brand' => 'Dell',
            'model' => 'Inspiron',
            'serial_number' => 'X36-AD-65',
            'is_mobile' => 0,
            'location_type' => get_class($this->room),
            'category_type_id' => $this->securityCat->id,
            'depreciable' => 0,
            'depreciation_start_date' => null,
            'depreciation_end_date' => null,
            'depreciation_duration' => null,
        ],
    );

    assertDatabaseHas(
        'maintainables',
        [
            'maintainable_type' => get_class($asset),
            'maintainable_id' => $asset->id,
            'name' => 'PC Gaming',
            'description' => 'PC Gaming Crolweb',
            'purchase_date' => null,
            'purchase_cost' => 2500.00,
            'under_warranty' => 1,
            'end_warranty_date' => '2026-08-08',
            'need_maintenance' => 0
        ]
    );

    $asset = Asset::find(2);
    assertDatabaseHas(
        'assets',
        [
            'code' => 'A0002',
            'reference_code' => 'S01-A0002',
            'brand' => 'Ferrari',
            'model' => 'F40',
            'serial_number' => 'VROUMVROUM',
            'is_mobile' => 0,
            'location_type' => get_class($this->site),
            'category_type_id' => $this->furnitureCat->id,
            'depreciable' => 1,
            'surface' => '25.0',
            'depreciation_start_date' => '2025-01-01',
            'depreciation_end_date' => '2029-01-01',
            'depreciation_duration' => 4,
        ],
    );

    assertDatabaseHas(
        'maintainables',
        [
            'maintainable_type' => get_class($asset),
            'maintainable_id' => $asset->id,
            'name' => 'Bureau Gaming',
            'description' => 'Le bureau du boss',
            'purchase_date' => '2025-01-01',
            'purchase_cost' => '1234.00',
            'under_warranty' => 1,
            'end_warranty_date' => '2027-01-01',
            'need_maintenance' => 1,
            'maintenance_frequency' => 'annual',
            'next_maintenance_date' => '2026-09-02',
            'last_maintenance_date' => '2025-09-02',
        ]
    );

    $asset = Asset::find(3);
    assertDatabaseHas(
        'assets',
        [
            'code' => 'A0003',
            'reference_code' => 'S01-B01-A0003',
            'brand' => 'Ferrari',
            'model' => 'F40',
            'serial_number' => 'VROUMVROUM',
            'is_mobile' => 0,
            'location_type' => get_class($this->building),
            'category_type_id' => $this->furnitureCat->id,
            'depreciable' => 1,
            'surface' => '25.0',
            'depreciation_start_date' => '2025-01-01',
            'depreciation_end_date' => '2029-01-01',
            'depreciation_duration' => 4,
        ],
    );

    assertDatabaseHas(
        'maintainables',
        [
            'maintainable_type' => get_class($asset),
            'maintainable_id' => $asset->id,
            'name' => 'Bureau Gaming',
            'description' => 'Le bureau du boss',
            'purchase_date' => '2025-01-01',
            'purchase_cost' => '1234.00',
            'under_warranty' => 1,
            'end_warranty_date' => '2027-01-01',
            'need_maintenance' => 1,
            'maintenance_frequency' => 'annual',
            'next_maintenance_date' => '2026-09-02',
            'last_maintenance_date' => '2025-09-02',
        ]
    );

    $asset = Asset::find(4);
    assertDatabaseHas(
        'assets',
        [
            'code' => 'A0004',
            'reference_code' => 'S01-B01-L01-A0004',
            'brand' => 'Ferrari',
            'model' => 'F40',
            'serial_number' => 'VROUMVROUM',
            'is_mobile' => 0,
            'location_type' => get_class($this->floor),
            'category_type_id' => $this->furnitureCat->id,
            'depreciable' => 1,
            'surface' => '25.0',
            'depreciation_start_date' => '2025-01-01',
            'depreciation_end_date' => '2029-01-01',
            'depreciation_duration' => 4,
        ],
    );

    assertDatabaseHas(
        'maintainables',
        [
            'maintainable_type' => get_class($asset),
            'maintainable_id' => $asset->id,
            'name' => 'Bureau Gaming',
            'description' => 'Le bureau du boss',
            'purchase_date' => '2025-01-01',
            'purchase_cost' => '1234.00',
            'under_warranty' => 1,
            'end_warranty_date' => '2027-01-01',
            'need_maintenance' => 1,
            'maintenance_frequency' => 'annual',
            'next_maintenance_date' => '2026-09-02',
            'last_maintenance_date' => '2025-09-02',
        ]
    );

    $asset = Asset::find(5);
    assertDatabaseHas(
        'assets',
        [
            'code' => 'A0005',
            'reference_code' => 'A0005',
            'brand' => 'Ferrari',
            'model' => 'F40',
            'serial_number' => 'VROUMVROUM',
            'is_mobile' => 1,
            'location_type' => get_class($this->manager),
            'category_type_id' => $this->furnitureCat->id,
            'depreciable' => 1,
            'surface' => '25.0',
            'depreciation_start_date' => '2025-01-01',
            'depreciation_end_date' => '2029-01-01',
            'depreciation_duration' => 4,
        ],
    );
    $this->assertNotNull($asset->qr_code);

    assertDatabaseHas(
        'maintainables',
        [
            'maintainable_type' => get_class($asset),
            'maintainable_id' => $asset->id,
            'name' => 'Bureau Gaming',
            'description' => 'Le bureau du boss',
            'purchase_date' => '2025-01-01',
            'purchase_cost' => '1234.00',
            'under_warranty' => 1,
            'end_warranty_date' => '2027-01-01',
            'need_maintenance' => 1,
            'maintenance_frequency' => 'annual',
            'next_maintenance_date' => '2026-09-02',
            'last_maintenance_date' => '2025-09-02',
        ]
    );
});
