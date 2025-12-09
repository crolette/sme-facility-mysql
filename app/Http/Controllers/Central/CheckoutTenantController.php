<?php

namespace App\Http\Controllers\Central;

use Inertia\Inertia;
use App\Models\Tenant;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CheckoutTenantController extends Controller
{

    public function create(Request $request)
    {
        $tenant = Tenant::where('vat_number', $request->query('vat_number'))->first();

        return $tenant->hasActiveSubscription ?  Inertia::location($tenant->domainAddress) : Inertia::render('website/checkout/choose-plan', ['tenant' => $tenant]);
    }

    public function store(Request $request)
    {
        $tenant = Tenant::where('vat_number', '=', $request->vat_number)->first();

        $checkout = $tenant->newSubscription($request->product, $request->plan)
            ->trialDays(1)
            ->allowPromotionCodes()
            ->collectTaxIds()
            ->checkout([
                'success_url' => route('checkout.confirmed'),
                'cancel_url' => route('checkout.cancelled'),
            ]);

        return Inertia::location($checkout->url);
    }
    public function confirmed(Request $request)
    {
        return Inertia::render('website/checkout/confirmed');
    }
    public function cancelled(Request $request)
    {
        return Inertia::render('website/checkout/cancelled');
    }
}
