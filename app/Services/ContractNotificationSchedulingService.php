<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Tenants\User;
use App\Models\Tenants\Contract;
use Barryvdh\Debugbar\Facades\Debugbar;
use App\Enums\ScheduledNotificationStatusEnum;
use App\Models\Tenants\ScheduledNotification;
use App\Models\Tenants\UserNotificationPreference;

class ContractNotificationSchedulingService
{
    public function scheduleForContract(Contract $contract)
    {
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
                $delay = $user->notification_preferences()->where('notification_type', $notificationType)->where('enabled', true)->value('notification_delay_days') ?? 7;
                // get the date of the notification type : i.e. notice_date or end_date
                $date = $contract->$notificationType;

                $createdNotification = $contract->notifications()->updateOrCreate(
                    [
                        'recipient_email' => $user->email,
                        'notification_type' => $notificationType,
                    ],
                    [
                        ...$notification,
                        'scheduled_at' => $date->subDays($delay),
                        'notification_type' => $notificationType,
                        'recipient_name' => $user->fullName,
                        'recipient_email' => $user->email,
                    ]
                );

                // dump($createdNotification);

                $createdNotification->user()->associate($user);
                $createdNotification->save();
            }
        }
    }

    public function updateScheduleForContract(Contract $contract)
    {
        // 1. reprendre les notifications liées au contrat
        // 2. reprendre les utilisateurs admin avec leur préférence
        // 3. boucler sur chaque user et actualiser avec les préférences

        if ($contract->wasChanged('end_date')) {
            $notifications = $contract->notifications()->where('notification_type', 'end_date')->get();
            foreach ($notifications as $notification) {
                $this->updateScheduleForContractEndDate($contract, $notification);
            }
        }

        if ($contract->wasChanged('notice_date')) {
            $notifications = $contract->notifications()->where('notification_type', 'notice_date')->get();

            foreach ($notifications as $notification) {
                $this->updateScheduleForContractNoticeDate($contract, $notification);
            }
        }
    }

    public function updateScheduleForContractNoticeDate(Contract $contract, ScheduledNotification $notification)
    {
        // TODO check if the date is > then start_date

        $newDate = $contract->notice_date->subDays($notification->user->notification_preferences()->where('notification_type', 'notice_date')->first()->notification_delay_days);
        if ($newDate > now())
            $notification->update(['scheduled_at' => $newDate]);
    }


    public function updateScheduleForContractEndDate(Contract $contract, ScheduledNotification $notification)
    {

        $newDate = $contract->end_date->subDays($notification->user->notification_preferences()->where('notification_type', 'end_date')->first()->notification_delay_days);
        if ($newDate > now())
            $notification->update(['scheduled_at' => $newDate]);
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

            $createdNotification = $contract->notifications()->updateOrCreate(
                [
                    'recipient_email' => $user->email,
                    'notification_type' => 'notice_date',
                ],
                [
                    ...$notification,
                    'scheduled_at' => $contract->notice_date->subDays($delayDays),
                    'notification_type' => 'notice_date',
                    'recipient_name' => $user->fullName,
                    'recipient_email' => $user->email,
                ]
            );

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
}
