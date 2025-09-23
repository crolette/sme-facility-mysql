<?php

namespace App\Http\Controllers\Tenants;

use Exception;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Models\Tenants\Asset;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class RestoreSoftDeletedAssetController extends Controller
{
    // receive the ID of the asset to restore
    public function restore(Asset $asset)
    {
        if (Auth::user()->cannot('restore', $asset))
            abort(403);

        try {
            DB::beginTransaction();

            $referenceCode = $asset->location->reference_code . '-' . $asset->code;
            $asset->update([
                'reference_code' => $referenceCode
            ]);

            $asset->restore();

            DB::commit();
            return ApiResponse::success([],'Asset restored');
        } catch (Exception $e) {
            DB::rollBack();
            Log::info($e->getMessage());
            return ApiResponse::error('Cannot restore : ' . $e->getMessage());
        }

        return ApiResponse::error('Error while restoring');
    }
}
