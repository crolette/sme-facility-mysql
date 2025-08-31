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

class AssetNotificationSchedulingService
{
    public function scheduleForAsset(Asset $asset)
    {
        // $notificationTypes = collect(config('notifications.notification_types.asset'));

        // warranty date : maintainable under warranty
        // depcriation : depreciable (true/false)
        // maintenance : maintainable : need_maintenance



        $users = User::role('Admin')->get();

        if ($asset->depreciable) {

            $notification = [
                'status' => ScheduledNotificationStatusEnum::PENDING->value,
                'notification_type' => 'depreciation_end_date',

                'data' => [
                    'subject' => $asset->name,
                    'depreciation_end_date' => $asset->depreciation_end_date
                ]
            ];

            if ($asset->maintainable->manager) {
                $manager = $asset->maintainable->manager;
                $preference = $manager->notification_preferences()->where('notification_type', 'depreciation_end_date')->first();

                if ($preference && $preference->enabled) {
                    $delay = $preference->notification_delay_days;

                    $asset->notifications()->create(
                        [
                            ...$notification,
                            'recipient_name' => $manager->fullName,
                            'recipient_email' => $manager->email,
                            'scheduled_at' => $asset->depreciation_end_date->subDays($delay),
                        ]
                    );
                }
            }

            foreach ($users as $user) {
                $preference = $user->notification_preferences()->where('notification_type', 'depreciation_end_date')->first();

                if (!$preference || !$preference->enabled)
                    continue;

                $delay = $preference->notification_delay_days;

                $asset->notifications()->create(
                    [
                        ...$notification,
                        'recipient_name' => $user->fullName,
                        'recipient_email' => $user->email,
                        'scheduled_at' => $asset->depreciation_end_date->subDays($delay),
                    ]
                );
            }
        }

        if ($asset->maintainable->need_maintenance) {

            $notification = [
                'status' => ScheduledNotificationStatusEnum::PENDING->value,
                'notification_type' => 'next_maintenance_date',

                'data' => [
                    'subject' => $asset->name,
                    'next_maintenance_date' => $asset->maintainable->next_maintenance_date
                ]
            ];

            // maintenance manager
            if ($asset->maintainable->manager) {
                $manager = $asset->maintainable->manager;
                $preference = $manager->notification_preferences()->where('notification_type', 'next_maintenance_date')->first();

                if ($preference && $preference->enabled) {
                    $delay = $preference->notification_delay_days;

                    $asset->notifications()->create(
                        [
                            ...$notification,
                            'recipient_name' => $manager->fullName,
                            'recipient_email' => $manager->email,
                            'scheduled_at' => $asset->maintainable->next_maintenance_date->subDays($delay),
                        ]
                    );
                }
            }

            foreach ($users as $user) {
                $preference = $user->notification_preferences()->where('notification_type', 'next_maintenance_date')->first();

                if (!$preference || !$preference->enabled)
                    continue;

                $delay = $preference->notification_delay_days;

                $asset->notifications()->create(
                    [
                        ...$notification,
                        'recipient_name' => $user->fullName,
                        'recipient_email' => $user->email,
                        'scheduled_at' => $asset->maintainable->next_maintenance_date->subDays($delay),
                    ]
                );
            }
        }

        if ($asset->maintainable->under_warranty) {

            $notification = [
                'status' => ScheduledNotificationStatusEnum::PENDING->value,
                'notification_type' => 'end_warranty_date',

                'data' => [
                    'subject' => $asset->name,
                    'end_warranty_date' => $asset->maintainable->end_warranty_date
                ]
            ];

            // maintenance manager
            if ($asset->maintainable->manager) {
                $manager = $asset->maintainable->manager;
                $preference = $manager->notification_preferences()->where('notification_type', 'end_warranty_date')->first();

                if ($preference && $preference->enabled) {
                    $delay = $preference->notification_delay_days;

                    $asset->notifications()->create(
                        [
                            ...$notification,
                            'recipient_name' => $manager->fullName,
                            'recipient_email' => $manager->email,
                            'scheduled_at' => $asset->maintainable->end_warranty_date->subDays($delay),
                        ]
                    );
                }
            }

            foreach ($users as $user) {
                $preference = $user->notification_preferences()->where('notification_type', 'end_warranty_date')->first();

                if (!$preference || !$preference->enabled)
                    continue;

                $delay = $preference->notification_delay_days;

                $asset->notifications()->create(
                    [
                        ...$notification,
                        'recipient_name' => $user->fullName,
                        'recipient_email' => $user->email,
                        'scheduled_at' => $asset->maintainable->end_warranty_date->subDays($delay),
                    ]
                );
            }
        }
        //

    }

    public function updateForAsset(Asset $asset)
    {
        Debugbar::info('updateForAsset');
        if ($asset->maintainable->wasChanged('end_warranty_date')) {
            Debugbar::info('updateForAsset end_warranty_date changed');
        }

        if ($asset->maintainable->wasChanged('next_maintenance_date')) {
            Debugbar::info('updateForAsset next_maintenance_date changed');
        }

        if ($asset->wasChanged('depreciation_end_date')) {
            Debugbar::info('updateForAsset depreciation_end_date changed');
        }
    }

    public function updateScheduleOfAssetForNotificationType(UserNotificationPreference $preference)
    {
        // 1. il faut rechercher toutes les  scheduled_notifications avec le notification_type et le user_id ET l'asset_type


        if ($preference->wasChanged('notification_delay_days')) {
            match ($preference->notification_type) {
                'end_warranty_date'  => $this->updateScheduleForEndWarrantyDate($preference),
                'depreciation_end_date'  => $this->updateScheduleForDepreciationEndDate($preference),
                default => null
            };
        };

        if ($preference->wasChanged('enabled') && $preference->enabled === false) {
            $this->deleteScheduledNotificationForNotificationType($preference);
        }


        if ($preference->wasChanged('enabled') && $preference->enabled === true) {
            match ($preference->notification_type) {
                'end_warranty_date'  => $this->createScheduleForWarrantyEndDate($preference),
                'depreciation_end_date'  => $this->createScheduleForDepreciationEndDate($preference),
                default => null
            };
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


    public function updateScheduleForDepreciationEndDate(UserNotificationPreference $preference)
    {
        $scheduledNotifications = ScheduledNotification::where('recipient_email', $preference->user->email)->where('notification_type', $preference->notification_type)->get();

        foreach ($scheduledNotifications as $notification) {
            $newDate = $notification->notifiable->depreciation_end_date->subDays($preference->notification_delay_days);
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
}
