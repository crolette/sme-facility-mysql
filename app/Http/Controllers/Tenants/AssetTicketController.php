<?php

namespace App\Http\Controllers\Tenants;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Tenants\Asset;

class AssetTicketController extends Controller
{
    public function create(Request $request, Asset $asset)
    {
        dd($request, $asset);
    }
}
