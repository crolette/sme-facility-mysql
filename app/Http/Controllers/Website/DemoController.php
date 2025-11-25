<?php

namespace App\Http\Controllers\Website;

use Inertia\Inertia;
use App\Mail\ContactMail;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Enums\ContactReasons;
use App\Mail\ContactCopyMail;
use App\Mail\ContactDemoMail;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\Central\DemoRequest;
use App\Http\Requests\Central\ContactRequest;

class DemoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        return Inertia::render('website/demo', ['recaptchaSiteKey' => config('captcha.sitekey')]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DemoRequest $request)
    {
        // dd($request->user()->preferred_locale);
        try {

            if (env('APP_ENV') === "local" || env('APP_ENV') === "testing") {
                Mail::to('crolweb@gmail.com')
                    ->locale(App::getLocale())
                    ->send(new ContactDemoMail($request->validated()));
            } else {
                Mail::to(['contact@sme-facility.com', 'info@facilitywebxp.be'])
                    ->locale(App::getLocale())
                    ->send(new ContactDemoMail($request->validated()));
            }

            return ApiResponse::success([], 'E-mail sent');
        } catch (Exception $e) {
            return ApiResponse::error('Error E-mail sent');
        }

        return redirect()->back()->withInput();
    }
}
