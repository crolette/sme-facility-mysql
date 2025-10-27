<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Tenants\User;
use App\Models\Tenants\Contract;
use App\Enums\ContractStatusEnum;
use Barryvdh\Debugbar\Facades\Debugbar;
use App\Models\Tenants\ScheduledNotification;
use App\Enums\ScheduledNotificationStatusEnum;
use App\Models\Tenants\UserNotificationPreference;

class ContractNotificationSchedulingService
{
    public function scheduleForContract(Contract $contract)
    {
        if ($contract->status === ContractStatusEnum::ACTIVE) {
            $users = User::role('Admin')->get();

            foreach ($users as $user) {
                $this->createScheduleForContractEndDate($contract, $user);
                $this->createScheduleForContractNoticeDate($contract, $user);
            }

            // Create notifications for related assets/locations with manager
            $contract = Contract::with(['assets', 'sites', 'rooms', 'floors', 'buildings'])->find($contract->id);
            $contractables = $contract->contractables();
            // dump(count($contractables));
            $contractables->each(function ($contractable) use ($contract) {
                // dump('contractables');
                if ($contractable->manager) {
                    // dump($contractable->manager);
                    $this->createScheduleForContractNoticeDate($contract, $contractable->manager);
                    $this->createScheduleForContractEndDate($contract, $contractable->manager);
                }
            });
        }
    }

    public function updateScheduleForContract(Contract $contract)
    {
        // 1. reprendre les notifications liées au contrat
        // 2. reprendre les utilisateurs admin avec leur préférence
        // 3. boucler sur chaque user et actualiser avec les préférences

        dump('Contract Service: updateScheduleForContract');
        dump($contract->getChanges());

        $contract = Contract::with(['assets', 'sites', 'rooms', 'floors', 'buildings'])->find($contract->id);
        $contractables = $contract->contractables();
        // dump(count($contractables));


        $users = User::role('Admin')->get();

        if ($contract->wasChanged('end_date') && $contract->end_date?->toDateString() > Carbon::now()->toDateString()) {
            // dump('Contract End Date Changed');
            $notifications = $contract->notifications()->where('notification_type', 'end_date')->where('status', 'pending')->get();
            foreach ($notifications as $notification) {
                $this->updateScheduleForContractEndDate($contract, $notification);
            }
        }

        dump($contract->notice_date->toDateString());
        dump($contract->wasChanged(['notice_date', 'notice_period', 'end_date']));
        dump($contract->wasChanged('notice_period'));
        dump($contract->notice_date?->toDateString() > Carbon::now()->toDateString());


        if ($contract->wasChanged('notice_date') && $contract->notice_date?->toDateString() > Carbon::now()->toDateString()) {
            $notifications = $contract->notifications()->where('notification_type', 'notice_date')->where('status', 'pending')->get();



            if (count($notifications)) {
                foreach ($notifications as $notification) {
                    $this->updateScheduleForContractNoticeDate($contract, $notification);
                }
            } else {
                foreach ($users as $user) {
                    $this->createScheduleForContractNoticeDate($contract, $user);
                }

                $contractables->each(function ($contractable) use ($contract) {
                    if ($contractable->manager) {
                        $this->createScheduleForContractNoticeDate($contract, $contractable->manager);
                    }
                });
            }
        }

        if ($contract->wasChanged('status') && $contract->status === ContractStatusEnum::ACTIVE) {
            $this->scheduleForContract($contract);
        }

        if ($contract->wasChanged('status') && in_array($contract->status, [ContractStatusEnum::EXPIRED, ContractStatusEnum::CANCELLED])) {
            $this->removeAllPendingNotificationsForAContract($contract);
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
        // dump('createScheduleForContractNoticeDate');
        // dump($contract->start_date?->toDateString());
        // dump($contract->notice_date?->toDateString());
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

        if ($preference && $preference->enabled && $contract->end_date?->toDateString() > Carbon::now()->toDateString()) {

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

    public function removeAllPendingNotificationsForAContract(Contract $contract)
    {
        $notifications = $contract->notifications()->where('status', 'pending')->get();

        if (count($notifications)) {
            foreach ($notifications as $notification) {
                $notification->delete();
            }
        }
    }

    public function removeNotificationsForNoticeDate(Contract $contract)
    {
        $notifications = $contract->notifications()->where('notification_type', 'notice_date')->where('status', 'pending')->get();

        if (count($notifications)) {
            foreach ($notifications as $notification) {
                $notification->delete();
            }
        }
    }

    public function removeNotificationsForEndDate(Contract $contract)
    {
        $notifications = $contract->notifications()->where('notification_type', 'end_date')->where('status', 'pending')->get();

        if (count($notifications)) {
            foreach ($notifications as $notification) {
                $notification->delete();
            }
        }
    }

    public function removeNotificationsForOldMaintenanceManager(Contract $contract, User $user)
    {
        // dump('--- removeNotificationsForOldMaintenanceManager ---');
        $notifications = $contract->notifications()->where('user_id', $user->id)->where('status', 'pending')->get();

        if (count($notifications) > 0)
            foreach ($notifications as $notification) {
                $notification->delete();
            }
    }
}
