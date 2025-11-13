<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class LocaleMiddleware
{
    public function handle($request, Closure $next)
    {
        $urlLocale = LaravelLocalization::getCurrentLocale();

        // dd($urlLocale);
        if ($urlLocale && LaravelLocalization::checkLocaleInSupportedLocales($urlLocale)) {
            App::setLocale($urlLocale);
            Session::put('locale', $urlLocale);

            if (Auth::check()) {
                Auth::user()->update(['preferred_locale' => $urlLocale]);
            }
        } else {
            // Fallback si pas de locale valide dans l'URL
            if (Auth::check() && Auth::user()->preferred_locale) {
                App::setLocale(Auth::user()->preferred_locale);
            } else if (Session::has('locale')) {
                App::setLocale(Session::get('locale'));
            } else {
                $locale = request()->getPreferredLanguage(array_keys(config('laravellocalization.supportedLocales')));
                App::setLocale($locale);
            }
        }

        return $next($request);
    }
}
