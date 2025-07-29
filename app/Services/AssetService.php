<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\Tenants\Room;
use App\Models\Tenants\Site;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Floor;
use App\Models\Tenants\Company;
use App\Models\Tenants\Building;

class AssetService
{
    public function attachLocation(Asset $asset, $locationType, $locationId): Asset
    {
        $location = match ($locationType) {
            'site'  => Site::findOrFail($locationId),
            'building' => Building::findOrFail($locationId),
            'floor' => Floor::findOrFail($locationId),
            'room' => Room::findOrFail($locationId),
        };

        if (!$asset->code) {
            $count = Company::incrementAndGetAssetNumber();
            $codeNumber = generateCodeNumber($count, 'A', 4);
            $asset->code = $codeNumber;
        }

        $referenceCode = $location->reference_code . '-' . $asset->code;

        if ($asset->location) {
            $asset->location()->dissociate();
        }
        $asset->location()->associate($location);

        $asset->reference_code = $referenceCode;

        return $asset;
    }
};
