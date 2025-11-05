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
use Illuminate\Support\Facades\Log;

class AssetService
{

    public function create(array $data): Asset
    {

        $asset = new Asset([
            ...$data,
        ]);

        return $asset;
    }

    public function update(Asset $asset, array $data)
    {
        Log::info('ASSET UPDATE SERVICE');
        Log::info($data);
        $updated = $asset->update(
            [
                ...$data
            ],
        );

        Log::info('ASSET UPDATED : ' . $updated);

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
                $asset->code = $this->createAssetCodeNumber();
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

    public function attachLocationFromImport($asset, $assetData)
    {
        // will be the reference code of the location or email of the user
        $locationCode = null;

        $locationType = $assetData['is_mobile'] === true ? 'user' : (isset($assetData['room']) ? 'room' : (isset($assetData['floor']) ? 'floor' : (isset($assetData['building']) ? 'building' :
            'site')));

        if ($locationType === 'user') {
            $locationCode = Str::after($assetData['user'], ' - ');
        } else {
            $locationCode = match ($locationType) {
                'site'  => Str::before($assetData['site'], ' - '),
                'building' => Str::before($assetData['building'], ' - '),
                'floor' => Str::before($assetData['floor'], ' - '),
                'room' => Str::before($assetData['room'], ' - '),
            };
        }

        $location = match ($locationType) {
            'user'  => User::where('email', $locationCode)->first(),
            'site'  => Site::where('reference_code', $locationCode)->first(),
            'building' => Building::where('reference_code', $locationCode)->first(),
            'floor' => Floor::where('reference_code', $locationCode)->first(),
            'room' => Room::where('reference_code', $locationCode)->first(),
        };

        if (!$location) {
            Log::error("No location found during import ", $assetData);
            throw new Exception("No location found");
        }

        if ($asset->location_type === get_class($location) && $asset->location_id === $location->id)
            return $asset;


        if (!$asset->code) {
            $asset->code = $this->createAssetCodeNumber();
        }

        $referenceCode = $locationType === 'user' ? $asset->code : $location->reference_code . '-' . $asset->code;

        $asset->location()->associate($location);

        $asset->reference_code = $referenceCode;

        $asset->save();

        return $asset;
    }

    private function createAssetCodeNumber(): string
    {
        $count = Company::incrementAndGetAssetNumber();
        $codeNumber = generateCodeNumber($count, 'A', 4);

        return $codeNumber;
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
