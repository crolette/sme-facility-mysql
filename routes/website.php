<?php

use Carbon\Carbon;
use Inertia\Inertia;
use App\Mail\ContactMail;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Mail\ContactDemoMail;
use App\Rules\NotDisposableEmail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Barryvdh\Debugbar\Facades\Debugbar;
use App\Http\Middleware\LocaleMiddleware;
use Illuminate\Support\Facades\Validator;
use App\Http\Middleware\AuthenticateCentral;
use App\Http\Requests\Central\ContactRequest;
use App\Http\Controllers\Website\DemoController;
use App\Http\Controllers\Website\ContactController;
use App\Http\Controllers\Central\CheckoutTenantController;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

foreach (config('tenancy.central_domains') as $domain) {
    Route::domain($domain)->group(function () {


        // Route::get('/mail', function () {
        //     $request = [
        //         'subject' => 'appointment',
        //         'first_name' => 'Test',
        //         'last_name' => 'SME',
        //         'vat_number' => 'BE0123456789',
        //         'phone_number' => '+32123456789',
        //         'company' => 'SME Facility',
        //         'email' => 'contact@sme-facility.com',
        //         'message' => 'Lorem ipsum dolor sit, amet consectetur adipisicing elit. Nihil corporis doloremque aperiam! Quaerat accusamus commodi alias non itaque a magni?'
        //     ];

        //     // $request = new ContactRequest($request);
        //     return (new ContactDemoMail($request))->render();
        // });

        // Route::get('subscriptions', function () {
        //     return Inertia::render('website/stripe');
        // });

        Route::get('locale/{locale}', function (Request $request, $locale) {

            $oldLocale = Session::get('locale');

            if (in_array($locale, array_keys(config('laravellocalization.supportedLocales')))) {
                Session::put('locale', $locale);
                App::setLocale($locale);
            }

            $newLocation = str_replace(`/` . $oldLocale, `/` . $locale, $request->header('Referer'));
            $newLocation = str_replace(['http://' . $request->header('Host'), 'https://' . $request->header('Host')], '', $newLocation);

            // Redirect back to the previous page
            return Inertia::location($newLocation);
        })->name('website.locale');

        Route::middleware(['signed'])->get('choose-plan', [CheckoutTenantController::class, 'create'])->name('choose-plan');

        Route::prefix(LaravelLocalization::setLocale())->middleware([
            'web',
            'localeSessionRedirect',
            'localizationRedirect'
        ])->group(function () {


            Route::post('choose-plan/', [CheckoutTenantController::class, 'store'])->name('checkout');
            Route::get('subscription/confirmed', [CheckoutTenantController::class, 'confirmed'])->name('checkout.confirmed');
            Route::get('subscription/cancelled', [CheckoutTenantController::class, 'cancelled'])->name('checkout.cancelled');

            Route::get('/', function () {
                return Inertia::render('welcome');
            })->name('website.home');

            Route::get('/faq', function () {
                return Inertia::render('website/faq');
            })->name('website.faq');

            Route::get('/careers', function () {
                return Inertia::render('website/careers');
            })->name('website.careers');

            Route::get('/cgu', function () {
                return Inertia::render('website/cgu');
            })->name('website.cgu');

            Route::get('/confidentiality', function () {
                return Inertia::render('website/confidentiality');
            })->name('website.confidentiality');

            Route::get('/legal', function () {
                return Inertia::render('website/legal');
            })->name('website.legal');

            Route::get('/cgv', function () {
                return Inertia::render('website/cgv');
            })->name('website.cgv');

            Route::get('/who-are-we', function () {
                return Inertia::render('website/who_are_we');
            })->name('website.who-are-we');

            // Route::middleware('throttle:2,60')->post('/newsletter', function (Request $request) {
            Route::middleware('throttle:10,60')->post('/newsletter', function (Request $request) {

                $data = $request->all();
                $data['email'] = strtolower($data['email']);

                $validated = Validator::make($data, [
                    'email' => ['required', 'string', 'lowercase', 'email', 'max:255', new NotDisposableEmail],
                    'consent' => 'required|accepted'
                ]);
                $validated = $validated->validated();

                try {
                    DB::beginTransaction();
                    DB::table('newsletter')->updateOrInsert(
                        ['email' => $validated['email']],
                        [
                            'email' => $validated['email'],
                            'consent' => $validated['consent'],
                            'created_at' => now()
                        ]
                    );

                    DB::commit();
                    return ApiResponse::success([], 'Check');
                } catch (Exception $e) {
                    Log::info('Error during insert email to newsletters', [$e->getMessage()]);
                    DB::rollback();
                    return ApiResponse::error('Error');
                }
            })->name('website.newsletter');

            Route::get('/contact', [ContactController::class, 'index'])->name('website.contact');
            Route::middleware('throttle:10,60')->post('/contact', [ContactController::class, 'store'])->name('website.contact.post');

            Route::get('/demo', [DemoController::class, 'index'])->name('website.demo');
            Route::middleware('throttle:10,60')->post('/demo', [DemoController::class, 'store'])->name('website.demo.post');

            Route::prefix('features')->group(function () {
                Route::get('/qr-code', function () {
                    return Inertia::render('website/features/qr-code');
                })->name('website.features.qrcode');

                Route::get('/maintenance', function () {
                    return Inertia::render('website/features/maintenance');
                })->name('website.features.maintenance');

                Route::get('/contracts', function () {
                    return Inertia::render('website/features/contracts');
                })->name('website.features.contracts');

                Route::get('/assets', function () {
                    return Inertia::render('website/features/assets');
                })->name('website.features.assets');

                Route::get('/documents', function () {
                    return Inertia::render('website/features/documents');
                })->name('website.features.documents');

                Route::get('/statistics', function () {
                    return Inertia::render('website/features/statistics');
                })->name('website.features.statistics');

                Route::get('/roles', function () {
                    return Inertia::render('website/features/roles');
                })->name('website.features.roles');
            });

            Route::prefix('who')->group(function () {
                Route::get('/facility-manager', function () {
                    return Inertia::render('website/who/facility-manager');
                })->name('website.who.facility-manager');
                Route::get('/maintenance-manager', function () {
                    return Inertia::render('website/who/maintenance-manager');
                })->name('website.who.maintenance-manager');
                Route::get('/sme', function () {
                    return Inertia::render('website/who/sme');
                })->name('website.who.sme');
            });

            Route::prefix('why')->group(function () {
                Route::get('/sme-facility', function () {
                    return Inertia::render('website/why-sme/why-sme');
                })->name('website.why');
            });

            Route::get('pricing', function () {
                return Inertia::render('website/pricing');
            })->name('website.pricing');
        });
    });
}
