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
use App\Models\Tenants\Maintainable;
use Maatwebsite\Excel\Facades\Excel;

use App\Enums\ContractRenewalTypesEnum;
use Illuminate\Support\Facades\Storage;
use function PHPUnit\Framework\assertNull;
use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertCount;
use function Pest\Laravel\assertDatabaseHas;
use function PHPUnit\Framework\assertEquals;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function PHPUnit\Framework\assertNotEmpty;
use function Pest\Laravel\assertDatabaseMissing;

beforeEach(function () {
    $this->user = User::factory()->withRole('Admin')->create();
    User::factory()->withRole('Maintenance Manager')->create(['email' => 'crolweb@gmail.com']);
    $this->actingAs($this->user, 'tenant');

    $this->siteType = LocationType::factory()->create(['level' => 'site', 'prefix' => 'S']);
    $this->buildingType = LocationType::factory()->create(['level' => 'building', 'prefix' => 'B']);
    $this->floorType = LocationType::factory()->create(['level' => 'floor', 'prefix' => 'L']);
    $this->roomType = LocationType::factory()->create(['level' => 'room', 'prefix' => 'R']);
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

