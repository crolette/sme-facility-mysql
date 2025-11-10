<?php

namespace App\Http\Controllers\API\V1;

use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Helpers\ApiResponse;
use App\Models\Tenants\User;
use App\Services\LogoService;
use App\Models\Tenants\Provider;
use App\Services\ProviderService;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Tenant\ProviderRequest;
use App\Http\Requests\Tenant\PictureUploadRequest;
use App\Http\Requests\Tenant\ProviderContactPersonsRequest;

class APIProviderController extends Controller
{
    public function __construct(
        protected LogoService $logoService,
        protected ProviderService $providerService,
    ) {}

    public function show(Provider $provider)
    {
        return ApiResponse::success($provider);
    }


    public function store(ProviderRequest $request, PictureUploadRequest $pictureRequest, ProviderContactPersonsRequest $contactPersonsRequest)
    {
        if (Auth::user()->cannot('create', Provider::class))
            return ApiResponse::notAuthorized();

        try {

            DB::beginTransaction();

            $provider = $this->providerService->create($request->validated());

            if ($contactPersonsRequest->validated('users')) {
                foreach ($contactPersonsRequest->validated('users') as $user) {
                    $user = User::create([...$user]);
                    $user->provider()->associate($provider)->save();
                }
            }

            if ($pictureRequest->validated('pictures')) {
                $provider = $this->logoService->uploadAndAttachLogo($provider, $pictureRequest->validated('pictures'), $request->validated('name'));
                $provider->save();
            }

            DB::commit();

            return ApiResponse::successFlash('', 'Provider created');
        } catch (Exception $e) {
            DB::rollback();
            return ApiResponse::error($e->getMessage());
        }

        return ApiResponse::error();
    }

    public function update(ProviderRequest $request, Provider $provider, PictureUploadRequest $pictureRequest)
    {
        if (Auth::user()->cannot('update', $provider))
            return ApiResponse::notAuthorized();

        try {

            DB::beginTransaction();

            $this->providerService->update($provider, $request->validated());

            if ($pictureRequest->validated('pictures')) {
                $provider = $this->logoService->uploadAndAttachLogo($provider, $pictureRequest->validated('pictures'), $request->validated('name'));
                $provider->save();
            }

            DB::commit();

            return ApiResponse::successFlash('', 'Provider updated');
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

        $response = $this->providerService->delete($provider);

        return $response === true ? ApiResponse::success('', 'Provider deleted') : ApiResponse::error('', 'Error during Provider deletion');
    }
};
