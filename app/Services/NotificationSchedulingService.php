<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Tenants\Room;
use App\Models\Tenants\Site;
use App\Models\Tenants\User;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Floor;
use App\Models\Tenants\Building;
use App\Models\Tenants\Contract;
use App\Enums\MaintenanceFrequency;
use App\Models\Tenants\Intervention;
use Barryvdh\Debugbar\Facades\Debugbar;
use App\Models\Tenants\ScheduledNotification;
use App\Enums\ScheduledNotificationStatusEnum;
use App\Models\Tenants\UserNotificationPreference;
use App\Services\AssetNotificationSchedulingService;
use App\Services\MaintainableNotificationSchedulingService;

class NotificationSchedulingService
{

    public function updateScheduleOfUserForNotificationType(UserNotificationPreference $preference)
    {
        // 1. il faut rechercher toutes les  scheduled_notifications avec le notification_type et le user_id ET l'asset_type


        if ($preference->wasChanged('notification_delay_days')) {
            match ($preference->notification_type) {
                'notice_date'  => $this->updateScheduleForContractNoticeDate($preference),
                'end_date'  => $this->updateScheduleForContractEndDate($preference),
                'end_warranty_date' => $this->updateScheduleForEndWarrantyDate($preference),
                'depreciation_end_date' => $this->updateScheduleForDepreciationEndDate($preference),
                'next_maintenance_date' => $this->updateScheduleForNextMaintenanceDate($preference),
                'planned_at' => $this->updateScheduleForPlannedAtDate($preference),
                default => null
            };
        };

        if ($preference->wasChanged('enabled') && $preference->enabled === false) {
            $this->deleteScheduledNotificationForNotificationType($preference);
        }


        if ($preference->wasChanged('enabled') && $preference->enabled === true) {
            match ($preference->notification_type) {
                'notice_date'  => $this->createScheduleForContractNoticeDate($preference),
                'end_date'  => $this->createScheduleForContractEndDate($preference),
                'end_warranty_date' => $this->createScheduleForWarrantyEndDate($preference),
                'depreciation_end_date' => $this->createScheduleForDepreciationEndDate($preference),
                'next_maintenance_date' => $this->createScheduleForNextMaintenanceDate($preference),
                'planned_at' => $this->createScheduleForPlannedAtDate($preference),
                default => null
            };
        }
    }

    public function updateScheduleForNextMaintenanceDate(UserNotificationPreference $preference)
    {

        $scheduledNotifications = ScheduledNotification::where('recipient_email', $preference->user->email)->where('notification_type', $preference->notification_type)->get();

        foreach ($scheduledNotifications as $notification) {

            if ($notification->notifiable->maintainable->maintenance_frequency == MaintenanceFrequency::ONDEMAND->value || $notification->notifiable->maintainable->next_maintenance_date->subDays($preference->notification_delay_days) < now())
                continue;

            $newDate = $notification->notifiable->maintainable->next_maintenance_date->subDays($preference->notification_delay_days);
            $notification->update(['scheduled_at' => $newDate]);
        }
    }

    public function updateScheduleForDepreciationEndDate(UserNotificationPreference $preference)
    {
        $scheduledNotifications = ScheduledNotification::where('recipient_email', $preference->user->email)->where('notification_type', $preference->notification_type)->get();

        foreach ($scheduledNotifications as $notification) {
            $newDate = $notification->notifiable->depreciation_end_date->subDays($preference->notification_delay_days);
            $notification->update(['scheduled_at' => $newDate]);
        }
    }


    public function updateScheduleForEndWarrantyDate(UserNotificationPreference $preference)
    {

        $scheduledNotifications = ScheduledNotification::where('recipient_email', $preference->user->email)->where('notification_type', $preference->notification_type)->get();

        foreach ($scheduledNotifications as $notification) {
            $newDate = $notification->notifiable->maintainable->end_warranty_date->subDays($preference->notification_delay_days);
            $notification->update(['scheduled_at' => $newDate]);
        }
    }

    public function updateScheduleForContractNoticeDate(UserNotificationPreference $preference)
    {

        $scheduledNotifications = ScheduledNotification::where('recipient_email', $preference->user->email)->where('notification_type', $preference->notification_type)->get();

        foreach ($scheduledNotifications as $notification) {
            $newDate = $notification->notifiable->notice_date->subDays($preference->notification_delay_days);
            $notification->update(['scheduled_at' => $newDate]);
        }
    }


    public function updateScheduleForContractEndDate(UserNotificationPreference $preference)
    {
        $scheduledNotifications = ScheduledNotification::where('recipient_email', $preference->user->email)->where('notification_type', $preference->notification_type)->get();

        foreach ($scheduledNotifications as $notification) {
            $newDate = $notification->notifiable->end_date->subDays($preference->notification_delay_days);
            $notification->update(['scheduled_at' => $newDate]);
        }
    }

    public function updateScheduleForPlannedAtDate(UserNotificationPreference $preference)
    {
        $scheduledNotifications = ScheduledNotification::where('recipient_email', $preference->user->email)->where('notification_type', $preference->notification_type)->get();

        foreach ($scheduledNotifications as $notification) {
            $newDate = $notification->notifiable->planned_at->subDays($preference->notification_delay_days);
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

    public function createScheduleForPlannedAtDate(UserNotificationPreference $preference)
    {
        $delayDays = $preference->notification_delay_days;
        $interventions = Intervention::where('planned_at', '>', Carbon::now()->addDays($delayDays))->get();

        $user = $preference->user;

        foreach ($interventions as $intervention) {

            app(InterventionNotificationSchedulingService::class)->createScheduleForPlannedAtDate($intervention, $user);
        }
    }

    public function createScheduleForContractNoticeDate(UserNotificationPreference $preference)
    {
        $delayDays = $preference->notification_delay_days;
        $contracts = Contract::where('notice_date', '>', Carbon::now()->addDays($delayDays))->get();

        $user = $preference->user;

        foreach ($contracts as $contract) {
            app(ContractNotificationSchedulingService::class)->createScheduleForContractNoticeDate($contract, $user);
        }
    }


    public function createScheduleForContractEndDate(UserNotificationPreference $preference)
    {
        $delayDays = $preference->notification_delay_days;
        $contracts = Contract::where('end_date', '>', Carbon::now()->addDays($delayDays))->get();

        $user = $preference->user;

        foreach ($contracts as $contract) {

            app(ContractNotificationSchedulingService::class)->createScheduleForContractEndDate($contract, $user);
        }
    }


    public function createScheduleForWarrantyEndDate(UserNotificationPreference $preference)
    {
        $delayDays = $preference->notification_delay_days;

        $assetsOrLocations = collect()
            ->merge($this->searchEntity(Asset::class, 'end_warranty_date', $delayDays))
            ->merge($this->searchEntity(Site::class, 'end_warranty_date', $delayDays))
            ->merge($this->searchEntity(Building::class, 'end_warranty_date', $delayDays))
            ->merge($this->searchEntity(Floor::class, 'end_warranty_date', $delayDays))
            ->merge($this->searchEntity(Room::class, 'end_warranty_date', $delayDays));

        $user = $preference->user;

        foreach ($assetsOrLocations as $assetOrLocation) {

            app(MaintainableNotificationSchedulingService::class)->createScheduleForEndWarrantyDate($assetOrLocation->maintainable, $user);
        }
    }


    public function createScheduleForDepreciationEndDate(UserNotificationPreference $preference)
    {
        $delayDays = $preference->notification_delay_days;
        $assets = Asset::where('depreciation_end_date', '>', Carbon::now()->addDays($delayDays))->get();

        foreach ($assets as $asset) {

            app(AssetNotificationSchedulingService::class)->createScheduleForDepreciable($asset, $preference->user);
        }
    }

    public function createScheduleForNextMaintenanceDate(UserNotificationPreference $preference)
    {
        $delayDays = $preference->notification_delay_days;
        $assetsOrLocations = collect([]);

        $assetsOrLocations = collect()
            ->merge($this->searchEntity(Asset::class, 'next_maintenance_date', $delayDays))
            ->merge($this->searchEntity(Site::class, 'next_maintenance_date', $delayDays))
            ->merge($this->searchEntity(Building::class, 'next_maintenance_date', $delayDays))
            ->merge($this->searchEntity(Floor::class, 'next_maintenance_date', $delayDays))
            ->merge($this->searchEntity(Room::class, 'next_maintenance_date', $delayDays));

        $user = $preference->user;

        foreach ($assetsOrLocations as $assetOrLocation) {
            app(MaintainableNotificationSchedulingService::class)->createScheduleForNextMaintenanceDate($assetOrLocation->maintainable, $user);
        }
    }

    private function searchEntity($modelClass, $column, $delayDays)
    {
        return $modelClass::whereHas('maintainable', fn($query) => $query->where($column, '>', Carbon::now()->addDays($delayDays)))->get();
    }
}
