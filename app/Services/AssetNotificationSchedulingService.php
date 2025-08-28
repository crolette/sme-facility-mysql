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

class AssetNotificationSchedulingService
{
    public function scheduleForAsset(Asset $asset)
    {
        // Debugbar::info('AssetNotificationSchedulingService - scheduleForAsset');

        // Exemple de JSON
        // [
        //     'asset/location name' => 'Photocopieur Xerox'/'Rez-de-chaussée',
        //     'due_date' => '2024-12-31',
        //     'dashboard_url' => 'https://app.com/contracts/15'
        //      
        //     'contract_name' => 'Contrat nettoyage',
        //     'supplier_name' => 'Entreprise XYZ',
        //     'contract_reference' => 'CNT-2024-001',
        // ]

        $notificationTypes = collect(config('notifications.notification_types.asset'));

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
        // $notification = [
        //     'status' => ScheduledNotificationStatusEnum::PENDING->value,
        //     'data' => [
        //         'subject' => 'test',
        //         'notice_date' => $contract->notice_date
        //     ]
        // ];

        // $users = User::role('Admin')->get();

        // foreach ($notificationTypes as $notificationType) {

        //     foreach ($users as $user) {
        //         $delay = $user->notification_preferences()->where('notification_type', $notificationType)->where('enabled', true)->value('notification_delay_days') ?? 7;
        //         // get the date of the notification type : i.e. notice_date or end_date
        //         $date = $contract->$notificationType;

        //         $contract->notifications()->create([
        //             ...$notification,
        //             'scheduled_at' => $date->subDays($delay),
        //             'notification_type' => $notificationType,
        //             'recipient_name' => $user->fullName,
        //             'recipient_email' => $user->email,
        //         ]);
        //     }
        // }
    }

    // public function updateScheduleOfUserForNotificationType(UserNotificationPreference $preference)
    // {
    //     // 1. il faut rechercher toutes les  scheduled_notifications avec le notification_type et le user_id ET l'asset_type


    //     if ($preference->wasChanged('notification_delay_days')) {
    //         match ($preference->notification_type) {
    //             'notice_date'  => $this->updateScheduleForContractNoticeDate($preference),
    //             'end_date'  => $this->updateScheduleForContractEndDate($preference),
    //             default => null
    //             // 'site'  => Site::findOrFail($locationId),
    //             // 'building' => Building::findOrFail($locationId),
    //             // 'floor' => Floor::findOrFail($locationId),
    //             // 'room' => Room::findOrFail($locationId),
    //         };
    //     };

    //     if ($preference->wasChanged('enabled') && $preference->enabled === false) {
    //         $this->deleteScheduledNotificationForNotificationType($preference);
    //     }


    //     if ($preference->wasChanged('enabled') && $preference->enabled === true) {
    //         match ($preference->notification_type) {
    //             'notice_date'  => $this->createScheduleForContractNoticeDate($preference),
    //             'end_date'  => $this->createScheduleForContractEndDate($preference),
    //             default => null
    //             // 'site'  => Site::findOrFail($locationId),
    //             // 'building' => Building::findOrFail($locationId),
    //             // 'floor' => Floor::findOrFail($locationId),
    //             // 'room' => Room::findOrFail($locationId),
    //         };
    //     }



    //     // dump($scheduledNotifications);
    //     // asset_type : asset, location, contract, intervention
    //     // 2. Mettre à jour la date scheduled_at de chaque scheduled_notification en prenant en compte le nouveau notification_delay_days



    // }

    // public function updateScheduleForContractNoticeDate(UserNotificationPreference $preference)
    // {

    //     $scheduledNotifications = ScheduledNotification::where('recipient_email', $preference->user->email)->where('notification_type', $preference->notification_type)->get();

    //     foreach ($scheduledNotifications as $notification) {
    //         $newDate = $notification->notifiable->notice_date->subDays($preference->notification_delay_days);
    //         $notification->update(['scheduled_at' => $newDate]);
    //     }
    // }


    // public function updateScheduleForContractEndDate(UserNotificationPreference $preference)
    // {
    //     $scheduledNotifications = ScheduledNotification::where('recipient_email', $preference->user->email)->where('notification_type', $preference->notification_type)->get();

    //     foreach ($scheduledNotifications as $notification) {
    //         $newDate = $notification->notifiable->end_date->subDays($preference->notification_delay_days);
    //         $notification->update(['scheduled_at' => $newDate]);
    //     }
    // }


    // public function deleteScheduledNotificationForNotificationType(UserNotificationPreference $preference)
    // {
    //     $scheduledNotifications = ScheduledNotification::where('recipient_email', $preference->user->email)->where('notification_type', $preference->notification_type)->get();

    //     foreach ($scheduledNotifications as $notification) {
    //         $notification->delete();
    //     }
    // }

    // public function createScheduleForContractNoticeDate(UserNotificationPreference $preference)
    // {
    //     $delayDays = $preference->notification_delay_days;
    //     $contracts = Contract::where('notice_date', '>', Carbon::now()->addDays($delayDays))->get();

    //     $user = $preference->user;

    //     foreach ($contracts as $contract) {
    //         $notification = [
    //             'status' => ScheduledNotificationStatusEnum::PENDING->value,
    //             'data' => [
    //                 'subject' => 'test',
    //                 'notice_date' => $contract->notice_date
    //             ]
    //         ];

    //         $contract->notifications()->create([
    //             ...$notification,
    //             'scheduled_at' => $contract->notice_date->subDays($delayDays),
    //             'notification_type' => 'notice_date',
    //             'recipient_name' => $user->fullName,
    //             'recipient_email' => $user->email,
    //         ]);
    //     }
    // }


    // public function createScheduleForContractEndDate(UserNotificationPreference $preference)
    // {
    //     $delayDays = $preference->notification_delay_days;
    //     $contracts = Contract::where('end_date', '>', Carbon::now()->addDays($delayDays))->get();

    //     $user = $preference->user;

    //     foreach ($contracts as $contract) {
    //         $notification = [
    //             'status' => ScheduledNotificationStatusEnum::PENDING->value,
    //             'data' => [
    //                 'subject' => 'test',
    //                 'notice_date' => $contract->end_date
    //             ]
    //         ];

    //         $contract->notifications()->create([
    //             ...$notification,
    //             'scheduled_at' => $contract->end_date->subDays($delayDays),
    //             'notification_type' => 'end_date',
    //             'recipient_name' => $user->fullName,
    //             'recipient_email' => $user->email,
    //         ]);
    //     }
    // }
}
