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
use Barryvdh\Debugbar\Facades\Debugbar;
use App\Http\Requests\Tenant\ContractStoreRequest;
use App\Http\Requests\Tenant\ContractUpdateRequest;

class APIContractController extends Controller
{
    public function __construct(
        protected ContractService $contractService
    ) {}

    public function store(ContractStoreRequest $request)
    {

        if (Auth::user()->cannot('create', Contract::class))
            return ApiResponse::notAuthorized();

        try {
            DB::beginTransaction();

            $contract = $this->contractService->create($request->validated());

            DB::commit();

            return ApiResponse::successFlash(['id' => $contract->id], 'Contract created');
        } catch (Exception $e) {
            DB::rollback();
            Log::error($e->getMessage());
            return ApiResponse::error('', 'ERROR : ' . $e->getMessage());
        }
        return ApiResponse::error('', 'Error while creating an asset');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ContractUpdateRequest $request, Contract $contract)
    {
        if (Auth::user()->cannot('update', $contract))
            return ApiResponse::notAuthorized();


        try {
            DB::beginTransaction();

            $contract = $this->contractService->update($contract, $request->validated());

            DB::commit();

            return ApiResponse::successFlash('', 'Contract updated');
        } catch (Exception $e) {
            DB::rollback();
            Log::error($e->getMessage());
            return ApiResponse::error('ERROR : ' . $e->getMessage());
        }
        return ApiResponse::error('Error while updating the contract');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contract $contract)
    {
        if (Auth::user()->cannot('delete', $contract))
            return ApiResponse::notAuthorized();

        $contract->delete();
        return ApiResponse::successFlash('', 'Contract deleted');
    }
}
