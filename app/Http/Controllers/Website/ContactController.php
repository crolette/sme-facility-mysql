<?php

namespace App\Http\Controllers\Website;

use Inertia\Inertia;
use App\Mail\ContactMail;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Enums\ContactReasons;
use App\Mail\ContactCopyMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\Central\ContactRequest;

class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        return Inertia::render('website/contact', ['reasons' => array_column(ContactReasons::cases(), 'value'), 'recaptchaSiteKey' => config('captcha.sitekey')]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ContactRequest $request)
    {
        // dd($request->user()->preferred_locale);
        try {

            DB::beginTransaction();
            DB::table('newsletter')->updateOrInsert(
                ['email' => $request->validated('email')],
                [
                    'email' => $request->validated('email'),
                    'consent' => $request->validated('consent'),
                    'updated_at' => now()
                ]
            );

            DB::commit();

            if (env('APP_ENV') === "local" || env('APP_ENV') === "testing") {
                Mail::to('crolweb@gmail.com')
                    ->locale(App::getLocale())
                    ->send(new ContactMail($request->validated()));
            } else {
                Mail::to(['contact@sme-facility.com', 'info@facilitywebxp.be'])
                    ->locale(App::getLocale())
                    ->send(new ContactMail($request->validated()));
            }

            return ApiResponse::success([], 'E-mail sent');
        } catch (Exception $e) {
            Log::info('Error during insert email to newsletters', [$e->getMessage()]);
            DB::rollback();
            return ApiResponse::error('Error E-mail sent');
        }

        return redirect()->back()->withInput();
    }
}
