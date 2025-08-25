<?php

namespace App\Http\Controllers\Tenants;

use Inertia\Inertia;
use App\Models\LocationType;
use App\Models\Tenants\Room;
use App\Models\Tenants\Floor;
use App\Enums\NoticePeriodEnum;
use App\Services\QRCodeService;
use App\Enums\ContractStatusEnum;
use App\Enums\ContractDurationEnum;
use App\Enums\MaintenanceFrequency;
use App\Http\Controllers\Controller;
use App\Models\Central\CategoryType;
use Illuminate\Support\Facades\Auth;
use App\Services\MaintainableService;
use App\Enums\ContractRenewalTypesEnum;

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
        $statuses = array_column(ContractStatusEnum::cases(), 'value');
        $renewalTypes = array_column(ContractRenewalTypesEnum::cases(), 'value');
        $contractDurations = array_column(ContractDurationEnum::cases(), 'value');
        $noticePeriods = array_column(NoticePeriodEnum::cases(), 'value');

        return Inertia::render('tenants/locations/create', ['levelTypes' => $levelTypes, 'locationTypes' => $locationTypes, 'routeName' => 'rooms', 'documentTypes' => $documentTypes, 'frequencies' => $frequencies, 'floorMaterials' => $floorMaterials, 'wallMaterials' => $wallMaterials, 'statuses' => $statuses, 'renewalTypes' => $renewalTypes, 'contractDurations' => $contractDurations, 'noticePeriods' => $noticePeriods]);
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
