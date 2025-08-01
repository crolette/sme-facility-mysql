<?php

namespace App\Http\Controllers\Tenants;

use Exception;
use Inertia\Inertia;
use App\Services\QRService;
use App\Models\LocationType;
use App\Models\Tenants\Site;
use Illuminate\Http\Request;
use App\Services\QRCodeService;
use App\Services\DocumentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\Central\CategoryType;
use Barryvdh\Debugbar\Facades\Debugbar;
use App\Http\Requests\Tenant\TenantSiteRequest;
use App\Http\Requests\Tenant\MaintainableRequest;
use App\Http\Requests\Tenant\DocumentUploadRequest;
use App\Services\MaintainableService;

class TenantSiteController extends Controller
{

    public function __construct(
        protected QRCodeService $qrCodeService,
        protected MaintainableService $maintainableService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sites = Site::all();

        return Inertia::render('tenants/locations/index', ['locations' => $sites, 'routeName' => 'sites']);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $locationTypes = LocationType::where('level', 'site')->get();
        $documentTypes = CategoryType::where('category', 'document')->get();
        return Inertia::render('tenants/locations/create', ['locationTypes' => $locationTypes, 'routeName' => 'sites', 'documentTypes' => $documentTypes]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TenantSiteRequest $siteRequest, MaintainableRequest $maintainableRequest, DocumentUploadRequest $documentUploadRequest, DocumentService $documentService)
    {
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
            return redirect()->route('tenant.sites.index')->with(['message' => 'Site created', 'type' => 'success']);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return redirect()->back()->with(['message' => 'ERROR : ' . $e->getMessage(), 'type' => 'error']);
        }

        return redirect()->back()->with(['message' => 'Something went wrong', 'type' => 'warning']);
    }

    /**
     * Display the specified resource.
     */
    public function show(Site $site)
    {
        return Inertia::render('tenants/locations/show', ['routeName' => 'sites', 'location' => $site->load(['locationType', 'documents'])]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Site $site)
    {
        $locationTypes = LocationType::where('level', 'site')->get();

        $documentTypes = CategoryType::where('category', 'document')->get();
        return Inertia::render('tenants/locations/create', ['location' => $site, 'locationTypes' => $locationTypes, 'routeName' => 'sites', 'documentTypes' => $documentTypes]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TenantSiteRequest $siteRequest, MaintainableRequest $maintainableRequest,  Site $site)
    {
        // TODO Check how to perform a check or be sure that a user can't change the level/location type as it would change every child (building, floor, room)

        if ($siteRequest->validated('locationType') !== $site->locationType->id) {
            $errors = new MessageBag([
                'locationType' => ['You cannot change the site of a location'],
            ]);
            return back()->withErrors($errors)->withInput()->with(['message' => 'Error !', 'type' => 'error']);
        }

        try {
            DB::beginTransaction();

            $site->update([
                'surface_floor' => $siteRequest->validated('surface_floor'),
                'surface_walls' => $siteRequest->validated('surface_walls'),
            ]);

            $site = $this->maintainableService->createMaintainable($site, $maintainableRequest);

            DB::commit();
            return redirect()->route('tenant.sites.index')->with(['message' => 'Site updated', 'type' => 'success']);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return redirect()->back()->with(['message' => 'ERROR : ' . $e->getMessage(), 'type' => 'error']);
        }

        return redirect()->back()->with(['message' => 'Something went wrong', 'type' => 'warning']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Site $site)
    {
        if (count($site->assets) > 0 || count($site->buildings) > 0) {
            abort(409)->with(['message' => 'Site cannot be deleted ! Assets and/or buildings are linked to this site', 'type' => 'warning']);
        }

        $site->delete();
        return redirect()->back()->with(['message' => 'Site deleted', 'type' => 'success']);
    }
}
