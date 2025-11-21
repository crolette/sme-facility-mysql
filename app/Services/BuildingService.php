<?php

namespace App\Services;

use Exception;
use App\Models\LocationType;
use App\Models\Tenants\Site;
use App\Models\Tenants\Building;
use App\Services\PictureService;
use App\Services\DocumentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BuildingService
{

    public function __construct(protected DocumentService $documentService, protected PictureService $pictureService) {}

    public function create(array $data): Building
    {
        $site = Site::find($data['levelType']);
        $buildingType = LocationType::find($data['locationType']);

        $count = Building::where('location_type_id', $buildingType->id)->where('level_id', $site->id)->count();

        $code = generateCodeNumber($count + 1, $buildingType->prefix);
        $referenceCode = $site->reference_code . '-' . $code;

        $building = new Building([
            ...$data,
            'code' => $code,
            'reference_code' => $referenceCode,

            'floor_material_id'  => !isset($data['floor_material_id']) ? null : ($data['floor_material_id'] === 'other' ? null :  $data['floor_material_id']),
            'wall_material_id'  => !isset($data['wall_material_id']) ? null : ($data['wall_material_id'] === 'other' ? null :  $data['wall_material_id']),
            'outdoor_material_id'  => !isset($data['outdoor_material_id']) ? null : ($data['outdoor_material_id'] === 'other' ? null :  $data['outdoor_material_id']),
        ]);

        // $building->reference_code = $referenceCode;
        $building->locationType()->associate($buildingType);

        $building->site()->associate($site);
        $building->save();

        return $building;
    }

    public function update(Building $building, array $data): Building
    {
        $building->update([
            ...$data,
            'floor_material_id'  => !isset($data['floor_material_id']) ? null : ($data['floor_material_id'] === 'other' ? null :  $data['floor_material_id']),
            'wall_material_id'  => !isset($data['wall_material_id']) ? null : ($data['wall_material_id'] === 'other' ? null :  $data['wall_material_id']),
            'outdoor_material_id'  => !isset($data['outdoor_material_id']) ? null : ($data['outdoor_material_id'] === 'other' ? null :  $data['outdoor_material_id']),
        ]);

        return $building;
    }


    public function deleteBuilding(Building $building): bool
    {
        try {
            DB::beginTransaction();

            $documents = $building->documents;

            foreach ($documents as $document) {
                $this->documentService->detachDocumentFromModel($building, $document->id);
                $this->documentService->verifyRelatedDocuments($document);
            };

            $pictures = $building->pictures;
            foreach ($pictures as $picture) {
                $this->pictureService->deletePictureFromStorage($picture);
            };

            Storage::disk('tenants')->deleteDirectory($building->directory);

            $deleted = $building->delete();

            DB::commit();
            return $deleted;
        } catch (Exception $e) {
            Log::info('Error during building deletion', ['site' => $building, 'error' => $e->getMessage()]);
            DB::rollBack();
            return false;
        }

        return false;
    }
};
