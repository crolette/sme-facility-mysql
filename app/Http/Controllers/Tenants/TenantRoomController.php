<?php

namespace App\Http\Controllers\Tenants;

use Inertia\Inertia;
use App\Models\LocationType;
use App\Models\Tenants\Room;
use Illuminate\Http\Request;
use App\Models\Tenants\Floor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\TenantRoomRequest;
use App\Http\Requests\Tenant\MaintainableRequest;

class TenantRoomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $locations = Room::with('floor')->get();
        return Inertia::render('tenants/locations/index', ['locations' => $locations, 'routeName' => 'rooms']);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $levelTypes = Floor::all();
        $locationTypes = LocationType::where('level', 'room')->get();

        return Inertia::render('tenants/locations/create', ['levelTypes' => $levelTypes, 'locationTypes' => $locationTypes, 'routeName' => 'rooms']);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TenantRoomRequest $roomRequest, MaintainableRequest $maintainableRequest)
    {

        try {
            DB::beginTransaction();

            $floor = Floor::find($roomRequest->validated('levelType'));
            $roomType = LocationType::find($roomRequest->validated('locationType'));
            $room = new Room($roomRequest->validated());

            $count = Room::where('location_type_id', $roomType->id)->where('level_id', $floor->id)->count();

            $codeNumber = generateCodeNumber($count, $roomType->prefix, 3);

            $referenceCode = $floor->reference_code . '-' . $codeNumber;

            $room->code = $codeNumber;
            $room->reference_code = $referenceCode;

            $room->floor()->associate($floor);
            $room->locationType()->associate($roomType);

            $room->save();

            $room->maintainable()->create($maintainableRequest->validated());

            DB::commit();

            return redirect()->route('tenant.rooms.index')->with(['message' => 'Room created', 'type' => 'success']);
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
    public function show(Room $room)
    {
        return Inertia::render('tenants/locations/show', ['location' => $room->load('floor')]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Room $room)
    {
        $levelTypes = Floor::all();
        $locationTypes = LocationType::where('level', 'room')->get();

        return Inertia::render('tenants/locations/create', ['location' => $room->makeVisible(['level_id', 'location_type_id'])->load('floor'), 'levelTypes' => $levelTypes, 'locationTypes' => $locationTypes, 'routeName' => 'rooms']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TenantRoomRequest $roomRequest, MaintainableRequest $maintainableRequest, Room $room)
    {

        if ($roomRequest->validated('locationType') !== $room->locationType->id) {
            $errors = new MessageBag([
                'locationType' => ['You cannot change the type of a location'],
            ]);
            return back()->withErrors($errors)->withInput()->with(['message' => 'Error !', 'type' => 'error']);
        }

        if ($roomRequest->validated('levelType') !== $room->floor->id) {
            $errors = new MessageBag([
                'levelType' => ['You cannot change the type of a location'],
            ]);
            return back()->withErrors($errors)->withInput()->with(['message' => 'Error !', 'type' => 'error']);
        }

        try {
            DB::beginTransaction();

            $room->maintainable()->update($maintainableRequest->validated());

            DB::commit();

            return redirect()->route('tenant.rooms.index')->with(['message' => 'Room updated', 'type' => 'success']);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return redirect()->back()->with(['message' => 'ERROR : ' . $e->getMessage(), 'type' => 'error']);
        }
        return back()->withInput();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Room $room)
    {
        $room->delete();

        return redirect()->route('tenant.rooms.index')->with(['message' => 'Room deleted', 'type' => 'success']);
    }
}
