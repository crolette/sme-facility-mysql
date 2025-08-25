<?php

namespace App\Http\Controllers\API\V1;

use Exception;
use App\Helpers\ApiResponse;
use App\Models\LocationType;
use App\Models\Tenants\Site;
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
use App\Http\Requests\Tenant\MaintainableRequest;
use App\Http\Requests\Tenant\DocumentUploadRequest;
use App\Http\Requests\Tenant\TenantBuildingRequest;
use App\Http\Requests\Tenant\ContractWithModelStoreRequest;

class APIBuildingController extends Controller
{
    public function __construct(
        protected QRCodeService $qrCodeService,
        protected MaintainableService $maintainableService,
        protected ContractService $contractService
    ) {}


    public function store(TenantBuildingRequest $buildingRequest, MaintainableRequest $maintainableRequest, ContractWithModelStoreRequest $contractRequest, DocumentUploadRequest $documentUploadRequest, DocumentService $documentService)
    {

        if (Auth::user()->cannot('create', Building::class))
            abort(403);

        try {
            DB::beginTransaction();

            $site = Site::find($buildingRequest->validated('levelType'));
            $buildingType = LocationType::find($buildingRequest->validated('locationType'));
            $count = Building::where('location_type_id', $buildingType->id)->where('level_id', $site->id)->count();

            $code = generateCodeNumber($count + 1, $buildingType->prefix);
            $referenceCode = $site->reference_code . '-' . $code;

            $building = Building::create([
                'code' => $code,
                'surface_floor' => $buildingRequest->validated('surface_floor'),
                'floor_material_id'  => $buildingRequest->validated('floor_material_id') === 'other' ? null :  $buildingRequest->validated('floor_material_id'),
                'floor_material_other'  => $buildingRequest->validated('floor_material_other'),
                'surface_walls' => $buildingRequest->validated('surface_walls'),
                'wall_material_id'  => $buildingRequest->validated('wall_material_id') === 'other' ? null :  $buildingRequest->validated('wall_material_id'),
                'wall_material_other'  => $buildingRequest->validated('wall_material_other'),
                'surface_outdoor' => $buildingRequest->validated('surface_outdoor'),
                'outdoor_material_id'  => $buildingRequest->validated('outdoor_material_id') === 'other' ? null :  $buildingRequest->validated('outdoor_material_id'),
                'outdoor_material_other'  => $buildingRequest->validated('outdoor_material_other'),
                'reference_code' => $referenceCode,
                'location_type_id' => $buildingType->id
            ]);

            $building->site()->associate($site);
            $building->save();

            $building = $this->maintainableService->createMaintainable($building, $maintainableRequest);
            $this->contractService->createWithModel($building, $contractRequest->validated('contracts'));

            $files = $documentUploadRequest->validated('files');
            if ($files) {
                $documentService->uploadAndAttachDocuments($building, $files);
            }
            if ($buildingRequest->validated('need_qr_code') === true)
                $this->qrCodeService->createAndAttachQR($building);

            DB::commit();
            return ApiResponse::success('', 'Building created');
        } catch (Exception $e) {
            DB::rollback();
            Log::error($e->getMessage());
            return ApiResponse::error('ERROR : ' . $e->getMessage());
            return redirect()->back()->with(['message' => 'ERROR : ' . $e->getMessage(), 'type' => 'error']);
        }
        return ApiResponse::error('Error while creating the building');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TenantBuildingRequest $buildingRequest, MaintainableRequest $maintainableRequest, Building $building)
    {
        if (Auth::user()->cannot('update', $building))
            abort(403);

        // TODO Check how to perform a check or be sure that a user can't change the level/location type as it would change every child (building, floor, room)

        if ($buildingRequest->validated('locationType') !== $building->locationType->id) {
            $errors = new MessageBag([
                'locationType' => ['You cannot change the building type of a location'],
            ]);
            return ApiResponse::error('Error while updating the site', $errors);
        }

        try {
            DB::beginTransaction();

            $building->update([
                'surface_floor' => $buildingRequest->validated('surface_floor'),
                'floor_material_id'  => $buildingRequest->validated('floor_material_id') === 'other' ? null :  $buildingRequest->validated('floor_material_id'),
                'floor_material_other'  => $buildingRequest->validated('floor_material_other'),
                'surface_walls' => $buildingRequest->validated('surface_walls'),
                'wall_material_id'  => $buildingRequest->validated('wall_material_id') === 'other' ? null :  $buildingRequest->validated('wall_material_id'),
                'wall_material_other'  => $buildingRequest->validated('wall_material_other'),
                'surface_outdoor' => $buildingRequest->validated('surface_outdoor'),
                'outdoor_material_id'  => $buildingRequest->validated('outdoor_material_id') === 'other' ? null :  $buildingRequest->validated('outdoor_material_id'),
                'outdoor_material_other'  => $buildingRequest->validated('outdoor_material_other'),
            ]);

            $building = $this->maintainableService->createMaintainable($building, $maintainableRequest);

            DB::commit();
            return ApiResponse::success('', 'Site updated');
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
    public function destroy(Building $building)
    {
        if (Auth::user()->cannot('delete', $building))
            abort(403);

        if (count($building->assets) > 0 || count($building->floors) > 0) {
            abort(409)->with(['message' => 'Building cannot be deleted ! Assets and/or floors are linked to this building', 'type' => 'warning']);
        }

        $building->delete();
        return ApiResponse::success('', 'site deleted');
    }
}
