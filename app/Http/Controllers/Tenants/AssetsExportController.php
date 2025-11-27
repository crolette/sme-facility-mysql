<?php

namespace App\Http\Controllers\Tenants;

use Carbon\Carbon;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Exports\AssetsExport;
use App\Models\Tenants\Asset;
use App\Jobs\ExportAssetsExcelJob;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\Central\CategoryType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Support\Facades\Validator;

class AssetsExportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Debugbar::info($request);
        if (!Gate::allows('export-excel')) {
            ApiResponse::notAuthorized();
            return redirect()->back();
        }

        $request = Validator::make($request->all(), [
            'ids' => 'nullable|array',
            'template' => 'nullable|boolean'
        ]);

        $validated = $request->validated();

        $validated['ids'] = isset($validated['ids']) ?  $validated['ids'] : [];
        $validated['template'] = isset($validated['template']) ?  $validated['template'] : false;


        Debugbar::info($request->validated(), $validated['ids'], $validated['template']);

        Log::info('DISPATCH EXPORT ASSETS EXCEL JOB');
        ExportAssetsExcelJob::dispatch(Auth::user(), $validated)->onQueue('default');;

        return ApiResponse::success([], 'Assets will be exported, you will receive an e-mail when it\'s done');
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
