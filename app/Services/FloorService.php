<?php

namespace App\Services;

use Exception;
use App\Models\LocationType;
use App\Models\Tenants\Floor;
use App\Models\Tenants\Building;
use App\Services\PictureService;
use App\Services\DocumentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FloorService
{

    public function __construct(protected DocumentService $documentService, protected PictureService $pictureService) {}

    public function create(array $data): Floor
    {
        $building = Building::find($data['levelType']);
        $floorType = LocationType::find($data['locationType']);
        $count = Floor::where('location_type_id', $floorType->id)->where('level_id', $building->id)->count();

        $codeNumber = generateCodeNumber($count + 1, $floorType->prefix);

        $referenceCode = $building->reference_code . '-' . $codeNumber;

        $floor = Floor::create([
            ...$data,
            'code' => $codeNumber,
            'reference_code' => $referenceCode,
            'floor_material_id'  => !isset($data['floor_material_id']) ? null : ($data['floor_material_id'] === 'other' ? null :  $data['floor_material_id']),
            'wall_material_id'  => !isset($data['wall_material_id']) ? null : ($data['wall_material_id'] === 'other' ? null :  $data['wall_material_id']),
        ]);

        $floor->locationType()->associate($floorType);
        $floor->building()->associate($building);
        $floor->save();

        return $floor;
    }

    public function update(Floor $floor, array $data): Floor
    {
        $floor->update([
            ...$data,
            'floor_material_id'  => !isset($data['floor_material_id']) ? null : ($data['floor_material_id'] === 'other' ? null :  $data['floor_material_id']),
            'wall_material_id'  => !isset($data['wall_material_id']) ? null : ($data['wall_material_id'] === 'other' ? null :  $data['wall_material_id']),
        ]);

        return $floor;
    }


    public function delete(Floor $floor): bool
    {
        try {
            DB::beginTransaction();
            $documents = $floor->documents;

            foreach ($documents as $document) {
                $this->documentService->detachDocumentFromModel($floor, $document->id);
                $this->documentService->verifyRelatedDocuments($document);
            };

            $pictures = $floor->pictures;
            foreach ($pictures as $picture) {
                $this->pictureService->deletePictureFromStorage($picture);
            };

            Storage::disk('tenants')->deleteDirectory($floor->directory);


            $deleted = $floor->delete();

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
