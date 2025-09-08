<?php

namespace App\Jobs;

use Carbon\Carbon;
use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\Tenants\ScheduledNotification;

class ProcessTenantNotifications implements ShouldQueue
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

        Log::info("Processing notifications for tenant: {$tenantId}");

        // Se connecter à la DB du tenant
        $this->tenant->run(function () use ($tenantId) {

            // Récupérer les notifications à envoyer (aujourd'hui)
            $notifications = ScheduledNotification::where('status', 'pending')
                ->where('scheduled_at', '<=', now())
                ->get();

            Log::info("Found {$notifications->count()} notifications to process for tenant: {$tenantId}");

            $successCount = 0;
            $errorCount = 0;

            foreach ($notifications as $notification) {
                try {
                    $this->processNotification($notification, $tenantId);
                    $successCount++;
                } catch (\Exception $e) {
                    $this->handleNotificationError($notification, $e, $tenantId);
                    $errorCount++;
                }
            }

            Log::info("Tenant {$tenantId} processing completed. Success: {$successCount}, Errors: {$errorCount}");
        });
    }

    protected function processNotification(ScheduledNotification $notification, $tenantId)
    {
        Log::info("Try send mail to : {$notification->recipient_email} for type : {$notification->notification_type}");
        try {
            Mail::to($notification->recipient_email)->send(
                new \App\Mail\ScheduledNotificationMail($notification)
            );
            Log::info("Mail sent to : {$notification->recipient_email}");
        } catch (\Exception $e) {
            Log::error("Email failed: " . $e->getMessage());
            throw $e;
        }


        // Marquer comme envoyée
        $notification->update([
            'status' => 'sent',
            'sent_at' => now()
        ]);

        Log::info("Notification sent successfully", [
            'tenant_id' => $tenantId,
            'notification_id' => $notification->id,
            'type' => $notification->notification_type,
            'recipient' => $notification->recipient_email
        ]);
    }

    protected function handleNotificationError(ScheduledNotification $notification, \Exception $e, $tenantId)
    {
        // Incrémenter le compteur de retry
        $retryCount = $notification->retry_count + 1;

        // Si on a atteint le max de retry, marquer comme failed
        if ($retryCount >= 3) {
            $notification->update([
                'status' => 'failed',
                'retry_count' => $retryCount,
                'error_message' => $e->getMessage()
            ]);
        } else {
            // Sinon, juste incrémenter le compteur pour retry plus tard
            $notification->update([
                'retry_count' => $retryCount,
                'error_message' => $e->getMessage()
            ]);
        }

        Log::error("Failed to send notification", [
            'tenant_id' => $tenantId,
            'notification_id' => $notification->id,
            'retry_count' => $retryCount,
            'error' => $e->getMessage()
        ]);
    }

    public function failed(\Throwable $exception)
    {
        Log::error("Tenant notification processing job failed completely", [
            'tenant_id' => $this->tenant->id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
