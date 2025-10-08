<?php

namespace App\Http\Controllers\Tenants;

use Illuminate\Http\Request;
use App\Exports\AssetsExport;
use App\Models\Tenants\Asset;
use App\Http\Controllers\Controller;
use App\Models\Central\CategoryType;
use Maatwebsite\Excel\Facades\Excel;

class AssetsExportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Excel::download(new AssetsExport(), 'assets.xlsx');
        // return new AssetsExport();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Asset $asset)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Asset $asset)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Asset $asset)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Asset $asset)
    {
        //
    }
}
