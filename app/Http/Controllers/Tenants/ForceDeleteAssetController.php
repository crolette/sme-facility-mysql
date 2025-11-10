<?php

namespace App\Http\Controllers\Tenants;

use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Models\Tenants\Asset;
use App\Http\Controllers\Controller;
use App\Services\AssetService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ForceDeleteAssetController extends Controller
{
    public function __construct(
        protected AssetService $assetService
    ) {}

    public function forceDelete(Asset $asset)
    {
        if (Auth::user()->cannot('forceDelete', $asset))
            abort(403);

        $response = $this->assetService->forceDeleteAsset($asset);

        return $response === true ? ApiResponse::success('', 'Asset deleted') : ApiResponse::error('', 'Error during asset deletion');
    }
}
