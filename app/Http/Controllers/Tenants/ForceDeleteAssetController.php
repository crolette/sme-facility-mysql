<?php

namespace App\Http\Controllers\Tenants;

use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Models\Tenants\Asset;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ForceDeleteAssetController extends Controller
{
    public function forceDelete(Asset $asset)
    {
        if (Auth::user()->cannot('forceDelete', $asset))
            abort(403);

        $asset->forceDelete();

        $tenantId = tenancy()->tenant->id;
        $directory = "$tenantId/assets/$asset->id/";

        Storage::disk('tenants')->deleteDirectory($directory);

        return ApiResponse::success('', 'Asset deleted');
    }
}
