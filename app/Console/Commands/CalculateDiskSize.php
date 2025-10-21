<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use RecursiveIteratorIterator;
use App\Models\Tenants\Company;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Jobs\ProcessTenantNotifications;
use Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator;


class CalculateDiskSize extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:tenants-disk-size';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch disk size calculation';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // $redis = app('redis')->connection('queue');
        // $redis->client()->select(3);

        Log::info('Starting tenant disk size calculation command');

        // Récupère tous les tenants actifs depuis la DB centrale
        $tenants = Tenant::all();

        // Log::info("Found {$tenants->count()} active tenants to process");

        tenancy()->runForMultiple($tenants, function ($tenant) {

            $path = Storage::disk('tenants')->path($tenant->company_code);

            $output = shell_exec("du -sb {$path}");
            $size = (int) explode("\t", $output)[0];

            Company::updateOrCreate(
                ['vat_number' => $tenant->vat_number],
                [
                    'disk_size' => $size,
                    'name' => $tenant->company_name,
                    'vat_number' => $tenant->vat_number,
                ]
            );

            Log::info("Dispatched tenant disk size calculation for tenant: {$tenant->company_code} : {$size}");
        });

        Log::info('Tenant disk size calculation dispatch completed');
    }

    public function failed(\Throwable $exception)
    {
        Log::error('Failed to dispatch tenant disk size calculation command', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
