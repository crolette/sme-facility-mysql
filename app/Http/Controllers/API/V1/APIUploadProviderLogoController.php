<?php

namespace App\Http\Controllers\API\V1;

use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Services\LogoService;
use App\Models\Tenants\Provider;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Tenant\ProviderRequest;
use App\Http\Requests\Tenant\ImageUploadRequest;

class APIUploadProviderLogoController extends Controller
{
    public function __construct(
        protected LogoService $logoService
    ) {}

    public function store(ImageUploadRequest $request, Provider $provider)
    {
        if (Auth::user()->cannot('update', $provider))
            return ApiResponse::notAuthorized();

        $provider = $this->logoService->uploadAndAttachLogo($provider, $request->validated('pictures'));
        $provider->save();
        return ApiResponse::success('', 'Logo uploaded');
    }
};
