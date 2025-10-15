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

        Debugbar::info($request->query('trashed'));
        if ($request->query('trashed')) {
            Debugbar::info('TRASHET');
            $assets = Asset::onlyTrashed();
        } else {
            Debugbar::info('NOT TRASHET');
            $assets = Asset::withoutTrashed();
        }

        if ($request->query('category')) {
            $assets->where('category_type_id', $request->query('category'));
        };

        if ($request->query('q')) {
            $assets->whereHas('maintainable', function (Builder $query) use ($request) {
                $query->where('name', 'like', '%' . $request->query('q') . '%');
            });
        }


        $assets = $assets->paginate()->withQueryString();
        $categories = CategoryType::where('category', 'asset')->get();

        return Inertia::render('tenants/assets/IndexAssets', ['items' => $assets, 'filters' => $request->only(['q', 'sortBy', 'trashed', 'orderBy', 'category']), 'categories' => $categories]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (Auth::user()->cannot('create', Asset::class))
            abort(403);

        $categories = CategoryType::where('category', 'asset')->get();
        $documentTypes = CategoryType::where('category', 'document')->get();
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

        $action = InterventionAction::first();

        $asset = Asset::where('reference_code', $asset->reference_code)->with(['maintainable.manager:id,first_name,last_name', 'contracts:id,name,type,provider_id,status,renewal_type,end_date,internal_reference,provider_reference', 'contracts.provider:id,name,logo', 'maintainable.providers:id,name'])->first();
        // dd($asset);
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


        $categories = CategoryType::where('category', 'asset')->get();
        $documentTypes = CategoryType::where('category', 'document')->get();
        $frequencies = array_column(MaintenanceFrequency::cases(), 'value');
        $statuses = array_column(ContractStatusEnum::cases(), 'value');
        $renewalTypes = array_column(ContractRenewalTypesEnum::cases(), 'value');
        return Inertia::render('tenants/assets/CreateUpdateAsset', ['asset' => $asset->load(['assetCategory', 'documents', 'maintainable.manager', 'maintainable.providers:id,name,category_type_id', 'contracts']), 'categories' => $categories, 'documentTypes' => $documentTypes, 'frequencies' => $frequencies, 'statuses' => $statuses, 'renewalTypes' => $renewalTypes]);
    }
}
