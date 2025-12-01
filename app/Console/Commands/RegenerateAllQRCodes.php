<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\Tenants\Room;
use App\Models\Tenants\Site;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Floor;
use RecursiveIteratorIterator;
use App\Models\Tenants\Company;
use App\Services\QRCodeService;
use Illuminate\Console\Command;
use App\Models\Tenants\Building;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Jobs\ProcessTenantNotifications;
use Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator;


class RegenerateAllQRCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:regen-qr-codes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch QR Code regeneration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // $redis = app('redis')->connection('queue');
        // $redis->client()->select(3);

        Log::info('Starting QR Code regeneration command');

        // Récupère tous les tenants actifs depuis la DB centrale
        $tenants = Tenant::all();


        tenancy()->runForMultiple($tenants, function ($tenant) {
            Log::info('tenant domain : ' . $tenant->domain->domain);
            Log::info('Regen QR codes for tenant : ' . $tenant);
            Log::info('tenant initialized ? ' . tenancy()->initialized);
            $collection = collect([]);
            $assets = Asset::whereNotNull('qr_code')->get();
            $sites = Site::whereNotNull('qr_code')->get();
            $buildings = Building::whereNotNull('qr_code')->get();
            $floors = Floor::whereNotNull('qr_code')->get();
            $rooms = Room::whereNotNull('qr_code')->get();

            $items = $collection->merge($sites)->merge($buildings)->merge($floors)->merge($rooms)->merge($assets);

            foreach ($items as $item) {
                app(QRCodeService::class)->createAndAttachQR($item, $tenant->domain->domain);
            }

            Log::info("Dispatched QR Code regeneration for tenant: {$tenant->company_code}");
        });

        Log::info('Tenant QR Code regeneration dispatch completed');
    }

    public function failed(\Throwable $exception)
    {
        Log::error('Failed to dispatch QR Code regeneration command', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
