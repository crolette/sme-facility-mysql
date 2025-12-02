<?php

namespace App\Http\Controllers\Central;

use Exception;
use Inertia\Inertia;
use App\Models\Tenant;
use App\Enums\AddressTypes;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Laravel\Cashier\Cashier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller;
use App\Models\Central\Subscription;
use App\Models\Central\CentralCountry;
use App\Http\Requests\Central\CentralTenantRequest;
use App\Http\Requests\Central\CompanyAddressRequest;
use App\Http\Requests\Central\InvoiceAddressRequest;
use App\Models\Central\SubscriptionItem;

class CentralTenantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tenants = Tenant::with('domain', 'subscriptions')->get();

        return Inertia::render('central/tenants/index', ['items' => $tenants]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Tenant $tenant)
    {
        $subscription = SubscriptionItem::first();

        $stripe = new \Stripe\StripeClient(config('cashier.secret'));
        $price = $stripe->prices->retrieve('price_1SZXnhFHXryfbBkbXL0omY5n', ['expand' => ['product']]);
        // dd($price->product->metadata);

        $url = URL::temporarySignedRoute(
            'choose-plan',
            now()->addDays(7),
            ['email' => $tenant->email, 'vat_number' => $tenant->vat_number]
        );

        return Inertia::render('central/tenants/show', ['tenant' => $tenant->load('domain'), 'url' => $url]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tenant $tenant)
    {
        $countries = CentralCountry::all();
        return Inertia::render('central/tenants/create', ['company' => $tenant->load('domain'), 'countries' => $countries]);
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


            // $stripeCustomer = $tenant->updateStripeCustomer([
            //     'business_name' => $tenant->company_name,
            //     'individual_name' => $tenant->first_name . ' ' . $tenant->last_name,
            //     'email' => $tenant->email,
            //     'phone' => $tenant->phone_number,
            //     'address' =>
            //     [
            //         'city' => $companyAddressRequest->validated('company')['city'],
            //         'line1' => $companyAddressRequest->validated('company')['street'] . ' ' . $companyAddressRequest->validated('company')['house_number'],
            //         'postal_code' => $companyAddressRequest->validated('company')['zip_code'],
            //         'country' => 'BE'
            //     ]
            // ]);

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
            try {
                $tenant->delete();
                return ApiResponse::success([], 'Tenant deleted');
            } catch (Exception $e) {
                Log::info('Error during tenant update : ' . $e->getMessage());
                return ApiResponse::error($e->getMessage());
            }
        }
        return ApiResponse::error('Error during tenant deletion.');
    }
}
