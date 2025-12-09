<?php

namespace App\Http\Middleware;

use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;

class CustomInitializeTenancyBySubdomain extends InitializeTenancyBySubdomain
{
    public function handle($request, $next)
    {
        // Vérifier si on est sur un domaine central
        if (in_array($request->getHost(), config('tenancy.central_domains'))) {
            return $next($request);
        }

        return parent::handle($request, $next);
    }
}
