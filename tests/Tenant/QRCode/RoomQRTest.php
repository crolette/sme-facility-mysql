<?php

use Carbon\Carbon;
use App\Models\LocationType;
use App\Models\Tenants\Room;
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
use function PHPUnit\Framework\assertJson;
use function PHPUnit\Framework\assertNull;
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
    LocationType::factory()->create(['level' => 'building']);
    LocationType::factory()->create(['level' => 'floor']);
    $this->locationType = LocationType::factory()->create(['level' => 'room']);
    CategoryType::factory()->create(['category' => 'document']);
    $this->site = Site::factory()->create();
    $this->building = Building::factory()->create();
    $this->floor = Floor::factory()->create();

    $this->formData = [
        'name' => 'New room',
        'description' => 'Description new room',
        'levelType' => $this->floor->id,
        'locationType' => $this->locationType->id
    ];
});


it('creates a QR Code when need_qr_code is true at room\'s creation', function () {

    $formData = [...$this->formData, 'need_qr_code' => true];

    $response = $this->postToTenant('api.rooms.store', $formData);
    $response->assertSessionHasNoErrors();

    $location = Room::first();

    $qr_hash = generateQRCodeHash('rooms', $location);

    $fileName = 'qr_'  . $qr_hash . '_' . Carbon::now()->isoFormat('YYYYMMDDHHMM')  . '.png';
    $qrPath = tenancy()->tenant->id . "/rooms/" . $location->id . "/qrcode/" . $fileName;

    assertDatabaseHas(
        'rooms',
        [
            'id' => $location->id,
            'qr_hash' => $qr_hash,
            'qr_code' => $qrPath
        ]
    );

    Storage::disk('tenants')->assertExists($location->qrPath);
});


it('can regenerate a QR Code for a room', function () {
    $location = Room::factory()->create();

    $response = $this->postToTenant('api.rooms.qr.regen', [], $location->reference_code);
    $response->assertSessionHasNoErrors();

    $qr_hash = generateQRCodeHash('rooms', $location);

    $fileName = 'qr_'  . $qr_hash . '_' . Carbon::now()->isoFormat('YYYYMMDDHHMM')  . '.png';
    $qrPath = tenancy()->tenant->id . "/rooms/" . $location->id . "/qrcode/" . $fileName;

    assertDatabaseHas(
        'rooms',
        [
            'id' => $location->id,
            'qr_hash' => $qr_hash,
            'qr_code' => $qrPath
        ]
    );

    Storage::disk('tenants')->assertExists($location->qrPath);
});