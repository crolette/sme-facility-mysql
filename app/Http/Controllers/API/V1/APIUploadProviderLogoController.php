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
use App\Http\Requests\Tenant\ImageUploadRequest;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Tenant\ProviderRequest;

class APIUploadProviderLogoController extends Controller
{
    public function __construct(
        protected LogoService $logoService
    ) {}

    public function store(ImageUploadRequest $request, Provider $provider)
    {
        Debugbar::info($request->validated('image'));
        $provider = $this->logoService->uploadAndAttachLogo($provider, $request->validated('image'));
        $provider->save();
        return ApiResponse::success('', 'Logo uploaded');
    }
};
