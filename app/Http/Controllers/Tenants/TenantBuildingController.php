<?php

namespace App\Http\Controllers\Tenants;

use Exception;
use Inertia\Inertia;
use App\Models\LocationType;
use App\Models\Tenants\Site;
use Illuminate\Http\Request;
use App\Models\Tenants\Building;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Barryvdh\Debugbar\Facades\Debugbar;
use App\Http\Requests\Tenant\MaintainableRequest;
use App\Http\Requests\Tenant\TenantBuildingRequest;

class TenantBuildingController extends Controller
{
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

        return Inertia::render('tenants/locations/create', ['levelTypes' => $levelTypes, 'locationTypes' => $locationTypes, 'routeName' => 'buildings']);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TenantBuildingRequest $buildingRequest, MaintainableRequest $maintainableRequest)
    {

        try {
            DB::beginTransaction();

            $site = Site::find($buildingRequest->validated('levelType'));
            $buildingType = LocationType::find($buildingRequest->validated('locationType'));
            $count = Building::where('location_type_id', $buildingType->id)->where('level_id', $site->id)->count();

            $code = generateCodeNumber($count, $buildingType->prefix);
            $referenceCode = $site->reference_code . '-' . $code;

            $building = Building::create([
                'code' => $code,
                'reference_code' => $referenceCode,
                'location_type_id' => $buildingType->id
            ]);

            $building->site()->associate($site);
            $building->save();

            $building->maintainable()->create([
                ...$maintainableRequest->validated()
            ]);

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

        return Inertia::render('tenants/locations/show', ['location' => $building->load('site')]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Building $building)
    {
        $levelTypes = Site::all();
        $locationTypes = LocationType::where('level', 'building')->get();

        return Inertia::render('tenants/locations/create', ['location' => $building->load('site'), 'levelTypes' => $levelTypes, 'locationTypes' => $locationTypes, 'routeName' => 'buildings']);
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

            $building->maintainable()->update([
                ...$maintainableRequest->validated()
            ]);

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
        $building->delete();
        return redirect()->back()->with(['message' => 'Building deleted', 'type' => 'success']);
    }
}
