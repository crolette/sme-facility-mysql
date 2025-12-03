<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\Tenants\Site;
use App\Models\Tenants\User;
use RecursiveIteratorIterator;
use App\Models\Tenants\Company;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Jobs\ProcessTenantNotifications;
use Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator;


class CalculateTenantLimits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:tenants-limits';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch tenants limits calculation';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // $redis = app('redis')->connection('queue');
        // $redis->client()->select(3);

        Log::info('Starting tenants limits calculation command');

        // Récupère tous les tenants actifs depuis la DB centrale
        $tenants = Tenant::all();

        // Log::info("Found {$tenants->count()} active tenants to process");

        tenancy()->runForMultiple($tenants, function ($tenant) {

            $sites = Site::count();
            $users = User::where('can_login', true)->count();

            $tenant->update(['current_sites_count' => $sites, 'current_users_count' => $users]);

            Log::info("Dispatched tenants limits calculation for tenant: {$tenant->company_code}");
        });

        Log::info('Tenants limits calculation dispatch completed');
    }

    public function failed(\Throwable $exception)
    {
        Log::error('Failed to dispatch tenants limits calculation command', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
