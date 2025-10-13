<?php

namespace App\Providers;

use App\Models\Tenants\User;
use App\Models\Tenants\Contract;
use Illuminate\Support\Facades\DB;
use App\Observers\ContractObserver;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

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
        // grant complete access to super admin
        Gate::before(function ($user, $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });

        Gate::define('import-excel', function (User $user) {
            return $user->can('import excel');
        });

        Gate::define('export-excel', function (User $user) {
            return $user->can('export excel');
        });

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

        if (env('APP_ENV') === "production") {
            Password::defaults(function () {
                return Password::min(12)
                    ->mixedCase()
                    ->letters()
                    ->numbers()
                    ->symbols()
                    ->uncompromised();
            });
        }
    }
}
