<?php

namespace App\Http\Controllers\API\V1;

use Exception;
use App\Helpers\ApiResponse;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Contract;
use App\Services\ContractService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Tenant\ContractStoreRequest;
use App\Http\Requests\Tenant\ContractUpdateRequest;

class APIContractController extends Controller
{
    public function __construct(
        protected ContractService $contractService

    ) {}

    public function store(ContractStoreRequest $request)
    {
        if (Auth::user()->cannot('create', Asset::class))
            abort(403);

        try {
            DB::beginTransaction();


            DB::commit();

            return ApiResponse::success('', 'Asset created');
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
    public function update(ContractUpdateRequest $request, Contract $contract)
    {
        if (Auth::user()->cannot('create', Asset::class))
            abort(403);

        try {
            DB::beginTransaction();

            $this->contractService->update($contract, $request->validated());

            DB::commit();

            return ApiResponse::success('', 'Contract updated');
        } catch (Exception $e) {
            DB::rollback();
            Log::error($e->getMessage());
            return ApiResponse::error('ERROR : ' . $e->getMessage());
            return redirect()->back()->with(['message' => 'ERROR : ' . $e->getMessage(), 'type' => 'error']);
        }
        return ApiResponse::error('Error while updating the contract');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Asset $asset)
    {
        if (Auth::user()->cannot('delete', $asset))
            abort(403);

        $asset->delete();
        return ApiResponse::success('', 'Asset deleted');
    }
}
