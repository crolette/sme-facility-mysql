<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\ProcessTenantNotifications;
use Illuminate\Support\Facades\Log;
use App\Models\Tenant;


class DispatchTenantNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:dispatch-notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch scheduled notifications';

    /**
     * Execute the console command.
     */	
    public function handle()
    {
        // $redis = app('redis')->connection('queue');
        // $redis->client()->select(3);

        Log::info('Starting tenant notifications dispatch command');

        // Récupère tous les tenants actifs depuis la DB centrale
        $tenants = Tenant::all();

        // Log::info("Found {$tenants->count()} active tenants to process");

        tenancy()->runForMultiple($tenants, function ($tenant) {

            ProcessTenantNotifications::dispatch($tenant)->onQueue('default');

            Log::info("Dispatched notification command job for tenant: {$tenant->id}");
        });
        // foreach ($tenants as $tenant) {
        //     // Dispatche un job pour chaque tenant
        // }

        Log::info('Tenant notifications dispatch completed');
    }

    public function failed(\Throwable $exception)
    {
        Log::error('Failed to dispatch tenant notifications command', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
