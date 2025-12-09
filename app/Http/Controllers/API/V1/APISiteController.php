<?php

namespace App\Http\Controllers\API\V1;

use Exception;
use App\Helpers\ApiResponse;
use App\Models\LocationType;
use App\Models\Tenants\Site;
use App\Services\SiteService;
use App\Services\TenantLimits;
use App\Services\QRCodeService;
use App\Services\ContractService;
use App\Services\DocumentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\MaintainableService;
use App\Http\Requests\Tenant\TenantSiteRequest;
use App\Http\Requests\Tenant\MaintainableRequest;
use App\Http\Requests\Tenant\DocumentUploadRequest;
use App\Http\Requests\Tenant\MaintainableUpdateRequest;
use App\Http\Requests\Tenant\ContractWithModelStoreRequest;

class APISiteController extends Controller
{
    public function __construct(
        protected SiteService $siteService,
        protected QRCodeService $qrCodeService,
        protected MaintainableService $maintainableService,
        protected ContractService $contractService,
    ) {}


    public function store(TenantSiteRequest $siteRequest, ContractWithModelStoreRequest $contractRequest, MaintainableRequest $maintainableRequest, DocumentUploadRequest $documentUploadRequest, DocumentService $documentService)
    {
        if (Auth::user()->cannot('create', Site::class))
            return ApiResponse::notAuthorized();

        try {
            DB::beginTransaction();

            $site = $this->siteService->create($siteRequest->validated());

            $this->maintainableService->create($site, $maintainableRequest->validated());

            if ($documentUploadRequest->validated('files')) {
                $documentService->uploadAndAttachDocuments($site, $documentUploadRequest->validated('files'));
            }

            if ($documentUploadRequest->validated('existing_documents')) {
                $documentService->attachExistingDocumentsToModel($site, $documentUploadRequest->validated('existing_documents'));
            }

            if ($contractRequest->validated('contracts'))
                $this->contractService->createWithModel($site, $contractRequest->validated('contracts'));

            if ($contractRequest->validated('existing_contracts'))
                $this->contractService->attachExistingContractsToModel($site, $contractRequest->validated('existing_contracts'));


            if ($siteRequest->validated('need_qr_code') === true)
                $this->qrCodeService->createAndAttachQR($site);

            DB::commit();

            TenantLimits::setSitesCount();

            return ApiResponse::successFlash('', 'Site created');
        } catch (Exception $e) {
            DB::rollback();
            Log::error($e->getMessage());
            return ApiResponse::error('ERROR : ' . $e->getMessage());
            return redirect()->back()->with(['message' => 'ERROR : ' . $e->getMessage(), 'type' => 'error']);
        }
        return ApiResponse::error('Error while creating the site');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TenantSiteRequest $siteRequest, MaintainableUpdateRequest $maintainableRequest,  Site $site)
    {

        if (Auth::user()->cannot('update', $site))
            return ApiResponse::notAuthorized();

        if ($siteRequest->validated('locationType') !== $site->locationType->id) {
            $errors = new MessageBag([
                'locationType' => ['You cannot change the type of a location'],
            ]);
            return ApiResponse::error('You cannot change the type of a location', $errors);
        }

        try {
            DB::beginTransaction();

            $site = $this->siteService->update($site, $siteRequest->validated());


            $this->maintainableService->update($site->maintainable, $maintainableRequest);

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
    public function destroy(Site $site)
    {
        if (Auth::user()->cannot('delete', $site))
            return ApiResponse::notAuthorized();

        if (count($site->assets) > 0 || count($site->buildings) > 0) {

            return ApiResponse::error('Site cannot be deleted ! Assets and/or buildings are linked to this site', [], 409);
        }

        $response = $this->siteService->deleteSite($site);
        TenantLimits::setSitesCount();

        return $response === true ? ApiResponse::success('', 'Site deleted') : ApiResponse::error('', 'Error during Site deletion');
    }
}
