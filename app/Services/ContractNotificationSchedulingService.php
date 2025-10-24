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

        $users = User::role('Admin')->get();

        foreach ($users as $user) {

            $this->createScheduleForContractEndDate($contract, $user);
            $this->createScheduleForContractNoticeDate($contract, $user);
        }

        dump('contractables');
        dump($contract->contractables);
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

    public function createScheduleForContractNoticeDate(Contract $contract, User $user)
    {
        $preference = $user->notification_preferences()->where('notification_type', 'notice_date')->first();
        $delayDays = $preference->notification_delay_days;

        if ($preference && $preference->enabled && $contract->notice_date?->toDateString() > Carbon::now()->toDateString()) {

            $notification = [
                'status' => ScheduledNotificationStatusEnum::PENDING->value,
                'scheduled_at' => $contract->notice_date->subDays($delayDays),
                'notification_type' => 'notice_date',
                'recipient_name' => $user->fullName,
                'recipient_email' => $user->email,
                'data' => [
                    'subject' => $contract->name,
                    'renewal_type' => $contract->renewal_type,
                    'provider' => $contract->provider->name,
                    'end_date' => $contract->end_date,
                    'notice_date' => $contract->notice_date,
                    'link' => route('tenant.contracts.show', $contract->id)
                ]
            ];

            $createdNotification = $contract->notifications()->updateOrCreate([
                'recipient_email' => $user->email,
                'status' => 'pending',
                'notification_type' => 'notice_date',
            ], [
                ...$notification,

            ]);

            $createdNotification->user()->associate($user);
            $createdNotification->save();
        }
    }


    public function createScheduleForContractEndDate(Contract $contract, User $user)
    {
        $preference = $user->notification_preferences()->where('notification_type', 'end_date')->first();
        $delayDays = $preference->notification_delay_days;

        if ($preference && $preference->enabled && $contract->end_date?->subDays($delayDays) > now()) {

            $notification = [
                'status' => ScheduledNotificationStatusEnum::PENDING->value,
                'scheduled_at' => $contract->end_date->subDays($delayDays),
                'notification_type' => 'end_date',
                'recipient_name' => $user->fullName,
                'recipient_email' => $user->email,
                'data' => [
                    'subject' => $contract->name,
                    'renewal_type' => $contract->renewal_type,
                    'provider' => $contract->provider->name,
                    'end_date' => $contract->end_date,
                    'link' => route('tenant.contracts.show', $contract->id)
                ]
            ];

            $createdNotification = $contract->notifications()->updateOrCreate([
                'recipient_email' => $user->email,
                'status' => 'pending',
                'notification_type' => 'end_date',
            ], [
                ...$notification,

            ]);

            $createdNotification->user()->associate($user);
            $createdNotification->save();
        }
    }
}
