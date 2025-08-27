<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Tenants\User;
use App\Models\Tenants\Contract;
use Barryvdh\Debugbar\Facades\Debugbar;
use App\Enums\ScheduledNotificationStatusEnum;
use App\Models\Tenants\ScheduledNotification;
use App\Models\Tenants\UserNotificationPreference;

class NotificationSchedulingService
{
    public function scheduleForContract(Contract $contract)
    {
        Debugbar::info('NotificationSchedulingService - scheduleForContract');

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

        $notificationTypes = collect(config('notifications.notification_types.contract'));

        $notification = [
            'status' => ScheduledNotificationStatusEnum::PENDING->value,
            'data' => [
                'subject' => 'test',
                'notice_date' => $contract->notice_date
            ]
        ];

        $users = User::role('Admin')->get();

        foreach ($notificationTypes as $notificationType) {
            $date = $contract->$notificationType;

            foreach ($users as $user) {
                $delay = $user->notification_preferences()->where('notification_type', $notificationType)->value('notification_delay_days') ?? 7;

                $contract->notifications()->create([
                    ...$notification,
                    'scheduled_at' => $date->subDays($delay),
                    'notification_type' => $notificationType,
                    'recipient_name' => $user->fullName,
                    'recipient_email' => $user->email,
                ]);
            }
        }
    }

    public function updateScheduleOfUserForNotificationType(UserNotificationPreference $preference)
    {
        // 1. il faut rechercher toutes les  scheduled_notifications avec le notification_type et le user_id ET l'asset_type


        $scheduledNotifications = ScheduledNotification::where('recipient_email', $preference->user->email)->where('notification_type', $preference->notification_type)->get();

        dump($scheduledNotifications);
        // asset_type : asset, location, contract, intervention
        // 2. Mettre à jour la date scheduled_at de chaque scheduled_notification en prenant en compte le nouveau notification_delay_days



        Debugbar::info('updateScheduleOfUserForNotificationType', $preference);
    }
}
