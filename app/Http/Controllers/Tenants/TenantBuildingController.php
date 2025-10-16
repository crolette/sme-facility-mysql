<?php

namespace App\Http\Controllers\Tenants;

use Exception;
use Inertia\Inertia;
use App\Models\LocationType;
use App\Models\Tenants\Site;
use Illuminate\Http\Request;
use App\Enums\NoticePeriodEnum;
use App\Services\QRCodeService;
use App\Models\Tenants\Building;
use App\Enums\ContractStatusEnum;
use App\Services\DocumentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\MessageBag;
use App\Enums\ContractDurationEnum;
use App\Enums\MaintenanceFrequency;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\Central\CategoryType;
use Illuminate\Support\Facades\Auth;
use App\Services\MaintainableService;
use App\Enums\ContractRenewalTypesEnum;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;
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
    public function index(Request $request)
    {
        if (Auth::user()->cannot('viewAny', Building::class))
            abort(403);

        $validator = Validator::make($request->all(), [
            'q' => 'string|max:255|nullable',
            'category' => 'integer|nullable|gt:0',
            'sortBy' => 'in:asc,desc',
            'orderBy' => 'string|nullable',
        ]);

        $validatedFields = $validator->validated();

        $locations = Building::query();

        if (isset($validatedFields['category'])) {
            $locations->where('location_type_id', $validatedFields['category']);
        };

        if (isset($validatedFields['q'])) {
            $locations->whereHas('maintainable', function (Builder $query) use ($validatedFields) {
                $query->where('name', 'like', '%' . $validatedFields['q'] . '%');
            });
        }

        $categories = LocationType::where('level', 'building')->get();

        return Inertia::render('tenants/locations/IndexLocations', ['items' => $locations->paginate()->withQueryString(), 'categories' => $categories, 'filters' =>  $validator->safe()->only(['q', 'sortBy',  'orderBy', 'category']), 'routeName' => 'buildings']);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (Auth::user()->cannot('create', Building::class))
            abort(403);

        $levelTypes = Site::all();
        $locationTypes = LocationType::where('level', 'building')->get();
        $documentTypes = CategoryType::where('category', 'document')->get();
        $frequencies = array_column(MaintenanceFrequency::cases(), 'value');
        $floorMaterials = CategoryType::where('category', 'floor_materials')->get();
        $wallMaterials = CategoryType::where('category', 'wall_materials')->get();
        $outdoorMaterials = CategoryType::where('category', 'outdoor_materials')->get();
        $statuses = array_column(ContractStatusEnum::cases(), 'value');
        $renewalTypes = array_column(ContractRenewalTypesEnum::cases(), 'value');
        $contractDurations = array_column(ContractDurationEnum::cases(), 'value');
        $noticePeriods = array_column(NoticePeriodEnum::cases(), 'value');

        return Inertia::render('tenants/locations/CreateUpdateLocation', ['levelTypes' => $levelTypes, 'locationTypes' => $locationTypes, 'routeName' => 'buildings', 'documentTypes' => $documentTypes, 'frequencies' => $frequencies, 'floorMaterials' => $floorMaterials, 'wallMaterials' => $wallMaterials, 'outdoorMaterials' => $outdoorMaterials, 'statuses' => $statuses, 'renewalTypes' => $renewalTypes, 'contractDurations' => $contractDurations, 'noticePeriods' => $noticePeriods]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Building $building)
    {
        if (Auth::user()->cannot('view', $building))
            abort(403);

        $building = Building::where('reference_code', $building->reference_code)->with(['site', 'documents', 'tickets.pictures', 'maintainable.manager', 'maintainable.providers', 'contracts', 'contracts.provider'])->first();
        $building->append('level_path', 'floor_material', 'wall_material', 'outdoor_material');


        return Inertia::render('tenants/locations/ShowLocation', ['routeName' => 'buildings', 'item' => $building]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Building $building)
    {
        if (Auth::user()->cannot('update', $building))
            abort(403);

        $levelTypes = Site::all();
        $locationTypes = LocationType::where('level', 'building')->get();
        $documentTypes = CategoryType::where('category', 'document')->get();
        $frequencies = array_column(MaintenanceFrequency::cases(), 'value');
        $floorMaterials = CategoryType::where('category', 'floor_materials')->get();
        $wallMaterials = CategoryType::where('category', 'wall_materials')->get();
        $outdoorMaterials = CategoryType::where('category', 'outdoor_materials')->get();
        return Inertia::render('tenants/locations/CreateUpdateLocation', ['location' => $building->load('site'), 'levelTypes' => $levelTypes, 'locationTypes' => $locationTypes, 'routeName' => 'buildings', 'documentTypes' => $documentTypes, 'frequencies' => $frequencies, 'floorMaterials' => $floorMaterials, 'wallMaterials' => $wallMaterials, 'outdoorMaterials' => $outdoorMaterials,]);
    }
}
