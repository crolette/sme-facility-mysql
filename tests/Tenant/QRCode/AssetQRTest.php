<?php

use Carbon\Carbon;
use App\Models\LocationType;
use App\Models\Tenants\Room;
use App\Models\Tenants\Site;
use App\Models\Tenants\User;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Floor;
use App\Models\Tenants\Building;

use App\Models\Tenants\Provider;
use Illuminate\Http\UploadedFile;
use App\Models\Central\CategoryType;
use Illuminate\Support\Facades\Storage;
use function PHPUnit\Framework\assertCount;
use function Pest\Laravel\assertDatabaseHas;
use function PHPUnit\Framework\assertEquals;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseMissing;
use function PHPUnit\Framework\assertSame;

beforeEach(function () {
    $this->user = User::factory()->withRole('Admin')->create();
    $this->actingAs($this->user, 'tenant');
    $this->manager = User::factory()->withRole('Maintenance Manager')->create();

    LocationType::factory()->create(['level' => 'site']);
    LocationType::factory()->create(['level' => 'building']);
    LocationType::factory()->create(['level' => 'floor']);
    LocationType::factory()->create(['level' => 'room']);
    CategoryType::factory()->count(2)->create(['category' => 'document']);
    $this->categoryType = CategoryType::factory()->create(['category' => 'asset']);
    CategoryType::factory()->count(2)->create(['category' => 'asset']);
    $this->site = Site::factory()->create();
    $this->building = Building::factory()->create();
    $this->floor = Floor::factory()->create();

    $this->room = Room::factory()
        ->for(LocationType::where('level', 'room')->first())
        ->for(Floor::first())
        ->create();

    $this->formData = [
        'name' => 'New asset',
        'description' => 'Description new asset',
        'locationId' => $this->site->id,
        'locationType' => 'site',
        'locationReference' => $this->site->reference_code,
        'categoryId' => $this->categoryType->id,
        
    ];
});


it('creates a QR Code when need_qr_code is true', function() {

    $formData = [...$this->formData, 'need_qr_code' => true];

    $response = $this->postToTenant('api.assets.store', $formData);
    $response->assertSessionHasNoErrors();

    $asset = Asset::first();

    $qr_hash = generateQRCodeHash('assets', $asset);

    $fileName = 'qr_'  . $qr_hash . '_' . Carbon::now()->isoFormat('YYYYMMDDHHMM')  . '.png';
     $qrPath = tenancy()->tenant->id . "/assets/" . $asset->id . "/qrcode/" . $fileName;

    assertDatabaseHas('assets', 
    [
        'id' => $asset->id,
        'qr_hash' => $qr_hash,
        'qr_code' => $qrPath
    ]);
    
    Storage::disk('tenants')->assertExists($asset->qrPath);

});


it('can regenerate a QR Code', function() {
    $asset = Asset::factory()->forLocation($this->room)->create();

    $response = $this->postToTenant('api.assets.qr.regen', [],$asset->reference_code);
    $response->assertSessionHasNoErrors();


    $qr_hash = generateQRCodeHash('assets', $asset);

    $fileName = 'qr_'  . $qr_hash . '_' . Carbon::now()->isoFormat('YYYYMMDDHHMM')  . '.png';
    $qrPath = tenancy()->tenant->id . "/assets/" . $asset->id . "/qrcode/" . $fileName;

    assertDatabaseHas(
        'assets',
        [
            'id' => $asset->id,
            'qr_hash' => $qr_hash,
            'qr_code' => $qrPath
        ]
    );

    Storage::disk('tenants')->assertExists($asset->qrPath);
});

