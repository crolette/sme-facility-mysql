<?php

namespace App\Http\Controllers\Tenants;

use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Tenants\Asset;
use App\Services\AssetService;
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
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Support\Facades\Validator;
use App\Models\Tenants\InterventionAction;
use Illuminate\Contracts\Database\Eloquent\Builder;

class TenantAssetController extends Controller
{

    public function __construct(
        protected QRCodeService $qrCodeService,
        protected AssetService $assetService,
        protected MaintainableService $maintainableService,

    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (Auth::user()->cannot('viewAny', Asset::class))
            abort(403);

        $validator = Validator::make($request->all(), [
            'q' => 'string|max:255|nullable',
            'category' => 'integer|nullable|gt:0',
            'sortBy' => 'in:asc,desc',
            'orderBy' => 'string|nullable',
            'trashed' => 'nullable|in:1,0'
        ]);

        $validatedFields = $validator->validated();

        if (isset($validatedFields['trashed']) && $validatedFields['trashed'] === '1') {
            $assets = Asset::onlyTrashed();
        } else {
            $assets = Asset::withoutTrashed();
        }

        if (Auth::user()->hasRole('Maintenance Manager')) {
            $assets->whereHas('maintainable', function (Builder $query) {
                $query->where('maintenance_manager_id', Auth::user()->id);
            });
        }

        if (isset($validatedFields['category'])) {
            $assets->where('category_type_id', $validatedFields['category']);
        };

        if (isset($validatedFields['q'])) {
            $assets->where('code', 'like', '%' . $validatedFields['q'] . '%')->orWhereHas('maintainable', function (Builder $query) use ($validatedFields) {
                $query->where('name', 'like', '%' . $validatedFields['q'] . '%');
            });
        }


        $categories = CategoryType::getByCategoryCache('asset');

        return Inertia::render('tenants/assets/IndexAssets', ['items' => $assets->paginate()->withQueryString(), 'filters' =>  $validator->safe()->only(['q', 'sortBy', 'trashed', 'orderBy', 'category']), 'categories' => $categories]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (Auth::user()->cannot('create', Asset::class))
            abort(403);

        $categories = CategoryType::getByCategoryCache('asset');
        $documentTypes = CategoryType::getByCategoryCache('document');
        $frequencies = array_column(MaintenanceFrequency::cases(), 'value');
        $statuses = array_column(ContractStatusEnum::cases(), 'value');
        $renewalTypes = array_column(ContractRenewalTypesEnum::cases(), 'value');
        $contractDurations = array_column(ContractDurationEnum::cases(), 'value');
        $noticePeriods = array_column(NoticePeriodEnum::cases(), 'value');

        return Inertia::render('tenants/assets/CreateUpdateAsset', ['categories' => $categories, 'documentTypes' => $documentTypes, 'frequencies' => $frequencies, 'statuses' => $statuses, 'renewalTypes' => $renewalTypes, 'contractDurations' => $contractDurations, 'noticePeriods' => $noticePeriods]);
    }


    /**
     * Display the specified resource.
     */
    public function show(Asset $asset)
    {
        if (Auth::user()->cannot('view', $asset))
            abort(403);

        $asset = Asset::where('reference_code', $asset->reference_code)->with(['maintainable.manager:id,first_name,last_name', 'contracts:id,name,type,provider_id,status,renewal_type,end_date,internal_reference,provider_reference', 'contracts.provider:id,name,logo', 'maintainable.providers:id,name'])->first();


        return Inertia::render('tenants/assets/ShowAsset', ['item' => $asset->append('level_path')]);
    }

    public function showDeleted($id)
    {
        $asset = Asset::withTrashed()->with(['documents', 'pictures', 'tickets.pictures'])->findOrFail($id);

        return Inertia::render('tenants/assets/ShowAsset', ['item' => $asset]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Asset $asset)
    {
        if (Auth::user()->cannot('update', $asset))
            abort(403);


        $categories = CategoryType::getByCategoryCache('asset');
        $documentTypes = CategoryType::getByCategoryCache('document');
        $frequencies = array_column(MaintenanceFrequency::cases(), 'value');
        $statuses = array_column(ContractStatusEnum::cases(), 'value');
        $renewalTypes = array_column(ContractRenewalTypesEnum::cases(), 'value');
        return Inertia::render('tenants/assets/CreateUpdateAsset', ['asset' => $asset->load(['assetCategory', 'location', 'documents', 'maintainable.manager', 'maintainable.providers:id,name,category_type_id', 'contracts']), 'categories' => $categories, 'documentTypes' => $documentTypes, 'frequencies' => $frequencies, 'statuses' => $statuses, 'renewalTypes' => $renewalTypes]);
    }
}
