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

    $this->locationType = LocationType::factory()->create(['level' => 'site']);

    $this->formData = [
        'name' => 'New site',
        'description' => 'Description new site',
        'locationType' => $this->locationType->id,
        'need_qr_code' => true
    ];
});


it('creates a QR Code when need_qr_code is true at site\'s creation', function () {

    $formData = [...$this->formData, 'need_qr_code' => true];

    $response = $this->postToTenant('api.sites.store', $formData);
    $response->assertSessionHasNoErrors();

    $location = Site::first();

    $qr_hash = generateQRCodeHash($location);

    $fileName = 'qr_'  . $qr_hash . '_' . Carbon::now()->isoFormat('YYYYMMDDhhmm')  . '.png';
    $qrPath = tenancy()->tenant->id . "/sites/" . $location->id . "/qrcode/" . $fileName;

    assertDatabaseHas(
        'sites',
        [
            'id' => $location->id,
            'qr_hash' => $qr_hash,
            'qr_code' => $qrPath
        ]
    );

    Storage::disk('tenants')->assertExists($location->qrPath);
});


it('can regenerate a QR Code for a site', function () {
    $location = Site::factory()->create();

    $response = $this->postToTenant('api.sites.qr.regen', [], $location->reference_code);
    $response->assertSessionHasNoErrors();

    $qr_hash = generateQRCodeHash($location);

    $fileName = 'qr_'  . $qr_hash . '_' . Carbon::now()->isoFormat('YYYYMMDDhhmm')  . '.png';
    $qrPath = tenancy()->tenant->id . "/sites/" . $location->id . "/qrcode/" . $fileName;

    assertDatabaseHas(
        'sites',
        [
            'id' => $location->id,
            'qr_hash' => $qr_hash,
            'qr_code' => $qrPath
        ]
    );

    Storage::disk('tenants')->assertExists($location->qrPath);
});
