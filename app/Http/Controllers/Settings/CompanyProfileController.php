<?php

namespace App\Http\Controllers\Settings;

use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use App\Models\Tenants\Company;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Http\Requests\Settings\ProfileUpdateRequest;

class CompanyProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function show(): Response
    {
        return Inertia::render('settings/company', [
            'company' => Company::first(),
        ]);
    }

}
