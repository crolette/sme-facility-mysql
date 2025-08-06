<?php

namespace App\Http\Controllers\API\V1;

use Exception;
use App\Helpers\ApiResponse;
use App\Models\LocationType;
use App\Models\Tenants\Site;
use App\Models\Tenants\Asset;
use App\Services\AssetService;
use App\Services\QRCodeService;
use App\Services\PictureService;
use App\Services\DocumentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\MaintainableService;
use App\Http\Requests\Tenant\AssetRequest;
use App\Http\Requests\Tenant\TenantSiteRequest;
use App\Http\Requests\Tenant\MaintainableRequest;
use App\Http\Requests\Tenant\PictureUploadRequest;
use App\Http\Requests\Tenant\DocumentUploadRequest;

class APISiteController extends Controller
{
    public function __construct(
        protected QRCodeService $qrCodeService,
        protected MaintainableService $maintainableService
    ) {}


    public function store(TenantSiteRequest $siteRequest, MaintainableRequest $maintainableRequest, DocumentUploadRequest $documentUploadRequest, DocumentService $documentService)
    {
        if (Auth::user()->cannot('create', Site::class))
            abort(403);

        try {
            DB::beginTransaction();

            $locationType = LocationType::find($siteRequest->validated('locationType'));
            $count = Site::where('location_type_id', $locationType->id)->count();

            $codeNumber = generateCodeNumber($count + 1, $locationType->prefix);

            $site = Site::create([
                'code' => $codeNumber,
                'surface_floor' => $siteRequest->validated('surface_floor'),
                'surface_walls' => $siteRequest->validated('surface_walls'),
                'reference_code' => $codeNumber,
                'location_type_id' => $locationType->id
            ]);

            $site = $this->maintainableService->createMaintainable($site, $maintainableRequest);

            $files = $documentUploadRequest->validated('files');
            if ($files) {
                $documentService->uploadAndAttachDocuments($site, $files);
            }

            $this->qrCodeService->createAndAttachQR($site);

            DB::commit();
            return ApiResponse::success('', 'Site created');
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
    public function update(TenantSiteRequest $siteRequest, MaintainableRequest $maintainableRequest,  Site $site)
    {
        if (Auth::user()->cannot('update', $site))
            abort(403);
        // TODO Check how to perform a check or be sure that a user can't change the level/location type as it would change every child (building, floor, room)

        if ($siteRequest->validated('locationType') !== $site->locationType->id) {
            $errors = new MessageBag([
                'locationType' => ['You cannot change the site of a location'],
            ]);
            return ApiResponse::error('ERROR', $errors);
        }

        try {
            DB::beginTransaction();

            $site->update([
                'surface_floor' => $siteRequest->validated('surface_floor'),
                'surface_walls' => $siteRequest->validated('surface_walls'),
            ]);

            $site = $this->maintainableService->createMaintainable($site, $maintainableRequest);

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
            abort(403);

        if (count($site->assets) > 0 || count($site->buildings) > 0) {
            abort(409)->with(['message' => 'Site cannot be deleted ! Assets and/or buildings are linked to this site', 'type' => 'warning']);
        }

        $site->delete();
        return ApiResponse::success('', 'site deleted');
    }
}
