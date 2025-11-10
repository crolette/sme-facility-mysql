<?php

namespace App\Services;

use Exception;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\LocationType;
use App\Models\Tenants\Room;
use App\Models\Tenants\Site;
use App\Models\Tenants\User;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Floor;
use App\Models\Tenants\Company;
use App\Models\Tenants\Building;
use App\Services\DocumentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FloorService
{

    public function __construct(protected DocumentService $documentService) {}

    // public function create(array $data): Building
    // {
    //     $site = Site::find($data['levelType']);
    //     $buildingType = LocationType::find($data['locationType']);

    //     $count = Building::where('location_type_id', $buildingType->id)->where('level_id', $site->id)->count();

    //     $code = generateCodeNumber($count + 1, $buildingType->prefix);
    //     $referenceCode = $site->reference_code . '-' . $code;

    //     $building = new Building([
    //         ...$data,
    //         'reference_code' => $referenceCode,
    //         'code' => $code,

    //         'floor_material_id'  => !isset($data['floor_material_id']) ? null : ($data['floor_material_id'] === 'other' ? null :  $data['floor_material_id']),
    //         'wall_material_id'  => !isset($data['wall_material_id']) ? null : ($data['wall_material_id'] === 'other' ? null :  $data['wall_material_id']),
    //         'outdoor_material_id'  => !isset($data['outdoor_material_id']) ? null : ($data['outdoor_material_id'] === 'other' ? null :  $data['outdoor_material_id']),
    //     ]);

    //     $building->reference_code = $referenceCode;
    //     $building->locationType()->associate($buildingType);

    //     $building->site()->associate($site);
    //     $building->save();

    //     return $building;
    // }

    // public function update(Building $building, array $data): Building
    // {
    //     $building->update([
    //         ...$data,
    //         'floor_material_id'  => !isset($data['floor_material_id']) ? null : ($data['floor_material_id'] === 'other' ? null :  $data['floor_material_id']),
    //         'wall_material_id'  => !isset($data['wall_material_id']) ? null : ($data['wall_material_id'] === 'other' ? null :  $data['wall_material_id']),
    //         'outdoor_material_id'  => !isset($data['outdoor_material_id']) ? null : ($data['outdoor_material_id'] === 'other' ? null :  $data['outdoor_material_id']),
    //     ]);

    //     return $building;
    // }

    // private function createAssetCodeNumber(): string
    // {
    //     $count = Company::incrementAndGetAssetNumber();
    //     $codeNumber = generateCodeNumber($count, 'A', 4);

    //     return $codeNumber;
    // }

    // public function attachLocationFromImport($asset, $assetData)
    // {
    //     // will be the reference code of the location or email of the user
    //     $locationCode = null ;

    //     $locationType = $assetData['is_mobile'] === true ? 'user' : 
    //         (isset($assetData['room']) ? 'room' : 
    //             (isset($assetData['floor']) ? 'floor' : 
    //                 (isset($assetData['building']) ? 'building' : 
    //                     'site')));


    //     if($locationType === 'user') {
    //         $locationCode = Str::after($assetData['user'], ' - ');
    //     } else {
    //         $locationCode = match ($locationType) {
    //             'site'  => Str::before($assetData['site'], ' - '),
    //             'building' => Str::before($assetData['building'], ' - '),
    //             'floor' => Str::before($assetData['floor'], ' - '),
    //             'room' => Str::before($assetData['room'], ' - '),
    //         };
    //     }

    //     $location = match ($locationType) {
    //         'user'  => User::where('email', $locationCode)->first(),
    //         'site'  => Site::where('reference_code', $locationCode)->first(),
    //         'building' => Building::where('reference_code', $locationCode)->first(),
    //         'floor' => Floor::where('reference_code', $locationCode)->first(),
    //         'room' => Room::where('reference_code', $locationCode)->first(),
    //     };

    //     if (!$location)
    //         throw new Exception("No location found");

    //     if (!$asset->code) {
    //         $asset->code = $this->createAssetCodeNumber();
    //     }

    //     $referenceCode = $locationType === 'user' ? $asset->code : $location->reference_code . '-' . $asset->code;

    //     $asset->location()->associate($location);

    //     $asset->reference_code = $referenceCode;

    //     return $asset;
    // }



    // public function deleteAsset(Asset $asset): bool
    // {
    //     try {
    //         DB::beginTransaction();
    //         $deleted = $asset->delete();
    //         DB::commit();
    //         return $deleted;
    //     } catch (Exception $e) {
    //         DB::rollBack();
    //         return false;
    //     }

    //     return false;
    // }

    public function deleteRoom(Floor $floor): bool
    {
        try {
            DB::beginTransaction();
            $deleted = $floor->delete();

            $documents = $floor->documents;

            foreach ($documents as $document) {
                $this->documentService->detachDocumentFromModel($floor, $document->id);
                $this->documentService->verifyRelatedDocuments($document);
            };

            $directory = $floor->directory;

            Storage::disk('tenants')->deleteDirectory($directory);

            DB::commit();
            return $deleted;
        } catch (Exception $e) {
            Log::info('Error during building deletion', ['site' => $floor, 'error' => $e->getMessage()]);
            DB::rollBack();
            return false;
        }

        return false;
    }
};
