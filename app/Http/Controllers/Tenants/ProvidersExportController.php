<?php

namespace App\Http\Controllers\Tenants;

use Carbon\Carbon;
use App\Helpers\ApiResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Jobs\ExportProvidersExcelJob;

class ProvidersExportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!Gate::allows('export-excel')) {
            ApiResponse::notAuthorized();
            return redirect()->back();
        }

        Log::info('DISPATCH EXPORT PROVIDERS EXCEL JOB');
        ExportProvidersExcelJob::dispatch(Auth::user())->onQueue('default');

        return ApiResponse::success([], 'Excel will be exported, you will receive an e-mail when it\'s done');
    }
}
