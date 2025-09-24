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
    $this->siteType = LocationType::factory()->create(['level' => 'site']);
    $this->buildingType = LocationType::factory()->create(['level' => 'building']);
    $this->floorType = LocationType::factory()->create(['level' => 'floor']);
    Site::factory()->create();
    $this->building = Building::factory()->create();

    $this->formData = [
        'name' => 'New floor',
        'description' => 'Description new floor',
        'levelType' => $this->building->id,
        'locationType' => $this->floorType->id
    ];
});

it('creates a QR Code when need_qr_code is true at floor\'s creation', function () {

    $formData = [...$this->formData, 'need_qr_code' => true];

    $response = $this->postToTenant('api.floors.store', $formData);
    $response->assertSessionHasNoErrors();

    $location = Floor::first();

    $qr_hash = generateQRCodeHash($location);

    $fileName = 'qr_'  . $qr_hash . '_' . Carbon::now()->isoFormat('YYYYMMDDHHMM')  . '.png';
    $qrPath = tenancy()->tenant->id . "/floors/" . $location->id . "/qrcode/" . $fileName;

    assertDatabaseHas(
        'floors',
        [
            'id' => $location->id,
            'qr_hash' => $qr_hash,
            'qr_code' => $qrPath
        ]
    );

    Storage::disk('tenants')->assertExists($location->qrPath);
});


it('can regenerate a QR Code for a floor', function () {
    $location = Floor::factory()->create();

    $response = $this->postToTenant('api.floors.qr.regen', [], $location->reference_code);
    $response->assertSessionHasNoErrors();

    $qr_hash = generateQRCodeHash($location);

    $fileName = 'qr_'  . $qr_hash . '_' . Carbon::now()->isoFormat('YYYYMMDDHHMM')  . '.png';
    $qrPath = tenancy()->tenant->id . "/floors/" . $location->id . "/qrcode/" . $fileName;

    assertDatabaseHas(
        'floors',
        [
            'id' => $location->id,
            'qr_hash' => $qr_hash,
            'qr_code' => $qrPath
        ]
    );

    Storage::disk('tenants')->assertExists($location->qrPath);
});