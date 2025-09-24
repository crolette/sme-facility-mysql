<?php

use Carbon\Carbon;
use App\Models\LocationType;
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
use function PHPUnit\Framework\assertCount;
use function Pest\Laravel\assertDatabaseHas;
use function PHPUnit\Framework\assertEquals;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseMissing;


beforeEach(function () {
    $this->user = User::factory()->withRole('Admin')->create();
    $this->actingAs($this->user, 'tenant');
    LocationType::factory()->create(['level' => 'site']);
    $this->locationType = LocationType::factory()->create(['level' => 'building']);
    $this->site = Site::factory()->create();


    $this->formData = [
        'name' => 'New building',
        'description' => 'Description new building',
        'levelType' => $this->site->id,
        'locationType' => $this->locationType->id,
        'need_maintenance' => false
    ];
});

it('creates a QR Code when need_qr_code is true at building\'s creation', function () {

    $formData = [...$this->formData, 'need_qr_code' => true];

    $response = $this->postToTenant('api.buildings.store', $formData);
    $response->assertSessionHasNoErrors();

    $location = Building::first();

    $qr_hash = generateQRCodeHash($location);

    $fileName = 'qr_'  . $qr_hash . '_' . Carbon::now()->isoFormat('YYYYMMDDHHMM')  . '.png';
    $qrPath = tenancy()->tenant->id . "/buildings/" . $location->id . "/qrcode/" . $fileName;

    assertDatabaseHas(
        'buildings',
        [
            'id' => $location->id,
            'qr_hash' => $qr_hash,
            'qr_code' => $qrPath
        ]
    );

    Storage::disk('tenants')->assertExists($location->qrPath);
});


it('can regenerate a QR Code for a building', function () {
    $location = Building::factory()->create();

    $response = $this->postToTenant('api.buildings.qr.regen', [], $location->reference_code);
    $response->assertSessionHasNoErrors();

    $qr_hash = generateQRCodeHash( $location);

    $fileName = 'qr_'  . $qr_hash . '_' . Carbon::now()->isoFormat('YYYYMMDDHHMM')  . '.png';
    $qrPath = tenancy()->tenant->id . "/buildings/" . $location->id . "/qrcode/" . $fileName;

    assertDatabaseHas(
        'buildings',
        [
            'id' => $location->id,
            'qr_hash' => $qr_hash,
            'qr_code' => $qrPath
        ]
    );

    Storage::disk('tenants')->assertExists($location->qrPath);
});