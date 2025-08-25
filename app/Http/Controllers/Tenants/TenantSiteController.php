<?php

namespace App\Http\Controllers\Tenants;

use Exception;
use Inertia\Inertia;
use App\Services\QRService;
use App\Models\LocationType;
use App\Models\Tenants\Site;
use Illuminate\Http\Request;
use App\Enums\NoticePeriodEnum;
use App\Services\QRCodeService;
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
use App\Http\Requests\Tenant\TenantSiteRequest;
use App\Http\Requests\Tenant\MaintainableRequest;
use App\Http\Requests\Tenant\DocumentUploadRequest;

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
        if (Auth::user()->cannot('viewAny', Site::class))
            abort(403);

        $sites = Site::all();

        return Inertia::render('tenants/locations/index', ['items' => $sites, 'routeName' => 'sites']);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (Auth::user()->cannot('create', Site::class))
            abort(403);

        $locationTypes = LocationType::where('level', 'site')->get();
        $documentTypes = CategoryType::where('category', 'document')->get();
        $frequencies = array_column(MaintenanceFrequency::cases(), 'value');
        $floorMaterials = CategoryType::where('category', 'floor_materials')->get();
        $wallMaterials = CategoryType::where('category', 'wall_materials')->get();
        $statuses = array_column(ContractStatusEnum::cases(), 'value');
        $renewalTypes = array_column(ContractRenewalTypesEnum::cases(), 'value');
        $contractDurations = array_column(ContractDurationEnum::cases(), 'value');
        $noticePeriods = array_column(NoticePeriodEnum::cases(), 'value');

        return Inertia::render('tenants/locations/create', ['locationTypes' => $locationTypes, 'routeName' => 'sites', 'documentTypes' => $documentTypes, 'frequencies' => $frequencies, 'floorMaterials' => $floorMaterials, 'wallMaterials' => $wallMaterials, 'statuses' => $statuses, 'renewalTypes' => $renewalTypes, 'contractDurations' => $contractDurations, 'noticePeriods' => $noticePeriods]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Site $site)
    {
        if (Auth::user()->cannot('view', $site))
            abort(403);

        return Inertia::render('tenants/locations/show', ['routeName' => 'sites', 'item' => $site->load(['locationType', 'documents', 'maintainable.manager', 'maintainable.providers'])]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Site $site)
    {
        if (Auth::user()->cannot('update', $site))
            abort(403);

        $locationTypes = LocationType::where('level', 'site')->get();

        $documentTypes = CategoryType::where('category', 'document')->get();
        $frequencies = array_column(MaintenanceFrequency::cases(), 'value');
        $floorMaterials = CategoryType::where('category', 'floor_materials')->get();
        $wallMaterials = CategoryType::where('category', 'wall_materials')->get();

        return Inertia::render('tenants/locations/create', ['location' => $site, 'locationTypes' => $locationTypes, 'routeName' => 'sites', 'documentTypes' => $documentTypes, 'frequencies' => $frequencies, 'floorMaterials' => $floorMaterials, 'wallMaterials' => $wallMaterials]);
    }
}
