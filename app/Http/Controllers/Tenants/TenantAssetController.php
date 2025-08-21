<?php

namespace App\Http\Controllers\Tenants;

use Inertia\Inertia;
use App\Models\Tenants\Asset;
use App\Services\AssetService;
use App\Services\QRCodeService;
use App\Enums\MaintenanceFrequency;
use App\Http\Controllers\Controller;
use App\Models\Central\CategoryType;
use Illuminate\Support\Facades\Auth;
use App\Services\MaintainableService;

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
    public function index()
    {
        if (Auth::user()->cannot('viewAny', Asset::class))
            abort(403);

        $assets = Asset::orderBy('id')->get();
        return Inertia::render('tenants/assets/index', ['assets' => $assets]);
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
        return Inertia::render('tenants/assets/create', ['categories' => $categories, 'documentTypes' => $documentTypes, 'frequencies' => $frequencies]);
    }


    /**
     * Display the specified resource.
     */
    public function show(Asset $asset)
    {
        if (Auth::user()->cannot('view', $asset))
            abort(403);

        return Inertia::render('tenants/assets/show', ['item' => $asset->load('maintainable.manager:id,first_name,last_name', 'maintainable.providers:id,name')]);
    }

    public function showDeleted($id)
    {
        $asset = Asset::withTrashed()->findOrFail($id);
        return Inertia::render('tenants/assets/show', ['asset' => $asset->load('documents', 'pictures', 'tickets.pictures')]);
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
        return Inertia::render('tenants/assets/create', ['asset' => $asset->load(['assetCategory', 'documents', 'maintainable.manager', 'maintainable.providers:id,name,category_type_id']), 'categories' => $categories, 'documentTypes' => $documentTypes, 'frequencies' => $frequencies]);
    }
}
