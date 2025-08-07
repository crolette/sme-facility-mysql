<?php

namespace App\Http\Controllers\Tenants;

use Inertia\Inertia;
use App\Models\LocationType;
use App\Models\Tenants\Room;
use Illuminate\Http\Request;
use App\Models\Tenants\Floor;
use App\Services\QRCodeService;
use App\Services\DocumentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\MessageBag;
use App\Enums\MaintenanceFrequency;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\Central\CategoryType;
use Illuminate\Support\Facades\Auth;
use App\Services\MaintainableService;
use App\Http\Requests\Tenant\TenantRoomRequest;
use App\Http\Requests\Tenant\MaintainableRequest;
use App\Http\Requests\Tenant\DocumentUploadRequest;

class TenantRoomController extends Controller
{
    public function __construct(
        protected QRCodeService $qrCodeService,
        protected MaintainableService $maintainableService,
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        if (Auth::user()->cannot('viewAny', Room::class))
            abort(403);

        $locations = Room::with('floor')->get();
        return Inertia::render('tenants/locations/index', ['locations' => $locations, 'routeName' => 'rooms']);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        if (Auth::user()->cannot('create', Room::class))
            abort(403);

        $levelTypes = Floor::all();
        $locationTypes = LocationType::where('level', 'room')->get();
        $documentTypes = CategoryType::where('category', 'document')->get();
        $frequencies = array_column(MaintenanceFrequency::cases(), 'value');
        $floorMaterials = CategoryType::where('category', 'floor_materials')->get();
        $wallMaterials = CategoryType::where('category', 'wall_materials')->get();

        return Inertia::render('tenants/locations/create', ['levelTypes' => $levelTypes, 'locationTypes' => $locationTypes, 'routeName' => 'rooms', 'documentTypes' => $documentTypes, 'frequencies' => $frequencies, 'floorMaterials' => $floorMaterials, 'wallMaterials' => $wallMaterials]);
    }


    /**
     * Display the specified resource.
     */
    public function show(Room $room)
    {

        if (Auth::user()->cannot('view', $room))
            abort(403);

        return Inertia::render('tenants/locations/show', ['routeName' => 'rooms', 'item' => $room->load(['floor', 'documents', 'tickets.pictures', 'maintainable.manager', 'maintainable.providers'])]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Room $room)
    {

        if (Auth::user()->cannot('update', $room))
            abort(403);

        $levelTypes = Floor::all();
        $locationTypes = LocationType::where('level', 'room')->get();
        $documentTypes = CategoryType::where('category', 'document')->get();
        $frequencies = array_column(MaintenanceFrequency::cases(), 'value');
        $floorMaterials = CategoryType::where('category', 'floor_materials')->get();
        $wallMaterials = CategoryType::where('category', 'wall_materials')->get();

        return Inertia::render('tenants/locations/create', ['location' => $room->makeVisible(['level_id', 'location_type_id'])->load('floor'), 'levelTypes' => $levelTypes, 'locationTypes' => $locationTypes, 'routeName' => 'rooms', 'documentTypes' => $documentTypes, 'frequencies' => $frequencies, 'floorMaterials' => $floorMaterials, 'wallMaterials' => $wallMaterials]);
    }
}
