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

        // 1. D'abord vérifier la session
        if (Session::has('locale')) {
            $locale = Session::get('locale');
            if (LaravelLocalization::checkLocaleInSupportedLocales($locale)) {
                App::setLocale($locale);
                return $next($request);
            }
        }

        // 2. Ensuite l'URL
        $urlLocale = LaravelLocalization::getCurrentLocale();
        if ($urlLocale && LaravelLocalization::checkLocaleInSupportedLocales($urlLocale)) {
            App::setLocale($urlLocale);
            Session::put('locale', $urlLocale);
            return $next($request);
        }

        // 3. Fallback sur la préférence navigateur
        $locale = request()->getPreferredLanguage(array_keys(config('laravellocalization.supportedLocales')));
        App::setLocale($locale);

        return $next($request);
    }
}
