<?php

namespace App\Http\Controllers\Tenants;

use Illuminate\Http\Request;
use App\Models\Tenants\Asset;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class ForceDeleteAssetController extends Controller
{
    public function forceDelete(int $assetId)
    {
        $asset = Asset::onlyTrashed()->findOrFail($assetId);
        $asset->forceDelete();




        $tenantId = tenancy()->tenant->id;
        $directory = "$tenantId/assets/$assetId/";

        Storage::disk('tenants')->deleteDirectory($directory);

        return redirect()->route('tenant.assets.index');
    }
}
