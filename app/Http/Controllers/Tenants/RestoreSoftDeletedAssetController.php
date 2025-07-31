<?php

namespace App\Http\Controllers\Tenants;

use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Models\Tenants\Asset;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Exception;

class RestoreSoftDeletedAssetController extends Controller
{
    // receive the ID of the asset to restore
    public function restore(string $assetId)
    {
        try {
            DB::beginTransaction();

            $asset = Asset::onlyTrashed()->findOrFail($assetId);

            $referenceCode = $asset->location->reference_code . '-' . $asset->code;
            $asset->update([
                'reference_code' => $referenceCode
            ]);

            $asset->restore();

            DB::commit();
            return redirect()->route('tenant.assets.index');
            return ApiResponse::success('Asset restored');
        } catch (Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Cannot restore');
        }
    }
}
