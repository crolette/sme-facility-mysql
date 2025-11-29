<?php

namespace App\Jobs;

use App\Enums\ContractRenewalTypesEnum;
use App\Enums\ContractStatusEnum;
use Carbon\Carbon;
use App\Models\Tenant;
use App\Models\Tenants\Contract;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\Tenants\ScheduledNotification;
use App\Services\ContractService;

class ProcessExpiredContractsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 300;

    protected $tenant;

    public function __construct(Tenant $tenant)
    {
        $this->tenant = $tenant;
    }

    public function handle()
    {
        $tenantId = $this->tenant->id;

        Log::info("Processing expired contracts for tenant: {$tenantId}");

        // Se connecter à la DB du tenant
        $this->tenant->run(function () use ($tenantId) {

            // Récupérer les notifications à envoyer (aujourd'hui)
            $expiredManualContracts = Contract::where('end_date', '<', Carbon::now()->toDateString())->where('status', ContractStatusEnum::ACTIVE)->where('renewal_type', ContractRenewalTypesEnum::MANUAL)->get();
            $expiredAutomaticContracts = Contract::where('end_date', '<', Carbon::now()->toDateString())->where('status', ContractStatusEnum::ACTIVE)->where('renewal_type', ContractRenewalTypesEnum::AUTOMATIC)->get();

            Log::info("Found {$expiredManualContracts->count()} expiredManualContracts to process for tenant: {$tenantId}");
            Log::info("Found {$expiredAutomaticContracts->count()} expiredAutomaticContracts to process for tenant: {$tenantId}");

            $successCount = 0;
            $errorCount = 0;

            foreach ($expiredManualContracts as $expiredManualContract) {

                try {
                    $expiredManualContract->update([
                        'status' => ContractStatusEnum::EXPIRED,
                    ]);
                    Log::info('Contract updated', [
                        'id' => $expiredManualContract->id,
                        'name' => $expiredManualContract->name,
                        'description' => $expiredManualContract->description,
                    ]);
                    $successCount++;
                } catch (\Exception $e) {
                    $this->handleNotificationError($expiredManualContract, $e, $tenantId);
                    $errorCount++;
                }
            }

            foreach ($expiredAutomaticContracts as $expiredAutomaticContract) {

                try {
                    app(ContractService::class)->extendAutomaticContract($expiredAutomaticContract);
                    Log::info('Contract extended', [
                        'id' => $expiredAutomaticContract->id,
                        'name' => $expiredAutomaticContract->name,
                        'description' => $expiredAutomaticContract->description,
                    ]);
                    $successCount++;
                } catch (\Exception $e) {
                    $this->handleNotificationError($expiredAutomaticContract, $e, $tenantId);
                    $errorCount++;
                }
            }

            Log::info("Tenant {$tenantId} expired contracts processing completed. Success: {$successCount}, Errors: {$errorCount}");
        });
    }


    protected function handleNotificationError(Contract $contract, \Exception $e, $tenantId)
    {
        Log::error("Failed to change contract status", [
            'tenant_id' => $tenantId,
            'notification_id' => $contract->id,
            'error' => $e->getMessage()
        ]);
    }

    public function failed(\Throwable $exception)
    {
        Log::error("Tenant expired contract processing job failed completely", [
            'tenant_id' => $this->tenant->id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
