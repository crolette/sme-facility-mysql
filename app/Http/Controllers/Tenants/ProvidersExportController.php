<?php

namespace App\Http\Controllers\Tenants;

use Carbon\Carbon;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Exports\AssetsExport;
use App\Models\Tenants\Asset;
use App\Exports\ProvidersExport;
use App\Jobs\ExportAssetsExcelJob;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\Central\CategoryType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use App\Jobs\ExportProvidersExcelJob;
use Barryvdh\Debugbar\Facades\Debugbar;

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
