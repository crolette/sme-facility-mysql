<?php

namespace App\Http\Controllers\Tenants;

use Carbon\Carbon;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Jobs\ExportProvidersExcelJob;
use Illuminate\Support\Facades\Validator;

class ProvidersExportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (!Gate::allows('export-excel')) {
            ApiResponse::notAuthorized();
            return redirect()->back();
        }

        $request = Validator::make($request->all(), [
            'ids' => 'nullable|array',
            'ids.*' => 'exists:providers,id',
            'template' => 'nullable|boolean'
        ]);

        $validated = $request->validated();

        $validated['ids'] = isset($validated['ids']) ?  $validated['ids'] : [];
        $validated['template'] = isset($validated['template']) ?  $validated['template'] : false;


        Log::info('DISPATCH EXPORT PROVIDERS EXCEL JOB');
        ExportProvidersExcelJob::dispatch(Auth::user(), $validated)->onQueue('default');

        return ApiResponse::success([], 'Providers will be exported, you will receive an e-mail when it\'s done');
    }
}
