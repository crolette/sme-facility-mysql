<?php

namespace App\Services;

use App\Enums\InterventionStatus;
use Carbon\Carbon;
use App\Models\Tenants\User;
use App\Models\Tenants\Contract;
use App\Models\Tenants\Intervention;
use Barryvdh\Debugbar\Facades\Debugbar;
use App\Models\Tenants\ScheduledNotification;
use App\Enums\ScheduledNotificationStatusEnum;
use App\Models\Tenants\UserNotificationPreference;

class InterventionNotificationSchedulingService
{
    public function scheduleForIntervention(Intervention $intervention)
    {
        $notificationTypes = collect(config('notifications.notification_types.intervention'));

        if ($intervention->planned_at && in_array($intervention->status, [InterventionStatus::PLANNED, InterventionStatus::IN_PROGRESS, InterventionStatus::WAITING_PARTS])) {
            if ($intervention->interventionable->manager) {
                $this->createScheduleForPlannedAtDate($intervention, $intervention->interventionable->manager);
            }

            $users = User::role('Admin')->get();
            foreach ($users as $user) {
                $this->createScheduleForPlannedAtDate($intervention, $user);
            }
        }
    }

    public function updateScheduleForIntervention(Intervention $intervention)
    {
        // 1. reprendre les notifications liées au contrat
        // 2. reprendre les utilisateurs admin avec leur préférence
        // 3. boucler sur chaque user et actualiser avec les préférences

        if ($intervention->wasChanged('planned_at')) {

            $notifications = $intervention->notifications()->where('notification_type', 'planned_at')->get();

            foreach ($notifications as $notification) {
                $this->updateScheduleForPlannedAtDate($intervention, $notification);
            }
        }

        if ($intervention->wasChanged('status')) {
            if ($intervention->status === InterventionStatus::CANCELLED || $intervention->status === InterventionStatus::COMPLETED) {
                $this->removeScheduleForPlannedAtDate($intervention);
            }
        }
    }

    public function createScheduleForPlannedAtDate(Intervention $intervention, User $user)
    {
        $preference = $user->notification_preferences()->where('notification_type', 'planned_at')->first();

        if ($preference && $preference->enabled && $intervention->planned_at->subDays($preference->notification_delay_days) > now()) {

            $notification = [
                'status' => ScheduledNotificationStatusEnum::PENDING->value,
                'data' => [
                    'subject' => 'test',
                    'intervention_date' => $intervention->planned_at,
                    'link' => route('tenant.interventions.show', $intervention->id)
                ]
            ];

            Debugbar::info('createScheduleForPlannedAtDate', $notification);

            $createdNotification = $intervention->notifications()->updateOrCreate(
                [
                    'recipient_email' => $user->email,
                    'notification_type' => 'planned_at',
                ],
                [
                    ...$notification,
                    'scheduled_at' => $intervention->planned_at->subDays($preference->notification_delay_days),
                    'notification_type' => 'planned_at',
                    'recipient_name' => $user->fullName,
                    'recipient_email' => $user->email,
                ]
            );

            // dump($createdNotification);

            $createdNotification->user()->associate($user);
            $createdNotification->save();
        }
    }

    public function updateScheduleForPlannedAtDate(Intervention $intervention, ScheduledNotification $notification)
    {

        $newDate = $intervention->planned_at->subDays($notification->user->notification_preferences()->where('notification_type', 'notice_date')->first()->notification_delay_days);

        if ($newDate > now())
            $notification->update(['scheduled_at' => $newDate]);
    }

    public function removeScheduleForPlannedAtDate(Intervention $intervention)
    {
        // dump('--- removeScheduleForEndWarrantyDate ---');
        $notifications = $intervention->notifications()->where('notification_type', 'planned_at')->where('scheduled_at', '>', now())->get();

        if (count($notifications) > 0)
            foreach ($notifications as $notification) {
                // dump('--- DELETE NOTIF ---');
                // dump($notification->id);
                $notification->delete();
            }
    }

    public function removeNotificationsForOldMaintenanceManager(Intervention $intervention, User $user)
    {
        $notifications = $intervention->notifications()->where('user_id', $user->id)->get();

        if (count($notifications) > 0)
            foreach ($notifications as $notification) {
                $notification->delete();
            }
    }
}
