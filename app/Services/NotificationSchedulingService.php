<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Tenants\User;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Contract;
use Barryvdh\Debugbar\Facades\Debugbar;
use App\Models\Tenants\ScheduledNotification;
use App\Enums\ScheduledNotificationStatusEnum;
use App\Models\Tenants\UserNotificationPreference;

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
                default => null
                // 'site'  => Site::findOrFail($locationId),
                // 'building' => Building::findOrFail($locationId),
                // 'floor' => Floor::findOrFail($locationId),
                // 'room' => Room::findOrFail($locationId),
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
                default => null
                // 'site'  => Site::findOrFail($locationId),
                // 'building' => Building::findOrFail($locationId),
                // 'floor' => Floor::findOrFail($locationId),
                // 'room' => Room::findOrFail($locationId),
            };
        }



        // dump($scheduledNotifications);
        // asset_type : asset, location, contract, intervention
        // 2. Mettre à jour la date scheduled_at de chaque scheduled_notification en prenant en compte le nouveau notification_delay_days



        Debugbar::info('updateScheduleOfUserForNotificationType', $preference);
    }

    public function updateScheduleForNextMaintenanceDate(UserNotificationPreference $preference)
    {

        $scheduledNotifications = ScheduledNotification::where('recipient_email', $preference->user->email)->where('notification_type', $preference->notification_type)->get();

        foreach ($scheduledNotifications as $notification) {
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


    public function deleteScheduledNotificationForNotificationType(UserNotificationPreference $preference)
    {
        $scheduledNotifications = ScheduledNotification::where('recipient_email', $preference->user->email)->where('notification_type', $preference->notification_type)->get();

        foreach ($scheduledNotifications as $notification) {
            $notification->delete();
        }
    }

    public function createScheduleForContractNoticeDate(UserNotificationPreference $preference)
    {
        $delayDays = $preference->notification_delay_days;
        $contracts = Contract::where('notice_date', '>', Carbon::now()->addDays($delayDays))->get();

        $user = $preference->user;

        foreach ($contracts as $contract) {
            $notification = [
                'status' => ScheduledNotificationStatusEnum::PENDING->value,
                'data' => [
                    'subject' => 'test',
                    'notice_date' => $contract->notice_date
                ]
            ];

            $createdNotification = $contract->notifications()->create([
                ...$notification,
                'scheduled_at' => $contract->notice_date->subDays($delayDays),
                'notification_type' => 'notice_date',
                'recipient_name' => $user->fullName,
                'recipient_email' => $user->email,
            ]);

            $createdNotification->user()->associate($user);
            $createdNotification->save();
        }
    }


    public function createScheduleForContractEndDate(UserNotificationPreference $preference)
    {
        $delayDays = $preference->notification_delay_days;
        $contracts = Contract::where('end_date', '>', Carbon::now()->addDays($delayDays))->get();

        $user = $preference->user;

        foreach ($contracts as $contract) {
            $notification = [
                'status' => ScheduledNotificationStatusEnum::PENDING->value,
                'data' => [
                    'subject' => 'test',
                    'notice_date' => $contract->end_date
                ]
            ];

            $createdNotification = $contract->notifications()->create([
                ...$notification,
                'scheduled_at' => $contract->end_date->subDays($delayDays),
                'notification_type' => 'end_date',
                'recipient_name' => $user->fullName,
                'recipient_email' => $user->email,
            ]);

            $createdNotification->user()->associate($user);
            $createdNotification->save();
        }
    }


    public function createScheduleForWarrantyEndDate(UserNotificationPreference $preference)
    {
        $delayDays = $preference->notification_delay_days;
        $assets = Asset::whereHas('maintainable', fn($query) => $query->where('end_warranty_date', '>', Carbon::now()->addDays($delayDays)))->get();
        $user = $preference->user;

        foreach ($assets as $asset) {
            $notification = [
                'status' => ScheduledNotificationStatusEnum::PENDING->value,
                'data' => [
                    'subject' => 'test',
                    'notice_date' => $asset->maintainable->end_warranty_date
                ]
            ];

            $asset->notifications()->create([
                ...$notification,
                'scheduled_at' => $asset->maintainable->end_warranty_date->subDays($delayDays),
                'notification_type' => 'end_warranty_date',
                'recipient_name' => $user->fullName,
                'recipient_email' => $user->email,
            ]);
        }
    }


    public function createScheduleForDepreciationEndDate(UserNotificationPreference $preference)
    {
        $delayDays = $preference->notification_delay_days;
        $assets = Asset::where('depreciation_end_date', '>', Carbon::now()->addDays($delayDays))->get();
        $user = $preference->user;

        foreach ($assets as $asset) {
            $notification = [
                'status' => ScheduledNotificationStatusEnum::PENDING->value,
                'data' => [
                    'subject' => 'test',
                    'notice_date' => $asset->end_date
                ]
            ];

            $asset->notifications()->create([
                ...$notification,
                'scheduled_at' => $asset->depreciation_end_date->subDays($delayDays),
                'notification_type' => 'depreciation_end_date',
                'recipient_name' => $user->fullName,
                'recipient_email' => $user->email,
            ]);
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
