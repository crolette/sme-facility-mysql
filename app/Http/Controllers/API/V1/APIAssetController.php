<?php

namespace App\Http\Controllers\API\V1;

use Exception;
use App\Helpers\ApiResponse;
use App\Models\Tenants\Asset;
use App\Services\AssetService;
use App\Services\QRCodeService;
use App\Services\PictureService;
use App\Services\ContractService;
use App\Services\DocumentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\MaintainableService;
use App\Http\Requests\Tenant\AssetCreateRequest;
use App\Http\Requests\Tenant\AssetUpdateRequest;
use App\Http\Requests\Tenant\MaintainableRequest;
use App\Http\Requests\Tenant\PictureUploadRequest;
use App\Http\Requests\Tenant\DocumentUploadRequest;
use App\Http\Requests\Tenant\ContractWithModelStoreRequest;

class APIAssetController extends Controller
{
    public function __construct(
        protected QRCodeService $qrCodeService,
        protected AssetService $assetService,
        protected MaintainableService $maintainableService,
        protected ContractService $contractService

    ) {}

    public function store(AssetCreateRequest $assetRequest, MaintainableRequest $maintainableRequest, ContractWithModelStoreRequest $contractRequest, DocumentUploadRequest $documentUploadRequest, PictureUploadRequest $pictureUploadRequest, PictureService $pictureService, DocumentService $documentService,)
    {
        if (Auth::user()->cannot('create', Asset::class))
            abort(403);

        try {
            DB::beginTransaction();

            $asset = new Asset([
                ...$assetRequest->validated(),
            ]);

            $asset = $this->assetService->attachLocation($asset, $assetRequest->validated('locationType'), $assetRequest->validated('locationId'));

            $asset->assetCategory()->associate($assetRequest->validated('categoryId'));
            $asset->save();
            $asset = $this->maintainableService->createMaintainable($asset, $maintainableRequest);

            if ($contractRequest->validated('contracts'))
                $this->contractService->createWithModel($asset, $contractRequest->validated('contracts'));

            $files = $documentUploadRequest->validated('files');
            if ($files) {
                $documentService->uploadAndAttachDocuments($asset, $files);
            }

            $pictures = $pictureUploadRequest->validated('pictures');

            if ($pictures) {
                $pictureService->uploadAndAttachPictures($asset, $pictures);
            }

            if ($assetRequest->validated('need_qr_code') === true)
                $this->qrCodeService->createAndAttachQR($asset);

            DB::commit();
            return ApiResponse::success('', 'Asset created');
        } catch (Exception $e) {
            DB::rollback();
            Log::error($e->getMessage());
            return ApiResponse::error('', 'ERROR : ' . $e->getMessage());
        }
        return ApiResponse::error('', 'Error while creating an asset');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AssetUpdateRequest $request, MaintainableRequest $maintainableRequest, Asset $asset)
    {
        if (Auth::user()->cannot('update', $asset))
            abort(403);

        try {
            DB::beginTransaction();

            if ($request->validated('locationType'))
                $asset = $this->assetService->attachLocation($asset, $request->validated('locationType'), $request->validated('locationId'));

            $categoryId = $request->validated('categoryId') ?? null;
            if ($categoryId && $categoryId !== $asset->assetCategory->id) {
                $asset->assetCategory()->dissociate();
                $asset->assetCategory()->associate($request->validated('categoryId'));
            }

            $asset->update([
                ...$request->validated(),
            ]);

            $asset = $this->maintainableService->createMaintainable($asset, $maintainableRequest);

            $asset->save();

            DB::commit();

            return ApiResponse::success(['reference_code' => $asset->reference_code], 'Asset updated');
        } catch (Exception $e) {
            DB::rollback();
            Log::error($e->getMessage());
            return ApiResponse::error('ERROR : ' . $e->getMessage());
        }
        return ApiResponse::error('Error while updating the asset');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Asset $asset)
    {
        if (Auth::user()->cannot('delete', $asset))
            abort(403);

        $asset->delete();
        return ApiResponse::success('', 'Asset deleted');
    }
}
