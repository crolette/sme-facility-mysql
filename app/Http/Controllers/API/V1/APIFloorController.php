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
use App\Services\FloorService;

class APIFloorController extends Controller
{
    public function __construct(
        protected FloorService $floorService,
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

            $floor = $this->floorService->create($floorRequest->validated());

            $this->maintainableService->create($floor, $maintainableRequest->validated());

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
                'locationType' => ['You cannot change the type of a location'],
            ]);
            return ApiResponse::error('You cannot change the type of a location', $errors);
        }

        if (intval($floorRequest->validated('levelType')) !== $floor->building->id) {
            $errors = new MessageBag([
                'levelType' => ['You cannot change the level type of a location'],
            ]);
            return ApiResponse::error('You cannot change the level type of a location', $errors);
        }

        try {
            DB::beginTransaction();

            $floor = $this->floorService->update($floor, $floorRequest->validated());

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

        $response = $this->floorService->delete($floor);

        return $response === true ? ApiResponse::success('', 'Floor deleted') : ApiResponse::error('', 'Error during Floor deletion');
    }
}
