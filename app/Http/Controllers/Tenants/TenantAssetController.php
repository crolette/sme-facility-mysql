<?php

namespace App\Http\Controllers\Tenants;

use Exception;
use Carbon\Carbon;
use Inertia\Inertia;
use App\Enums\TicketStatus;
use App\Services\QRService;
use Illuminate\Support\Str;
use App\Models\Tenants\Room;
use App\Models\Tenants\Site;
use Illuminate\Http\Request;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Floor;
use App\Services\QRCodeService;
use App\Models\Tenants\Building;
use App\Models\Tenants\Document;
use App\Services\PictureService;
use App\Services\DocumentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\Central\CategoryType;
use Illuminate\Support\Facades\Auth;
use App\Models\Central\AssetCategory;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Tenant\AssetRequest;
use App\Http\Requests\Tenant\FileUploadRequest;
use App\Http\Requests\Tenant\MaintainableRequest;
use App\Http\Requests\Tenant\PictureUploadRequest;
use App\Http\Requests\Tenant\DocumentUploadRequest;
use App\Models\Tenants\Company;

class TenantAssetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $assets = Asset::orderBy('id')->get();
        return Inertia::render('tenants/assets/index', ['assets' => $assets]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = CategoryType::where('category', 'asset')->get();
        $documentTypes = CategoryType::where('category', 'document')->get();
        return Inertia::render('tenants/assets/create', ['categories' => $categories, 'documentTypes' => $documentTypes]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AssetRequest $assetRequest, MaintainableRequest $maintainableRequest, DocumentUploadRequest $documentUploadRequest, PictureUploadRequest $pictureUploadRequest, PictureService $pictureService, DocumentService $documentService, QRCodeService $qrService)
    {

        try {
            DB::beginTransaction();

            $location = null;
            if ($assetRequest->validated('locationType') === 'site') {
                $location = Site::where('id', $assetRequest->validated('locationId'))->where('reference_code', $assetRequest->validated('locationReference'))->first();
            } elseif ($assetRequest->validated('locationType') === 'building') {
                $location = Building::where('id', $assetRequest->validated('locationId'))->where('reference_code', $assetRequest->validated('locationReference'))->first();
            } elseif ($assetRequest->validated('locationType') === 'floor') {
                $location = Floor::where('id', $assetRequest->validated('locationId'))->where('reference_code', $assetRequest->validated('locationReference'))->first();
            } elseif ($assetRequest->validated('locationType') === 'room') {
                $location = Room::where('id', $assetRequest->validated('locationId'))->where('reference_code', $assetRequest->validated('locationReference'))->first();
            }

            $count = Company::incrementAndGetAssetNumber();
            $codeNumber = generateCodeNumber($count, 'A', 4);

            $referenceCode = $location->reference_code . '-' . $codeNumber;

            $asset = new Asset([
                ...$assetRequest->validated(),
                'code' => $codeNumber,
                'reference_code' => $referenceCode
            ]);



            $asset->assetCategory()->associate($assetRequest->validated('categoryId'));
            $asset->location()->associate($location);

            $asset->save();

            $asset->maintainable()->create($maintainableRequest->validated());

            $files = $documentUploadRequest->validated('files');

            if ($files) {
                $documentService->uploadAndAttachDocuments($asset, $files);
            }

            $pictures = $pictureUploadRequest->validated('pictures');

            if ($pictures) {
                $pictureService->uploadAndAttachPictures($asset, $pictures);
            }

            $qrService->createAndAttachQR($asset);

            DB::commit();

            return redirect()->route('tenant.assets.index')->with(['message' => 'Asset created', 'type' => 'success']);
        } catch (Exception $e) {
            DB::rollback();
            Log::error($e->getMessage());
            return redirect()->back()->with(['message' => 'ERROR : ' . $e->getMessage(), 'type' => 'error']);
        }
        return back()->withInput();
    }

    /**
     * Display the specified resource.
     */
    public function show(Asset $asset)
    {
        return Inertia::render('tenants/assets/show', ['asset' => $asset->load('documents', 'pictures', 'tickets.pictures')]);
    }

    public function showDeleted($id)
    {
        $asset = Asset::withTrashed()->findOrFail($id);
        return Inertia::render('tenants/assets/show', ['asset' => $asset->load('documents', 'pictures', 'tickets.pictures')]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Asset $asset)
    {
        $categories = CategoryType::where('category', 'asset')->get();
        $documentTypes = CategoryType::where('category', 'document')->get();
        return Inertia::render('tenants/assets/create', ['asset' => $asset->load(['assetCategory', 'documents']), 'categories' => $categories, 'documentTypes' => $documentTypes]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AssetRequest $assetRequest, MaintainableRequest $maintainableRequest, Asset $asset)
    {
        try {
            DB::beginTransaction();

            $location = null;
            $locationType = $assetRequest->validated('locationType') ?? null;
            if ($locationType) {
                if ($assetRequest->validated('locationType') === 'site') {
                    $location = Site::where('id', $assetRequest->validated('locationId'))->where('reference_code', $assetRequest->validated('locationReference'))->first();
                } elseif ($assetRequest->validated('locationType') === 'building') {
                    $location = Building::where('id', $assetRequest->validated('locationId'))->where('reference_code', $assetRequest->validated('locationReference'))->first();
                } elseif ($assetRequest->validated('locationType') === 'floor') {
                    $location = Floor::where('id', $assetRequest->validated('locationId'))->where('reference_code', $assetRequest->validated('locationReference'))->first();
                } elseif ($assetRequest->validated('locationType') === 'room') {
                    $location = Room::where('id', $assetRequest->validated('locationId'))->where('reference_code', $assetRequest->validated('locationReference'))->first();
                }

                $referenceCode = $location->reference_code . '-' . $asset->code;
                $asset->location()->dissociate();
                $asset->location()->associate($location);

                $asset->update([
                    'reference_code' => $referenceCode
                ]);
            }

            $categoryId = $assetRequest->validated('categoryId') ?? null;
            if ($categoryId && $categoryId !== $asset->assetCategory->id) {
                $asset->assetCategory()->dissociate();
                $asset->assetCategory()->associate($assetRequest->validated('categoryId'));
            }

            $asset->update([
                ...$assetRequest->validated(),
            ]);


            $asset->save();

            $asset->maintainable()->update($maintainableRequest->validated());

            DB::commit();

            return redirect()->route('tenant.assets.index')->with(['message' => 'Asset updated', 'type' => 'success']);
        } catch (Exception $e) {
            DB::rollback();
            Log::error($e->getMessage());
            return redirect()->back()->with(['message' => 'ERROR : ' . $e->getMessage(), 'type' => 'error']);
        }
        return back()->withInput();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Asset $asset)
    {
        $asset->delete();
        return redirect()->route('tenant.assets.index')->with(['message' => 'Asset deleted', 'type' => 'success']);;
    }
}
