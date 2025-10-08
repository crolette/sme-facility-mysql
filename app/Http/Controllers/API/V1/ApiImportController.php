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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Tenant\ProviderRequest;

class ApiImportController extends Controller
{
    public function __construct(
        protected LogoService $logoService
    ) {}

    public function store(Request $request)
    {
        if (Auth::user()->cannot('create', Asset::class))
            return ApiResponse::notAuthorized();

        try {
            
            Excel::import(new AssetsImport, $request->file);
            return ApiResponse::success('', 'Assets imported');

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();

            foreach ($failures as $failure) {
                
                $failure->row(); // row that went wrong
                $failure->attribute(); // either heading key (if using heading row concern) or column index
                $failure->errors(); // Actual error messages from Laravel validator
                $failure->values(); // The values of the row that has failed.
            }
            Log::info($failures);

            return ApiResponse::error('Error during import');
        }

      
    }
};
