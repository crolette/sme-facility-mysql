<?php

namespace App\Http\Controllers\Tenants;

use Exception;
use Inertia\Inertia;
use App\Models\LocationType;
use Illuminate\Http\Request;
use App\Models\Tenants\Floor;
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
use App\Http\Requests\Tenant\TenantFloorRequest;
use App\Http\Requests\Tenant\MaintainableRequest;
use App\Http\Requests\Tenant\DocumentUploadRequest;

class TenantFloorController extends Controller
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
        if (Auth::user()->cannot('viewAny', Floor::class))
            abort(403);

        $floors = Floor::with('building')->get();
        return Inertia::render('tenants/locations/index', ['items' => $floors, 'routeName' => 'floors']);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (Auth::user()->cannot('create', Floor::class))
            abort(403);

        $levelTypes = Building::all();
        $locationTypes = LocationType::where('level', 'floor')->get();
        $documentTypes = CategoryType::where('category', 'document')->get();
        $frequencies = array_column(MaintenanceFrequency::cases(), 'value');
        $floorMaterials = CategoryType::where('category', 'floor_materials')->get();
        $wallMaterials = CategoryType::where('category', 'wall_materials')->get();
        $statuses = array_column(ContractStatusEnum::cases(), 'value');
        $renewalTypes = array_column(ContractRenewalTypesEnum::cases(), 'value');
        $contractDurations = array_column(ContractDurationEnum::cases(), 'value');
        $noticePeriods = array_column(NoticePeriodEnum::cases(), 'value');

        return Inertia::render('tenants/locations/create', ['levelTypes' => $levelTypes, 'locationTypes' => $locationTypes, 'routeName' => 'floors', 'documentTypes' => $documentTypes, 'frequencies' => $frequencies, 'floorMaterials' => $floorMaterials, 'wallMaterials' => $wallMaterials, 'statuses' => $statuses, 'renewalTypes' => $renewalTypes, 'contractDurations' => $contractDurations, 'noticePeriods' => $noticePeriods]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Floor $floor)
    {

        if (Auth::user()->cannot('view', $floor))
            abort(403);

        return Inertia::render('tenants/locations/show', ['routeName' => 'floors', 'item' => $floor->load(['locationType', 'documents', 'maintainable.manager', 'maintainable.providers'])]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Floor $floor)
    {
        if (Auth::user()->cannot('update', $floor))
            abort(403);


        $levelTypes = Building::all();
        $locationTypes = LocationType::where('level', 'floor')->get();
        $documentTypes = CategoryType::where('category', 'document')->get();
        $frequencies = array_column(MaintenanceFrequency::cases(), 'value');
        $floorMaterials = CategoryType::where('category', 'floor_materials')->get();
        $wallMaterials = CategoryType::where('category', 'wall_materials')->get();
        return Inertia::render('tenants/locations/create', ['location' => $floor->load('building'), 'levelTypes' => $levelTypes, 'locationTypes' => $locationTypes, 'routeName' => 'floors', 'documentTypes' => $documentTypes, 'frequencies' => $frequencies, 'floorMaterials' => $floorMaterials, 'wallMaterials' => $wallMaterials]);
    }
}
