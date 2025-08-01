<?php

namespace App\Http\Controllers\Tenants;

use Exception;
use Inertia\Inertia;
use App\Models\LocationType;
use Illuminate\Http\Request;
use App\Models\Tenants\Floor;
use App\Services\QRCodeService;
use App\Models\Tenants\Building;
use App\Services\DocumentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\Central\CategoryType;
use App\Http\Requests\Tenant\TenantFloorRequest;
use App\Http\Requests\Tenant\MaintainableRequest;
use App\Http\Requests\Tenant\DocumentUploadRequest;

class TenantFloorController extends Controller
{
    public function __construct(
        protected QRCodeService $qrCodeService,
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $floors = Floor::with('building')->get();
        return Inertia::render('tenants/locations/index', ['locations' => $floors, 'routeName' => 'floors']);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $levelTypes = Building::all();
        $locationTypes = LocationType::where('level', 'floor')->get();
        $documentTypes = CategoryType::where('category', 'document')->get();
        return Inertia::render('tenants/locations/create', ['levelTypes' => $levelTypes, 'locationTypes' => $locationTypes, 'routeName' => 'floors', 'documentTypes' => $documentTypes]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TenantFloorRequest $floorRequest, MaintainableRequest $maintainableRequest, DocumentUploadRequest $documentUploadRequest, DocumentService $documentService)
    {
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
                'surface_walls' => $floorRequest->validated('surface_walls'),
                'reference_code' => $referenceCode,
                'location_type_id' => $floorType->id
            ]);

            $floor->building()->associate($building);
            $floor->save();

            $floor->maintainable()->create([
                ...$maintainableRequest->validated()
            ]);

            $files = $documentUploadRequest->validated('files');
            if ($files) {
                $documentService->uploadAndAttachDocuments($floor, $files);
            }
            $this->qrCodeService->createAndAttachQR($floor);

            DB::commit();

            return redirect()->route('tenant.floors.index')->with(['message' => 'Floor created', 'type' => 'success']);
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
    public function show(Floor $floor)
    {
        return Inertia::render('tenants/locations/show', ['routeName' => 'floors', 'location' => $floor->load(['locationType', 'documents'])]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Floor $floor)
    {
        $levelTypes = Building::all();
        $locationTypes = LocationType::where('level', 'floor')->get();
        $documentTypes = CategoryType::where('category', 'document')->get();
        return Inertia::render('tenants/locations/create', ['location' => $floor->load('building'), 'levelTypes' => $levelTypes, 'locationTypes' => $locationTypes, 'routeName' => 'floors', 'documentTypes' => $documentTypes]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TenantFloorRequest $floorRequest, MaintainableRequest $maintainableRequest, Floor $floor)
    {
        // TODO Check how to perform a check or be sure that a user can't change the level/location type as it would change every child (building, floor, room)

        if (intval($floorRequest->validated('locationType')) !== $floor->locationType->id) {
            $errors = new MessageBag([
                'locationType' => ['You cannot change the floor type of a location'],
            ]);
            return back()->withErrors($errors)->withInput()->with(['message' => 'Error !', 'type' => 'error']);
        }

        if (intval($floorRequest->validated('levelType')) !== $floor->building->id) {
            $errors = new MessageBag([
                'levelType' => ['You cannot change the level type of a location'],
            ]);
            return back()->withErrors($errors)->withInput()->with(['message' => 'Error !', 'type' => 'error']);
        }

        try {
            DB::beginTransaction();

            $floor->update([
                'surface_floor' => $floorRequest->validated('surface_floor'),
                'surface_walls' => $floorRequest->validated('surface_walls'),
            ]);

            $floor->maintainable()->update([
                ...$maintainableRequest->validated()
            ]);

            DB::commit();
            return redirect()->route('tenant.floors.index')->with(['message' => 'Floor updated', 'type' => 'success']);
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
    public function destroy(Floor $floor)
    {
        if (count($floor->assets) > 0 || count($floor->rooms) > 0) {
            abort(409)->with(['message' => 'Floor cannot be deleted ! Assets and/or rooms are linked to this floor', 'type' => 'warning']);
        }

        $floor->delete();
        return redirect()->route('tenant.floors.index')->with(['message' => 'Floor deleted', 'type' => 'success']);
    }
}
