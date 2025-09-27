<?php

namespace App\Http\Controllers\Central;

use Exception;
use Inertia\Inertia;
use App\Models\Domain;
use App\Models\Tenant;
use App\Enums\AddressTypes;
use App\Helpers\ApiResponse;
use App\Models\Tenants\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Events\NewTenantCreatedEvent;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Password;
use App\Http\Requests\Central\CentralTenantRequest;
use App\Http\Requests\Central\CompanyAddressRequest;
use App\Http\Requests\Central\InvoiceAddressRequest;
use App\Notifications\TenantAdminCreatedPasswordResetNotification;

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
        try {
            DB::beginTransaction();

                $tenant = Tenant::create([...$tenantRequest->validated(), 'id' => $tenantRequest->validated('company_code')]);

                $tenant->domain()->create(['domain' => $tenantRequest->validated('domain_name')]);

                $tenant->addresses()->create([...$companyAddressRequest->validated('company')]);

                if (!$invoiceAddressRequest->validated('same_address_as_company'))
                    $tenant->addresses()->create([...$invoiceAddressRequest->validated('invoice'), 'address_type' => AddressTypes::INVOICE->value]);

            // FIXME this should be uncommented when on private server
            // $email = $tenantRequest->validated('email');
            // $tenant->run(function () use ($email, $tenant) {
            //     $admin = User::where('email', $email)->first();

            //     event(new NewTenantCreatedEvent($admin, $tenant));

            //     $token = Password::createToken($admin);
            //     $admin->notify(new TenantAdminCreatedPasswordResetNotification($token, $tenant));

            // });

            DB::commit();
            return ApiResponse::successFlash([], 'Tenant created');

        } catch(\Throwable $e) {
            Log::info('Error during tenant creation : ' . $e->getMessage());
            DB::rollBack();
            return ApiResponse::error($e->getMessage());
        }

    }
}
