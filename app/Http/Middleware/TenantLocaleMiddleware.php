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

class TenantLocaleMiddleware
{
    public function handle($request, Closure $next)
    {

        if (Auth::user()?->preferred_locale) {
            App::setLocale(Auth::user()->preferred_locale);
        } else if (Session::has('locale')) {
            App::setLocale(Session::get('locale'));
        } else {
            App::setLocale('en');
        };


        return $next($request);
    }
}
