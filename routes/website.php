<?php

use Inertia\Inertia;
use App\Mail\ContactMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Barryvdh\Debugbar\Facades\Debugbar;
use App\Http\Middleware\LocaleMiddleware;
use App\Http\Middleware\AuthenticateCentral;
use App\Http\Controllers\Website\ContactController;
use App\Http\Requests\Central\ContactRequest;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

foreach (config('tenancy.central_domains') as $domain) {
    Route::domain($domain)->group(function () {
        Route::get('/mail', function () {
            $request = [
                'subject' => 'appointment',
                'first_name' => 'Test',
                'last_name' => 'SME',
                'vat_number' => 'BE0123456789',
                'phone_number' => '+32123456789',
                'company' => 'SME Facility',
                'email' => 'contact@sme-facility.com',
                'message' => 'Lorem ipsum dolor sit, amet consectetur adipisicing elit. Nihil corporis doloremque aperiam! Quaerat accusamus commodi alias non itaque a magni?'
            ];

            // $request = new ContactRequest($request);
            return (new ContactMail($request))->render();
        });

        Route::get('locale/{locale}', function (Request $request, $locale) {

            if (in_array($locale, array_keys(config('laravellocalization.supportedLocales')))) {
                Session::put('locale', $locale);
                App::setLocale($locale);
            }

            // Redirect back to the previous page
            return Inertia::location(route('home'));
        })->name('website.locale');

        Route::prefix(LaravelLocalization::setLocale())->middleware([
            'web',
            'localeSessionRedirect',
            'localizationRedirect'
        ])->group(function () {


            Route::get('/', function () {
                return Inertia::render('welcome');
            })->name('home');

            Route::get('/faq', function () {
                return Inertia::render('website/features/qr-code');
            })->name('website.faq');

            Route::get('/contact', [ContactController::class, 'index'])->name('website.contact');
            Route::post('/contact', [ContactController::class, 'store'])->name('website.contact.post');

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
