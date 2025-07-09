<?php

namespace App\Http\Controllers\Tenants;

use Illuminate\Http\Request;
use App\Models\Tenants\Asset;
use App\Http\Controllers\Controller;

class ForceDeleteAssetController extends Controller
{
    public function forceDelete(int $assetId)
    {
        $asset = Asset::onlyTrashed()->findOrFail($assetId);
        $asset->forceDelete();
        return redirect()->route('tenant.assets.index');
    }
}
