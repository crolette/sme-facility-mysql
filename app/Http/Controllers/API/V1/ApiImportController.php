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
use App\Jobs\ImportExcelUsersJob;
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
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Tenant\ProviderRequest;
use App\Http\Requests\Tenant\ImportFileRequest;
use App\Jobs\ImportExcelContractsJob;
use App\Models\Tenants\Contract;
use App\Models\Tenants\User;

class ApiImportController extends Controller
{

    public function store(ImportFileRequest $request)
    {
        $validated = $request->validated();

        $neededFileName = ['contracts', 'contacts', 'assets', 'providers'];
        $hasNeededFileName = false;
        $uploadedFileName = null;

        foreach ($neededFileName as $fileName) {
            if (str_contains($validated['file']->getClientOriginalName(), $fileName)) {
                $hasNeededFileName = true;
                $uploadedFileName = $fileName;
            }
        }

        if ($hasNeededFileName === false) {
            return ApiResponse::error('Wrong file.');
        }

        if (!Gate::allows('import-excel')) {
            ApiResponse::notAuthorized();
            return redirect()->back();
        }

        $class = match ($uploadedFileName) {
            'contracts' => Contract::class,
            'assets' => Asset::class,
            'providers' => Provider::class,
            'contacts' => User::class,
        };

        if (Auth::user()->cannot('create', $class)) {
            ApiResponse::notAuthorized();
            return redirect()->back();
        }


        try {

            $directory = tenancy()->tenant->id . "/imports/";
            $fileName = Carbon::now()->isoFormat('YYYYMMDDhhmm') . '_' . $validated['file']->getClientOriginalName();
            Storage::disk('tenants')->putFileAs($directory, $validated['file'], $fileName);

            Log::info('DISPATCH IMPORT EXCEL JOB : ' . $directory . $fileName);

            match ($uploadedFileName) {
                'contracts' => ImportExcelContractsJob::dispatch(Auth::user(), $directory . $fileName),
                'assets' => ImportExcelAssetsJob::dispatch(Auth::user(), $directory . $fileName),
                'providers' => ImportExcelProvidersJob::dispatch(Auth::user(), $directory . $fileName),
                'contacts' => ImportExcelUsersJob::dispatch(Auth::user(), $directory . $fileName),
            };


            return ApiResponse::success('', $uploadedFileName . ' will be imported, you will receive an email when it\'s done.');
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();

            foreach ($failures as $failure) {
                $failure->row(); // row that went wrong
                $failure->attribute(); // either heading key (if using heading row concern) or column index
                $failure->errors(); // Actual error messages from Laravel validator
                $failure->values(); // The values of the row that has failed.
            }
            Log::info($failures);

            return ApiResponse::error('Error during ' . $uploadedFileName . ' import');
        }
    }
};
