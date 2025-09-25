<?php

namespace App\Http\Controllers\Central;

use Exception;
use Inertia\Inertia;
use App\Models\Tenant;
use App\Enums\AddressTypes;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\Central\CentralTenantRequest;
use App\Http\Requests\Central\CompanyAddressRequest;
use App\Http\Requests\Central\InvoiceAddressRequest;

class CentralTenantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tenants = Tenant::with('domain')->get();
        // dd($tenants);

        return Inertia::render('central/tenants/index', ['tenants' => $tenants]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Tenant $tenant)
    {
        //
        return Inertia::render('central/tenants/show', ['tenant' => $tenant->load('domain')]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tenant $tenant)
    {
        return Inertia::render('central/tenants/create', ['company' => $tenant->load('domain')]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Tenant $tenant, CentralTenantRequest $tenantRequest, CompanyAddressRequest $companyAddressRequest, InvoiceAddressRequest $invoiceAddressRequest,)
    {
        // dd($tenant);
        if ($tenant->domain->domain !== $tenantRequest->validated('domain_name')) {
            $errors = new MessageBag([
                'domain_name' => ['You cannot change the name of the domain'],
            ]);
            return ApiResponse::error($errors, $errors);
        }
        
        if ($tenant->company_code !== $tenantRequest->validated('company_code')) {
            $errors = new MessageBag([
                'company_code' => ['You cannot change the company code as it is used for your QR codes.'],
            ]);
            return ApiResponse::error($errors, $errors);
        }

        try {

            DB::beginTransaction();

            $tenant->update([...$tenantRequest->validated()]);

            $tenant->companyAddress()->update([...$companyAddressRequest->validated('company')]);

            if (!$invoiceAddressRequest->validated('same_address_as_company')) {
                if ($tenant->invoiceAddress) {
                    $tenant->invoiceAddress()->update([...$invoiceAddressRequest->validated('invoice')]);
                } else {
                    $tenant->addresses()->create([...$invoiceAddressRequest->validated('invoice'), 'address_type' => AddressTypes::INVOICE->value]);
                }
            }

            if ($invoiceAddressRequest->validated('same_address_as_company') && $tenant->invoiceAddress)
                $tenant->invoiceAddress()->delete();

            DB::commit();

            return ApiResponse::success([], 'Tenant updated');

        } catch (Exception $e) {
            DB::rollBack();
            Log::info('Error during tenant update : ' . $e->getMessage());
            return ApiResponse::error($e->getMessage());
        }

        return ApiResponse::error('Error during tenant update.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tenant $tenant)
    {
        if ($tenant) {
            $tenant->delete();
        }

        return redirect()->route('central.tenants.index');
    }
}
