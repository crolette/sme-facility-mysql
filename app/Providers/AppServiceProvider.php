<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (tenant()) {
            $host = request()->getHost();

            // Désactiver le préfixe tenant pour les assets
            \URL::forceRootUrl("https://{$host}");
            config(['app.url' => "https://{$host}"]);
            config(['app.asset_url' => "https://{$host}"]);

            // Important : Override la fonction asset() pour les tenants
            app()->singleton('url', function ($app) use ($host) {
                $url = new \Illuminate\Routing\UrlGenerator(
                    $app['router']->getRoutes(),
                    $app['request']
                );
                $url->setRootUrl("https://{$host}");
                return $url;
            });
        }
    }
}
