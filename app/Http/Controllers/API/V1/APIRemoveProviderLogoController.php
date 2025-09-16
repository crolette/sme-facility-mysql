<?php

namespace App\Http\Controllers\API\V1;

use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Helpers\ApiResponse;
use App\Services\LogoService;
use App\Models\Tenants\Provider;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Tenant\ProviderRequest;

class APIRemoveProviderLogoController extends Controller
{
    public function __construct(
        protected LogoService $logoService
    ) {}

    public function destroy(Provider $provider)
    {
        if (Auth::user()->cannot('update', $provider))
            return ApiResponse::notAuthorized();
        
        $this->logoService->deleteExistingFiles($provider);
        return ApiResponse::success('', 'Logo deleted');
    }
};
