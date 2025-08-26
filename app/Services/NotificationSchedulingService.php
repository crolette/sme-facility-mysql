<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Tenants\User;
use App\Models\Tenants\Contract;
use Barryvdh\Debugbar\Facades\Debugbar;
use App\Enums\ScheduledNotificationStatusEnum;


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
            foreach ($users as $user) {
                $delay = $user->notification_preferences()->where('notification_type', $notificationType)->first()?->pluck('notification_delay_days') ?? 7;
                $date = $contract->$notificationType;

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

    public function updateScheduleOfUserForNotificationType(User $user, string $notification_type) {}
}
