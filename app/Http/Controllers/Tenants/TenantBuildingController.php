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
use App\Enums\MaintenanceFrequency;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\Central\CategoryType;
use App\Services\MaintainableService;
use Barryvdh\Debugbar\Facades\Debugbar;
use App\Http\Requests\Tenant\MaintainableRequest;
use App\Http\Requests\Tenant\DocumentUploadRequest;
use App\Http\Requests\Tenant\TenantBuildingRequest;
use Illuminate\Support\Facades\Auth;

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
        if (Auth::user()->cannot('viewAny', Building::class))
            abort(403);

        $buildings = Building::all();
        return Inertia::render('tenants/locations/index', ['locations' => $buildings, 'routeName' => 'buildings']);
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
        return Inertia::render('tenants/locations/create', ['levelTypes' => $levelTypes, 'locationTypes' => $locationTypes, 'routeName' => 'buildings', 'documentTypes' => $documentTypes, 'frequencies' => $frequencies]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Building $building)
    {
        if (Auth::user()->cannot('view', $building))
            abort(403);

        return Inertia::render('tenants/locations/show', ['routeName' => 'buildings', 'item' => $building->load('site', 'documents', 'maintainable.manager', 'maintainable.providers')]);
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
        return Inertia::render('tenants/locations/create', ['location' => $building->load('site'), 'levelTypes' => $levelTypes, 'locationTypes' => $locationTypes, 'routeName' => 'buildings', 'documentTypes' => $documentTypes, 'frequencies' => $frequencies]);
    }
}
