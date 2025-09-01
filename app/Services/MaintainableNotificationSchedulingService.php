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
use Illuminate\Database\Eloquent\Collection;

class MaintainableNotificationSchedulingService
{

    public function createScheduleForMaintainable(Maintainable $maintainable)
    {
        $users = User::role('Admin')->get();

        if ($maintainable->manager) {
            $this->createScheduleForUser($maintainable, $maintainable->manager);
        }

        foreach ($users as $user) {
            $this->createScheduleForUser($maintainable, $user);
        }
    }

    private function createScheduleForUser(Maintainable $maintainable, User $user)
    {
        if ($maintainable->need_maintenance) {
            $this->createScheduleForNextMaintenanceDate($maintainable, $user);
        }

        if ($maintainable->under_warranty) {
            $preference = $user->notification_preferences()->where('notification_type', 'end_warranty_date')->first();

            if ($preference && $preference->enabled) {

                $notification = [
                    'status' => ScheduledNotificationStatusEnum::PENDING->value,
                    'notification_type' => 'end_warranty_date',

                    'data' => [
                        'subject' => $maintainable->name,
                        'end_warranty_date' => $maintainable->end_warranty_date
                    ]
                ];

                $delay = $preference->notification_delay_days;

                $createdNotification = $maintainable->maintainable->notifications()->create(
                    [
                        ...$notification,
                        'recipient_name' => $user->fullName,
                        'recipient_email' => $user->email,
                        'scheduled_at' => $maintainable->end_warranty_date->subDays($delay),
                    ]
                );

                $createdNotification->user()->associate($user);
                $createdNotification->save();
            }
        }
    }

    public function updateScheduleOfMaintainable(Maintainable $maintainable)
    {
        // 1. il faut rechercher toutes les  scheduled_notifications avec le notification_type et le user_id ET l'asset_type
        $maintainable->refresh();
        $users = User::role('Admin')->get();

        if ($maintainable->wasChanged('under_warranty') && $maintainable->under_warranty === true) {
            dump('update maintainable end warranty date');
        };

        if (($maintainable->wasChanged('need_maintenance') && $maintainable->need_maintenance === true) || $maintainable->wasChanged('next_maintenance_date')) {
            // dump('--- update maintainable next_maintenance_date to TRUE ---');

            $notifications = $maintainable->maintainable->notifications()->where('notification_type', 'next_maintenance_date')->where('scheduled_at', '>', now())->get();
            // dump('notifications : ' . count($notifications));
            if (count($notifications)) {
                $this->updateScheduleForNextMaintenanceDate($maintainable, $notifications);
            } else {
                if ($maintainable->manager) {

                    // dump('manager id : ' . $maintainable->manager->id);
                    $this->createScheduleForNextMaintenanceDate($maintainable, $maintainable->manager);
                }

                foreach ($users as $user) {
                    $this->createScheduleForNextMaintenanceDate($maintainable, $user);
                }
            }
        };

        if ($maintainable->wasChanged('need_maintenance') && $maintainable->need_maintenance === false) {
            dump('--- update maintainable next_maintenance_date to FALSE ---');
            $this->removeScheduleForNextMaintenanceDate($maintainable);
        };

        if ($maintainable->wasChanged('maintenance_manager_id') && $maintainable->manager) {
            dump('--- update maintainable maintenance_manager_id ---');
            $this->createScheduleForUser($maintainable, $maintainable->manager);
        };
    }

    public function updateScheduleForNextMaintenanceDate(Maintainable $maintainable, Collection $notifications)
    {

        foreach ($notifications as $notification) {
            // changer scheduled_at en fonction de la nouvelle date de maintenance et en fonction des préférences utilisateurs
            $notificationPreference = $notification->user->notification_preferences()->where('notification_type', 'next_maintenance_date')->first();

            $notification->update(['scheduled_at' => $maintainable->next_maintenance_date->subDays($notificationPreference->notification_delay_days)]);
        }
    }

    public function createScheduleForNextMaintenanceDate(Maintainable $maintainable, User $user)
    {
        // dump('createScheduleForNextMaintenanceDate');
        // dump('maintainable next_maintenance_date : ' . $maintainable->next_maintenance_date);

        $preference = $user->notification_preferences()->where('notification_type', 'next_maintenance_date')->first();

        if ($preference && $preference->enabled) {
            $notification = [
                'status' => ScheduledNotificationStatusEnum::PENDING->value,
                'notification_type' => 'next_maintenance_date',

                'data' => [
                    'subject' => $maintainable->name,
                    'next_maintenance_date' => $maintainable->next_maintenance_date
                ]
            ];


            $delay = $preference->notification_delay_days;

            $createdNotification = $maintainable->maintainable->notifications()->create(
                [
                    ...$notification,
                    'recipient_name' => $user->fullName,
                    'recipient_email' => $user->email,
                    'scheduled_at' => $maintainable->next_maintenance_date->subDays($delay),
                ]
            );

            $createdNotification->user()->associate($user);
            $createdNotification->save();
        }
    }

    public function removeScheduleForNextMaintenanceDate(Maintainable $maintainable)
    {
        $notifications = $maintainable->maintainable->notifications()->where('notification_type', 'next_maintenance_date')->where('scheduled_at', '>', now())->get();

        if (count($notifications) > 0)
            $notifications->delete();
    }

    public function removeNotificationsForOldMaintenanceManager(Maintainable $maintainable, User $user)
    {
        $notifications = $maintainable->maintainable->notifications()->where('user_id', $user->id)->get();

        if (count($notifications) > 0)
            $notifications->delete();
    }
}
