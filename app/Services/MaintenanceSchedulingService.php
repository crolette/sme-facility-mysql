<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Tenants\User;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Contract;
use Barryvdh\Debugbar\Facades\Debugbar;
use App\Models\Tenants\ScheduledNotification;
use App\Enums\ScheduledNotificationStatusEnum;
use App\Models\Tenants\Maintainable;
use App\Models\Tenants\UserNotificationPreference;

class MaintenanceSchedulingService
{


    public function updateScheduleMaintenanceForNotificationType(UserNotificationPreference $preference)
    {
        // 1. il faut rechercher toutes les  scheduled_notifications avec le notification_type et le user_id ET l'asset_type


        if ($preference->wasChanged('notification_delay_days')) {
            match ($preference->notification_type) {
                'next_maintenance_date'  => $this->updateScheduleForNextMaintenanceDate($preference),
                default => null
            };
        };

        if ($preference->wasChanged('enabled') && $preference->enabled === false) {
            $this->deleteScheduledNotificationForNotificationType($preference);
        }


        if ($preference->wasChanged('enabled') && $preference->enabled === true) {
            match ($preference->notification_type) {
                'next_maintenance_date'  => $this->createScheduleForNextMaintenanceDate($preference),
                default => null
                // 'site'  => Site::findOrFail($locationId),
                // 'building' => Building::findOrFail($locationId),
                // 'floor' => Floor::findOrFail($locationId),
                // 'room' => Room::findOrFail($locationId),
            };
        }
    }

    public function updateScheduleForNextMaintenanceDate(UserNotificationPreference $preference)
    {

        $scheduledNotifications = ScheduledNotification::where('recipient_email', $preference->user->email)->where('notification_type', $preference->notification_type)->get();



        foreach ($scheduledNotifications as $notification) {
            $newDate = $notification->notifiable->maintainable->next_maintenance_date->subDays($preference->notification_delay_days);
            $notification->update(['scheduled_at' => $newDate]);
        }
    }

    public function deleteScheduledNotificationForNotificationType(UserNotificationPreference $preference)
    {
        $scheduledNotifications = ScheduledNotification::where('recipient_email', $preference->user->email)->where('notification_type', $preference->notification_type)->get();

        foreach ($scheduledNotifications as $notification) {
            $notification->delete();
        }
    }

    public function createScheduleForNextMaintenanceDate(UserNotificationPreference $preference)
    {
        $delayDays = $preference->notification_delay_days;
        $assets = Asset::whereHas('maintainable', fn($query) => $query->where('next_maintenance_date', '>', Carbon::now()->addDays($delayDays)))->get();
        $user = $preference->user;

        foreach ($assets as $asset) {
            $notification = [
                'status' => ScheduledNotificationStatusEnum::PENDING->value,
                'data' => [
                    'subject' => 'test',
                    'notice_date' => $asset->maintainable->next_maintenance_date
                ]
            ];

            $asset->notifications()->create([
                ...$notification,
                'scheduled_at' => $asset->maintainable->next_maintenance_date->subDays($delayDays),
                'notification_type' => 'next_maintenance_date',
                'recipient_name' => $user->fullName,
                'recipient_email' => $user->email,
            ]);
        }
    }
}
