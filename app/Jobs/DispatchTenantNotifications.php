<?php

namespace App\Jobs;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use App\Jobs\ProcessTenantNotifications;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class DispatchTenantNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 120;

    public function __construct()
    {
        //
    }

    public function handle()
    {
        Log::info('Starting tenant notifications dispatch');

        // Récupère tous les tenants actifs depuis la DB centrale
        $tenants = Tenant::all();

        // Log::info("Found {$tenants->count()} active tenants to process");

        tenancy()->runForMultiple($tenants, function($tenant) {

            ProcessTenantNotifications::dispatch($tenant)->onQueue('default');
    
            Log::info("Dispatched notification job for tenant: {$tenant->id}");
        });
        // foreach ($tenants as $tenant) {
        //     // Dispatche un job pour chaque tenant
        // }

        Log::info('Tenant notifications dispatch completed');
    }

    public function failed(\Throwable $exception)
    {
        Log::error('Failed to dispatch tenant notifications', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
