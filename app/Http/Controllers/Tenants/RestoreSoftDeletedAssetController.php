<?php

namespace App\Http\Controllers\Tenants;

use Illuminate\Http\Request;
use App\Models\Tenants\Asset;
use App\Http\Controllers\Controller;

class RestoreSoftDeletedAssetController extends Controller
{
    // receive the ID of the asset to restore
    public function restore(string $assetId)
    {
        $asset = Asset::onlyTrashed()->findOrFail($assetId);
        $asset->restore();
        return redirect()->route('tenant.assets.index');
    }
}
