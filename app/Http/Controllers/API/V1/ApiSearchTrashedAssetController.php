<?php

namespace App\Http\Controllers\API\V1;

use App\Helpers\ApiResponse;
use App\Models\Tenants\Room;
use App\Models\Tenants\Site;
use Illuminate\Http\Request;
use App\Models\Tenants\Floor;
use App\Models\Tenants\Building;
use App\Http\Controllers\Controller;
use App\Models\Tenants\Asset;
use Barryvdh\Debugbar\Facades\Debugbar;

class ApiSearchTrashedAssetController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('q');

        if ($search) {
            $assets = Asset::onlyTrashed()->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(code) LIKE ?', ['%' . strtolower($search) . '%'])
                    ->orWhereRaw('LOWER(reference_code) LIKE ?', ['%' . strtolower($search) . '%']);
            })->paginate();
        } else {
            $assets = Asset::onlyTrashed()->paginate();
        }


        return ApiResponse::success($assets);
    }
}
