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

class RoomService
{

    public function __construct(protected DocumentService $documentService, protected PictureService $pictureService) {}


    public function create(array $data): Room
    {

        $floor = Floor::find($data['levelType']);
        $roomType = LocationType::find($data['locationType']);

        $count = Room::where('location_type_id', $roomType->id)->where('level_id', $floor->id)->count();

        $codeNumber = generateCodeNumber($count + 1, $roomType->prefix, 3);
        $referenceCode = $floor->reference_code . '-' . $codeNumber;

        $room = new Room([
            ...$data,
            'code' => $codeNumber,
            'reference_code' => $referenceCode,

            'floor_material_id'  => !isset($data['floor_material_id']) ? null : ($data['floor_material_id'] === 'other' ? null :  $data['floor_material_id']),
            'wall_material_id'  => !isset($data['wall_material_id']) ? null : ($data['wall_material_id'] === 'other' ? null :  $data['wall_material_id']),
        ]);

        $room->floor()->associate($floor);
        $room->locationType()->associate($roomType);

        $room->save();

        return $room;
    }

    public function update(Room $room, array $data): Room

    {
        $room->update([
            ...$data,
            'floor_material_id'  => !isset($data['floor_material_id']) ? null : ($data['floor_material_id'] === 'other' ? null :  $data['floor_material_id']),
            'wall_material_id'  => !isset($data['wall_material_id']) ? null : ($data['wall_material_id'] === 'other' ? null :  $data['wall_material_id']),
        ]);
        return $room;
    }



    public function deleteRoom(Room $room): bool
    {
        try {
            DB::beginTransaction();


            $documents = $room->documents;

            foreach ($documents as $document) {
                $this->documentService->detachDocumentFromModel($room, $document->id);
                $this->documentService->verifyRelatedDocuments($document);
            };

            $pictures = $room->pictures;
            foreach ($pictures as $picture) {
                $this->pictureService->deletePictureFromStorage($picture);
            };

            Storage::disk('tenants')->deleteDirectory($room->directory);

            $deleted = $room->delete();

            DB::commit();
            return $deleted;
        } catch (Exception $e) {
            Log::info('Error during building deletion', ['site' => $room, 'error' => $e->getMessage()]);
            DB::rollBack();
            return false;
        }

        return false;
    }
};
