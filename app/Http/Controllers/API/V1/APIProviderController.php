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

class APIProviderController extends Controller
{
    public function __construct(
        protected LogoService $logoService
    ) {}

    public function show(Provider $provider)
    {
        return ApiResponse::success($provider);
    }


    public function store(ProviderRequest $request)
    {
        if (Auth::user()->cannot('create', Provider::class))
            return ApiResponse::notAuthorized();

        try {

            DB::beginTransaction();

            $provider = new Provider($request->safe()->except('logo'));
            $provider->providerCategory()->associate($request->validated('categoryId'));

            $provider->save();

            if ($request->validated('logo')) {
                $provider = $this->logoService->uploadAndAttachLogo($provider, $request->validated('logo'), $request->validated('name'));
                $provider->save();
            }

            DB::commit();

            return ApiResponse::success('', 'Provider created');
        } catch (Exception $e) {
            DB::rollback();
            return ApiResponse::error($e->getMessage());
        }

        return ApiResponse::error();
    }

    public function update(ProviderRequest $request, Provider $provider)
    {
        if (Auth::user()->cannot('update', $provider))
            return ApiResponse::notAuthorized();

        try {

            DB::beginTransaction();

            $provider->update($request->safe()->except('logo'));

            if (!$request->validated('logo') === null)
                $provider = $this->logoService->uploadAndAttachLogo($provider, $request->validated('logo'), $request->validated('name'));

            $categoryId = $request->validated('categoryId') ?? null;
            if ($categoryId && $categoryId !== $provider->providerCategory->id) {
                $provider->providerCategory()->dissociate();
                $provider->providerCategory()->associate($request->validated('categoryId'));
            }

            $provider->save();

            DB::commit();

            return ApiResponse::success('', 'Provider updated');
        } catch (Exception $e) {
            DB::rollback();
            return ApiResponse::error($e->getMessage());
        }

        return ApiResponse::error();
    }

    public function destroy(Provider $provider)
    {
        if (Auth::user()->cannot('delete', $provider))
            return ApiResponse::notAuthorized();
        
        $provider->delete();
        return ApiResponse::success('', 'Provider deleted');
    }
};
