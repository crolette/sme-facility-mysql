<?php

namespace App\Http\Controllers\API\V1;

use Exception;
use App\Helpers\ApiResponse;
use App\Models\LocationType;
use App\Models\Tenants\Room;
use App\Models\Tenants\Floor;
use App\Services\QRCodeService;
use App\Models\Tenants\Building;
use App\Services\ContractService;
use App\Services\DocumentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\MaintainableService;
use App\Http\Requests\Tenant\TenantRoomRequest;
use App\Http\Requests\Tenant\TenantFloorRequest;
use App\Http\Requests\Tenant\MaintainableRequest;
use App\Http\Requests\Tenant\DocumentUploadRequest;
use App\Http\Requests\Tenant\ContractWithModelStoreRequest;

class APIRoomController extends Controller
{
    public function __construct(
        protected QRCodeService $qrCodeService,
        protected MaintainableService $maintainableService,
        protected ContractService $contractService
    ) {}


    public function store(TenantRoomRequest $roomRequest, ContractWithModelStoreRequest $contractRequest, MaintainableRequest $maintainableRequest, DocumentUploadRequest $documentUploadRequest, DocumentService $documentService)
    {
        if (Auth::user()->cannot('create', Room::class))
            abort(403);


        try {
            DB::beginTransaction();

            $floor = Floor::find($roomRequest->validated('levelType'));
            $roomType = LocationType::find($roomRequest->validated('locationType'));
            $room = new Room([
                ...$roomRequest->validated(),
                'surface_floor' => $roomRequest->validated('surface_floor'),
                'floor_material_id'  => $roomRequest->validated('floor_material_id') === 'other' ? null :  $roomRequest->validated('floor_material_id'),
                'floor_material_other'  => $roomRequest->validated('floor_material_other'),
                'surface_walls' => $roomRequest->validated('surface_walls'),
                'wall_material_id'  => $roomRequest->validated('wall_material_id') === 'other' ? null :  $roomRequest->validated('wall_material_id'),
                'wall_material_other'  => $roomRequest->validated('wall_material_other'),
            ]);

            $count = Room::where('location_type_id', $roomType->id)->where('level_id', $floor->id)->count();

            $codeNumber = generateCodeNumber($count + 1, $roomType->prefix, 3);
            $referenceCode = $floor->reference_code . '-' . $codeNumber;

            $room->code = $codeNumber;
            $room->reference_code = $referenceCode;

            $room->floor()->associate($floor);
            $room->locationType()->associate($roomType);

            $room->save();

            $this->maintainableService->create($room, $maintainableRequest);
            if ($contractRequest->validated('contracts'))
                $this->contractService->createWithModel($room, $contractRequest->validated('contracts'));

            if ($documentUploadRequest->validated('files')) {
                $documentService->uploadAndAttachDocuments($room, $documentUploadRequest->validated('files'));
            }

            if ($documentUploadRequest->validated('existing_documents')) {
                $documentService->attachExistingDocumentsToModel($room, $documentUploadRequest->validated('existing_documents'));
            }

            if ($roomRequest->validated('need_qr_code') === true)
                $this->qrCodeService->createAndAttachQR($room);

            DB::commit();
            return ApiResponse::success('', 'Room created');
        } catch (Exception $e) {
            DB::rollback();
            Log::error($e->getMessage());
            return ApiResponse::error('ERROR : ' . $e->getMessage());
            return redirect()->back()->with(['message' => 'ERROR : ' . $e->getMessage(), 'type' => 'error']);
        }
        return ApiResponse::error('Error while creating the room');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TenantRoomRequest $roomRequest, MaintainableRequest $maintainableRequest, Room $room)
    {
        if (Auth::user()->cannot('update', $room))
            abort(403);

        if ($roomRequest->validated('locationType') !== $room->locationType->id) {
            $errors = new MessageBag([
                'locationType' => ['You cannot change the type of a location'],
            ]);
            return ApiResponse::error('You cannot change the type of a location', $errors);
        }

        if ($roomRequest->validated('levelType') !== $room->floor->id) {
            $errors = new MessageBag([
                'levelType' => ['You cannot change the level type of a location'],
            ]);
            return ApiResponse::error('You cannot change the level type of a location', $errors);
        }

        try {
            DB::beginTransaction();

            $room->update([
                'surface_floor' => $roomRequest->validated('surface_floor'),
                'floor_material_id'  => $roomRequest->validated('floor_material_id') === 'other' ? null :  $roomRequest->validated('floor_material_id'),
                'floor_material_other'  => $roomRequest->validated('floor_material_other'),
                'surface_walls' => $roomRequest->validated('surface_walls'),
                'wall_material_id'  => $roomRequest->validated('wall_material_id') === 'other' ? null :  $roomRequest->validated('wall_material_id'),
                'wall_material_other'  => $roomRequest->validated('wall_material_other'),
            ]);

            $this->maintainableService->update($room->maintainable, $maintainableRequest);

            DB::commit();
            return ApiResponse::success('', 'Room updated');
        } catch (Exception $e) {
            DB::rollback();
            Log::error($e->getMessage());
            return ApiResponse::error('ERROR : ' . $e->getMessage());
        }
        return ApiResponse::error('Error while updating the room');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Room $room)
    {
        if (Auth::user()->cannot('delete', $room))
            abort(403);


        if (count($room->assets) > 0) {
            abort(409)->with(['message' => 'Room cannot be deleted ! Assets are linked to this room', 'type' => 'warning']);
        }


        $room->delete();
        return ApiResponse::success('', 'Room deleted');
    }
}
