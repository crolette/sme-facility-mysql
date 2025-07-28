<?php

namespace App\Http\Controllers\Central;

use Inertia\Inertia;
use App\Models\Tenant;
use App\Enums\AddressTypes;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\Central\CentralTenantRequest;
use App\Http\Requests\Central\CompanyAddressRequest;
use App\Http\Requests\Central\InvoiceAddressRequest;
use Illuminate\Support\Facades\Session;

class RegisterCentralTenantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index() {}

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('central/tenants/create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CentralTenantRequest $tenantRequest, CompanyAddressRequest $companyAddressRequest, InvoiceAddressRequest $invoiceAddressRequest)
    {

        $email = $tenantRequest->validated('email');
        $first_name = $tenantRequest->validated('first_name');
        $last_name = $tenantRequest->validated('last_name');
        $password = $tenantRequest->validated('password');
        session(['email' => $email, 'first_name' => $first_name, 'last_name' => $last_name, 'password' => $password]);

        $tenant = Tenant::create([...$tenantRequest->validated(), 'id' => $tenantRequest->validated('company_code')]);

        $tenant->domain()->create(['domain' => $tenantRequest->validated('domain_name')]);

        $tenant->addresses()->create([...$companyAddressRequest->validated('company')]);

        if (!$invoiceAddressRequest->validated('same_address_as_company'))
            $tenant->addresses()->create([...$invoiceAddressRequest->validated('invoice'), 'address_type' => AddressTypes::INVOICE->value]);

        return redirect()->route('central.tenants.index');
    }
}
