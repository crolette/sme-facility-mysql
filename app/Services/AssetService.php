<?php

namespace App\Services;

use Exception;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\Tenants\Room;
use App\Models\Tenants\Site;
use App\Models\Tenants\User;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Floor;
use App\Models\Tenants\Company;
use App\Models\Tenants\Building;
use Illuminate\Support\Facades\DB;

class AssetService
{

    public function create(array $data) : Asset
    {

        $asset = new Asset([
            ...$data,
        ]);

        return $asset;
    }

    public function attachLocation(Asset $asset, $locationType, $locationId): Asset | bool
    {
        try {
            DB::beginTransaction();
            $location = match ($locationType) {
                'user'  => User::findOrFail($locationId),
                'site'  => Site::findOrFail($locationId),
                'building' => Building::findOrFail($locationId),
                'floor' => Floor::findOrFail($locationId),
                'room' => Room::findOrFail($locationId),
            };

            if (!$location)
                throw new Exception("No location found");

            if (!$asset->code) {
                $count = Company::incrementAndGetAssetNumber();
                $codeNumber = generateCodeNumber($count, 'A', 4);
                $asset->code = $codeNumber;
            }

            $referenceCode = $locationType === 'user' ? $asset->code : $location->reference_code . '-' . $asset->code;
            // $referenceCode = $location->reference_code . '-' . $asset->code;

            if ($asset->location) {
                $asset->location()->dissociate();
            }
            $asset->location()->associate($location);

            $asset->reference_code = $referenceCode;

            DB::commit();
            return $asset;
        } catch (Exception $e) {
            DB::rollBack();
            return false;
        }
        return false;
    }

    public function deleteAsset(Asset $asset): bool
    {
        try {
            DB::beginTransaction();
            $deleted = $asset->delete();
            DB::commit();
            return $deleted;
        } catch (Exception $e) {
            DB::rollBack();
            return false;
        }

        return false;
    }
};
