<?php

namespace App\Http\Controllers\API\V1;

use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Imports\AssetsImport;
use App\Models\Tenants\Asset;
use App\Services\LogoService;
use App\Models\Tenants\Provider;
use App\Jobs\ImportExcelAssetsJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use App\Jobs\ImportExcelProvidersJob;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Tenant\ProviderRequest;
use App\Http\Requests\Tenant\ImportFileRequest;

class ApiImportProvidersController extends Controller
{

    public function store(ImportFileRequest $request)
    {
        $validated = $request->validated();

        if (!str_contains($validated['file']->getClientOriginalName(), 'providers')) {
            Log::error('Wrong file. The file name should include providers');
            return ApiResponse::error('Wrong file. The file name should include providers');
        }

        if (!Gate::allows('import-excel')) {
            ApiResponse::notAuthorized();
            return redirect()->back();
        }

        if (Auth::user()->cannot('create', Provider::class)) {
            ApiResponse::notAuthorized();
            return redirect()->back();
        }

        try {

            $directory = tenancy()->tenant->id . "/imports/"; // e.g., "webxp/tickets/1/pictures"
            $fileName = Carbon::now()->isoFormat('YYYYMMDDhhmm') . '_' . $validated['file']->getClientOriginalName();
            Storage::disk('tenants')->putFileAs($directory, $validated['file'], $fileName);

            Log::info('DISPATCH IMPORT EXCEL JOB : ' . $directory . $fileName);
            ImportExcelProvidersJob::dispatch(Auth::user(), $directory . $fileName);

            return ApiResponse::success('', 'Providers will be imported, you will receive an email when it\'s done.');
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();

            foreach ($failures as $failure) {

                $failure->row(); // row that went wrong
                $failure->attribute(); // either heading key (if using heading row concern) or column index
                $failure->errors(); // Actual error messages from Laravel validator
                $failure->values(); // The values of the row that has failed.
            }
            Log::info($failures);

            return ApiResponse::error('Error during providers import');
        }
    }
};
