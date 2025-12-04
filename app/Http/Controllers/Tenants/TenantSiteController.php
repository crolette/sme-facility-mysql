<?php

namespace App\Http\Controllers\Tenants;

use Exception;
use Inertia\Inertia;
use App\Services\QRService;
use App\Helpers\ApiResponse;
use App\Models\LocationType;
use App\Models\Tenants\Site;
use Illuminate\Http\Request;
use App\Services\TenantLimits;
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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;
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
    public function index(Request $request)
    {
        if (Auth::user()->cannot('viewAny', Site::class)) {
            ApiResponse::notAuthorizedFlash();
            return back();
        }

        $validator = Validator::make($request->all(), [
            'q' => 'string|max:255|nullable',
            'category' => 'integer|nullable|gt:0',
            'sortBy' => 'in:asc,desc',
            'orderBy' => 'string|nullable',
        ]);

        $validatedFields = $validator->validated();

        $locations = Site::query()->forMaintenanceManager();

        if (isset($validatedFields['category'])) {
            $locations->where('location_type_id', $validatedFields['category']);
        };

        if (isset($validatedFields['q'])) {
            $locations->whereHas('maintainable', function (Builder $query) use ($validatedFields) {
                $query->where('name', 'like', '%' . $validatedFields['q'] . '%');
            });
        }

        $categories = LocationType::getByLevelCache('site');



        return Inertia::render('tenants/locations/IndexLocations', ['items' => $locations->paginate()->withQueryString(), 'categories' => $categories, 'filters' =>  $validator->safe()->only(['q', 'sortBy',  'orderBy', 'category']), 'routeName' => 'sites']);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (Auth::user()->cannot('create', Site::class)) {
            ApiResponse::notAuthorizedFlash();
            return back();
        }

        $locationTypes = LocationType::getByLevelCache('site');
        $documentTypes = CategoryType::getByCategoryCache('document');
        $frequencies = array_column(MaintenanceFrequency::cases(), 'value');
        $floorMaterials = CategoryType::getByCategoryCache('floor_materials');
        $wallMaterials = CategoryType::getByCategoryCache('wall_materials');
        $statuses = array_column(ContractStatusEnum::cases(), 'value');
        $renewalTypes = array_column(ContractRenewalTypesEnum::cases(), 'value');
        $contractDurations = array_column(ContractDurationEnum::cases(), 'value');
        $noticePeriods = array_column(NoticePeriodEnum::cases(), 'value');

        return Inertia::render('tenants/locations/CreateUpdateLocation', ['locationTypes' => $locationTypes, 'routeName' => 'sites', 'documentTypes' => $documentTypes, 'frequencies' => $frequencies, 'floorMaterials' => $floorMaterials, 'wallMaterials' => $wallMaterials, 'statuses' => $statuses, 'renewalTypes' => $renewalTypes, 'contractDurations' => $contractDurations, 'noticePeriods' => $noticePeriods]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Site $site)
    {
        if (Auth::user()->cannot('view', $site)) {
            ApiResponse::notAuthorizedFlash();
            return back();
        }

        // dd($site->assets, $site->buildings);

        $site = Site::where('reference_code', $site->reference_code)->with(['buildings', 'documents', 'tickets.pictures', 'maintainable.manager', 'maintainable.providers', 'contracts', 'contracts.provider'])->first();
        $site->append('floor_material', 'wall_material');


        return Inertia::render('tenants/locations/ShowLocation', ['routeName' => 'sites', 'item' => $site]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Site $site)
    {
        if (Auth::user()->cannot('update', $site)) {
            ApiResponse::notAuthorizedFlash();
            return back();
        }

        $locationTypes = LocationType::getByLevelCache('site');
        $documentTypes = CategoryType::getByCategoryCache('document');
        $frequencies = array_column(MaintenanceFrequency::cases(), 'value');
        $floorMaterials = CategoryType::getByCategoryCache('floor_materials');
        $wallMaterials = CategoryType::getByCategoryCache('wall_materials');

        return Inertia::render('tenants/locations/CreateUpdateLocation', ['location' => $site, 'locationTypes' => $locationTypes, 'routeName' => 'sites', 'documentTypes' => $documentTypes, 'frequencies' => $frequencies, 'floorMaterials' => $floorMaterials, 'wallMaterials' => $wallMaterials]);
    }
}
