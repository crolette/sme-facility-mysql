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
use App\Http\Requests\Tenant\MaintainableUpdateRequest;
use App\Http\Requests\Tenant\ContractWithModelStoreRequest;
use App\Services\BuildingService;

class APIBuildingController extends Controller
{
    public function __construct(
        protected BuildingService $buildingService,
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

            $building = $this->buildingService->create($buildingRequest->validated());

            $this->maintainableService->create($building, $maintainableRequest->validated());

            if ($contractRequest->validated('contracts'))
                $this->contractService->createWithModel($building, $contractRequest->validated('contracts'));


            if ($contractRequest->validated('existing_contracts'))
                $this->contractService->attachExistingContractsToModel($building, $contractRequest->validated('existing_contracts'));

            if ($documentUploadRequest->validated('files')) {
                $documentService->uploadAndAttachDocuments($building, $documentUploadRequest->validated('files'));
            }

            if ($documentUploadRequest->validated('existing_documents')) {
                $documentService->attachExistingDocumentsToModel($building, $documentUploadRequest->validated('existing_documents'));
            }

            if ($buildingRequest->validated('need_qr_code') === true)
                $this->qrCodeService->createAndAttachQR($building);

            DB::commit();
            return ApiResponse::successFlash('', 'Building created');
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
    public function update(TenantBuildingRequest $buildingRequest, MaintainableUpdateRequest $maintainableRequest, Building $building)
    {
        if (Auth::user()->cannot('update', $building))
            abort(403);

        // TODO Check how to perform a check or be sure that a user can't change the level/location type as it would change every child (building, floor, room)

        if ($buildingRequest->validated('locationType') !== $building->locationType->id) {
            $errors = new MessageBag([
                'locationType' => ['You cannot change the building type of a location'],
            ]);
            return ApiResponse::error('Error while updating the building', $errors);
        }

        try {
            DB::beginTransaction();

            $building = $this->buildingService->update($building, $buildingRequest->validated());

            $this->maintainableService->update($building->maintainable, $maintainableRequest);

            DB::commit();
            return ApiResponse::success('', 'Building updated');
        } catch (Exception $e) {
            DB::rollback();
            Log::error($e->getMessage());
            return ApiResponse::error('ERROR : ' . $e->getMessage());
        }
        return ApiResponse::error('Error while updating the building');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Building $building)
    {
        if (Auth::user()->cannot('delete', $building))
            abort(403);

        if (count($building->assets) > 0 || count($building->floors) > 0) {
            return ApiResponse::error('Building cannot be deleted ! Assets and/or floors are linked to this building', [], 409);
        }

        $building->delete();
        return ApiResponse::success('', 'Building deleted');
    }
}
