<?php

namespace App\Http\Controllers\Tenants;

use Inertia\Inertia;
use App\Models\LocationType;
use App\Models\Tenants\Room;
use Illuminate\Http\Request;
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
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;

class TenantRoomController extends Controller
{
    public function __construct(
        protected QRCodeService $qrCodeService,
        protected MaintainableService $maintainableService,
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        if (Auth::user()->cannot('viewAny', Room::class))
            abort(403);

        $validator = Validator::make($request->all(), [
            'q' => 'string|max:255|nullable',
            'category' => 'integer|nullable|gt:0',
            'sortBy' => 'in:asc,desc',
            'orderBy' => 'string|nullable',
        ]);

        $validatedFields = $validator->validated();

        $locations = Room::with('floor');

        if (isset($validatedFields['category'])) {
            $locations->where('location_type_id', $validatedFields['category']);
        };

        if (isset($validatedFields['q'])) {
            $locations->whereHas('maintainable', function (Builder $query) use ($validatedFields) {
                $query->where('name', 'like', '%' . $validatedFields['q'] . '%');
            });
        }

        $categories = LocationType::where('level', 'room')->get();


        return Inertia::render('tenants/locations/IndexLocations', ['items' => $locations->paginate()->withQueryString(), 'categories' => $categories, 'filters' =>  $validator->safe()->only(['q', 'sortBy',  'orderBy', 'category']), 'routeName' => 'rooms']);
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

        return Inertia::render('tenants/locations/CreateUpdateLocation', ['levelTypes' => $levelTypes, 'locationTypes' => $locationTypes, 'routeName' => 'rooms', 'documentTypes' => $documentTypes, 'frequencies' => $frequencies, 'floorMaterials' => $floorMaterials, 'wallMaterials' => $wallMaterials, 'statuses' => $statuses, 'renewalTypes' => $renewalTypes, 'contractDurations' => $contractDurations, 'noticePeriods' => $noticePeriods]);
    }


    /**
     * Display the specified resource.
     */
    public function show(Room $room)
    {

        if (Auth::user()->cannot('view', $room))
            abort(403);

        $room = Room::where('reference_code', $room->reference_code)->with(['floor', 'documents', 'tickets.pictures', 'maintainable.manager', 'maintainable.providers', 'contracts', 'contracts.provider'])->first();
        $room->append('level_path', 'floor_material', 'wall_material');

        return Inertia::render('tenants/locations/ShowLocation', ['routeName' => 'rooms', 'item' => $room]);
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

        return Inertia::render('tenants/locations/CreateUpdateLocation', ['location' => $room->makeVisible(['level_id', 'location_type_id'])->load('floor'), 'levelTypes' => $levelTypes, 'locationTypes' => $locationTypes, 'routeName' => 'rooms', 'documentTypes' => $documentTypes, 'frequencies' => $frequencies, 'floorMaterials' => $floorMaterials, 'wallMaterials' => $wallMaterials]);
    }
}
