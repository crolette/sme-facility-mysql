<?php

namespace App\Http\Controllers\Tenants;

use Exception;
use Inertia\Inertia;
use App\Models\LocationType;
use App\Models\Tenants\Site;
use Illuminate\Http\Request;
use App\Services\QRCodeService;
use App\Models\Tenants\Building;
use App\Services\DocumentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\Central\CategoryType;
use App\Services\MaintainableService;
use Barryvdh\Debugbar\Facades\Debugbar;
use App\Http\Requests\Tenant\MaintainableRequest;
use App\Http\Requests\Tenant\DocumentUploadRequest;
use App\Http\Requests\Tenant\TenantBuildingRequest;

class TenantBuildingController extends Controller
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
        //
        $buildings = Building::all();
        return Inertia::render('tenants/locations/index', ['locations' => $buildings, 'routeName' => 'buildings']);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        $levelTypes = Site::all();
        $locationTypes = LocationType::where('level', 'building')->get();
        $documentTypes = CategoryType::where('category', 'document')->get();
        return Inertia::render('tenants/locations/create', ['levelTypes' => $levelTypes, 'locationTypes' => $locationTypes, 'routeName' => 'buildings', 'documentTypes' => $documentTypes]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TenantBuildingRequest $buildingRequest, MaintainableRequest $maintainableRequest, DocumentUploadRequest $documentUploadRequest, DocumentService $documentService)
    {

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
                'surface_walls' => $buildingRequest->validated('surface_walls'),
                'reference_code' => $referenceCode,
                'location_type_id' => $buildingType->id
            ]);

            $building->site()->associate($site);
            $building->save();

            $building = $this->maintainableService->createMaintainable($building, $maintainableRequest);


            $files = $documentUploadRequest->validated('files');
            if ($files) {
                $documentService->uploadAndAttachDocuments($building, $files);
            }

            $this->qrCodeService->createAndAttachQR($building);

            DB::commit();

            return redirect()->route('tenant.buildings.index')->with(['message' => 'Building created', 'type' => 'success']);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return redirect()->back()->with(['message' => 'ERROR : ' . $e->getMessage(), 'type' => 'error']);
        }
        return back()->withInput();
    }

    /**
     * Display the specified resource.
     */
    public function show(Building $building)
    {
        // dd($building->load('locationType'));

        return Inertia::render('tenants/locations/show', ['routeName' => 'buildings', 'location' => $building->load('site', 'documents')]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Building $building)
    {
        $levelTypes = Site::all();
        $locationTypes = LocationType::where('level', 'building')->get();
        $documentTypes = CategoryType::where('category', 'document')->get();
        return Inertia::render('tenants/locations/create', ['location' => $building->load('site'), 'levelTypes' => $levelTypes, 'locationTypes' => $locationTypes, 'routeName' => 'buildings', 'documentTypes' => $documentTypes]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TenantBuildingRequest $buildingRequest, MaintainableRequest $maintainableRequest, Building $building)
    {
        // TODO Check how to perform a check or be sure that a user can't change the level/location type as it would change every child (building, floor, room)

        if ($buildingRequest->validated('locationType') !== $building->locationType->id) {
            $errors = new MessageBag([
                'locationType' => ['You cannot change the building type of a location'],
            ]);
            return back()->withErrors($errors)->withInput()->with(['message' => 'Error !', 'type' => 'error']);
        }

        try {
            DB::beginTransaction();

            $building->update([
                'surface_floor' => $buildingRequest->validated('surface_floor'),
                'surface_walls' => $buildingRequest->validated('surface_walls'),
            ]);

            $building = $this->maintainableService->createMaintainable($building, $maintainableRequest);

            DB::commit();
            return redirect()->route('tenant.buildings.index')->with(['message' => 'Building updated', 'type' => 'success']);
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
    public function destroy(Building $building)
    {
        if (count($building->assets) > 0 || count($building->floors) > 0) {
            abort(409)->with(['message' => 'Building cannot be deleted ! Assets and/or floors are linked to this building', 'type' => 'warning']);
        }

        $building->delete();
        return redirect()->back()->with(['message' => 'Building deleted', 'type' => 'success']);
    }
}
