<?php

namespace App\Http\Controllers\API\V1;

use Exception;
use App\Helpers\ApiResponse;
use App\Models\LocationType;
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
use App\Http\Requests\Tenant\TenantFloorRequest;
use App\Http\Requests\Tenant\MaintainableRequest;
use App\Http\Requests\Tenant\DocumentUploadRequest;
use App\Http\Requests\Tenant\MaintainableUpdateRequest;
use App\Http\Requests\Tenant\ContractWithModelStoreRequest;

class APIFloorController extends Controller
{
    public function __construct(
        protected QRCodeService $qrCodeService,
        protected MaintainableService $maintainableService,
        protected ContractService $contractService
    ) {}


    public function store(TenantFloorRequest $floorRequest, MaintainableRequest $maintainableRequest, ContractWithModelStoreRequest $contractRequest,  DocumentUploadRequest $documentUploadRequest, DocumentService $documentService)
    {
        if (Auth::user()->cannot('create', Floor::class))
            abort(403);

        try {
            DB::beginTransaction();

            $building = Building::find($floorRequest->validated('levelType'));
            $floorType = LocationType::find($floorRequest->validated('locationType'));
            $count = Floor::where('location_type_id', $floorType->id)->where('level_id', $building->id)->count();

            $codeNumber = generateCodeNumber($count + 1, $floorType->prefix);

            $referenceCode = $building->reference_code . '-' . $codeNumber;

            $floor = Floor::create([
                'code' => $codeNumber,
                'surface_floor' => $floorRequest->validated('surface_floor'),
                'floor_material_id'  => $floorRequest->validated('floor_material_id') === 'other' ? null :  $floorRequest->validated('floor_material_id'),
                'floor_material_other'  => $floorRequest->validated('floor_material_other'),
                'surface_walls' => $floorRequest->validated('surface_walls'),
                'wall_material_id'  => $floorRequest->validated('wall_material_id') === 'other' ? null :  $floorRequest->validated('wall_material_id'),
                'wall_material_other'  => $floorRequest->validated('wall_material_other'),
                'reference_code' => $referenceCode,
                'location_type_id' => $floorType->id
            ]);

            $floor->building()->associate($building);
            $floor->save();

            $this->maintainableService->create($floor, $maintainableRequest);

            if ($contractRequest->validated('contracts'))
                $this->contractService->createWithModel($floor, $contractRequest->validated('contracts'));

            if ($contractRequest->validated('existing_contracts'))
                $this->contractService->attachExistingContractsToModel($floor, $contractRequest->validated('existing_contracts'));

            if ($documentUploadRequest->validated('files')) {
                $documentService->uploadAndAttachDocuments($floor, $documentUploadRequest->validated('files'));
            }

            if ($documentUploadRequest->validated('existing_documents')) {
                $documentService->attachExistingDocumentsToModel($floor, $documentUploadRequest->validated('existing_documents'));
            }

            if ($floorRequest->validated('need_qr_code') === true)
                $this->qrCodeService->createAndAttachQR($floor);

            DB::commit();
            return ApiResponse::successFlash('', 'Floor created');
        } catch (Exception $e) {
            DB::rollback();
            Log::error($e->getMessage());
            return ApiResponse::error('ERROR : ' . $e->getMessage());
            return redirect()->back()->with(['message' => 'ERROR : ' . $e->getMessage(), 'type' => 'error']);
        }
        return ApiResponse::error('Error while creating the floor');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TenantFloorRequest $floorRequest, MaintainableUpdateRequest $maintainableRequest, Floor $floor)
    {
        if (Auth::user()->cannot('update', $floor))
            abort(403);

        // TODO Check how to perform a check or be sure that a user can't change the level/location type as it would change every child (building, floor, room)

        if (intval($floorRequest->validated('locationType')) !== $floor->locationType->id) {
            $errors = new MessageBag([
                'locationType' => ['You cannot change the floor type of a location'],
            ]);
            return ApiResponse::error('You cannot change the floor type of a location', $errors);
        }

        if (intval($floorRequest->validated('levelType')) !== $floor->building->id) {
            $errors = new MessageBag([
                'levelType' => ['You cannot change the level type of a location'],
            ]);
            return ApiResponse::error('You cannot change the level type of a location', $errors);
        }

        try {
            DB::beginTransaction();

            $floor->update([
                'surface_floor' => $floorRequest->validated('surface_floor'),
                'floor_material_id'  => $floorRequest->validated('floor_material_id') === 'other' ? null :  $floorRequest->validated('floor_material_id'),
                'floor_material_other'  => $floorRequest->validated('floor_material_other'),
                'surface_walls' => $floorRequest->validated('surface_walls'),
                'wall_material_id'  => $floorRequest->validated('wall_material_id') === 'other' ? null :  $floorRequest->validated('wall_material_id'),
                'wall_material_other'  => $floorRequest->validated('wall_material_other'),
            ]);

            $this->maintainableService->update($floor->maintainable, $maintainableRequest);

            DB::commit();
            return ApiResponse::success('', 'Floor updated');
        } catch (Exception $e) {
            DB::rollback();
            Log::error($e->getMessage());
            return ApiResponse::error('ERROR : ' . $e->getMessage());
        }
        return ApiResponse::error('Error while updating the site');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Floor $floor)
    {

        if (Auth::user()->cannot('delete', $floor))
            abort(403);

        if (count($floor->assets) > 0 || count($floor->rooms) > 0) {
            return ApiResponse::error('Floor cannot be deleted ! Assets and/or rooms are linked to this floor', [], 409);
        }

        $floor->delete();
        return ApiResponse::success('', 'Floor deleted');
    }
}
