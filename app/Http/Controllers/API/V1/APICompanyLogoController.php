<?php

namespace App\Http\Controllers\API\V1;

use Exception;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Models\Tenants\Company;
use App\Http\Controllers\Controller;
use App\Services\CompanyLogoService;
use Barryvdh\Debugbar\Facades\Debugbar;
use App\Http\Requests\Tenant\CompanyLogoRequest;

class APICompanyLogoController extends Controller
{
    public function __construct(
        protected CompanyLogoService $logoService
    ) {}

    public function store(CompanyLogoRequest $request)
    {
        $company = Company::first();
        $this->logoService->uploadAndAttachLogo($company, $request->validated('image'));

        session(['tenantLogo' => $company->logo]);

        return ApiResponse::success('', 'Logo uploaded');
    }

    public function destroy(Request $request)
    {
        try {
            $company = Company::first();
    
            $this->logoService->deleteExistingFiles($company);
            return ApiResponse::success('', 'Logo deleted');

        } catch(Exception $e) {
            Log::info('Error while deleting logo : ' . $e->getMessage());
            return ApiResponse::error('Error while deleting logo');
        }

    }
};
