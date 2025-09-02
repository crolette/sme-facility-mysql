<?php

namespace App\Http\Controllers\API\V1;

use Exception;
use App\Helpers\ApiResponse;
use App\Models\Tenants\Asset;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Barryvdh\Debugbar\Facades\Debugbar;
use App\Http\Requests\Tenant\UserNotificationPreferenceRequest;
use App\Models\Tenants\UserNotificationPreference;
use App\Services\UserNotificationPreferenceService;

class APIUserNotificationPreferencesController extends Controller
{
    public function __construct(
        protected UserNotificationPreferenceService $preferenceService

    ) {}

    public function store(UserNotificationPreferenceRequest $request)
    {

        if (Auth::user()->cannot('updateOwn', Auth::user()))
            abort(403);

        try {
            // DB::beginTransaction();

            $this->preferenceService->create(Auth::user(), $request->validated());

            // DB::commit();

            return ApiResponse::success([], 'Preference created');
        } catch (Exception $e) {
            DB::rollback();
            Log::error($e->getMessage());
            return ApiResponse::error('', 'ERROR : ' . $e->getMessage());
            return redirect()->back()->with(['message' => 'ERROR : ' . $e->getMessage(), 'type' => 'error']);
        }
        return ApiResponse::error('', 'Error while creating an asset');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserNotificationPreferenceRequest $request, UserNotificationPreference $preference)
    {
        if (Auth::user()->cannot('updateOwn', Auth::user()))
            abort(403);

        try {
            // DB::beginTransaction();

            $this->preferenceService->update($preference, $request->validated());

            // $contract = $this->contractService->update($contract, $request->validated());
            // Debugbar::info($contract);

            // DB::commit();

            return ApiResponse::success('', 'Preference updated');
        } catch (Exception $e) {
            DB::rollback();
            Log::error($e->getMessage());
            return ApiResponse::error('ERROR : ' . $e->getMessage());
        }
        return ApiResponse::error('Error while updating the preference');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UserNotificationPreference $preference)
    {
        if (Auth::user()->cannot('updateOwn', Auth::user()))
            abort(403);

        // $preference->delete();
        return ApiResponse::success('', 'preference deleted');
    }
}
