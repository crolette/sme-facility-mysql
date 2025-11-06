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

            if (isset($intervention->planned_at) && count($notifications) === 0) {
                $this->scheduleForIntervention($intervention);
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
        // dump('--- createScheduleForPlannedAtDate');
        $preference = $user->notification_preferences()->where('notification_type', 'planned_at')->first();
        // dump($preference);

        if ($preference && $preference->enabled && $intervention->planned_at->toDateString() > Carbon::now()->toDateString()) {

            $notification = [
                'status' => ScheduledNotificationStatusEnum::PENDING->value,
                'scheduled_at' => $intervention->planned_at->subDays($preference->notification_delay_days),
                'notification_type' => 'planned_at',
                'recipient_name' => $user->fullName,
                'recipient_email' => $user->email,
                'data' => [
                    'type' => $intervention->type,
                    'subject' => $intervention->interventionable->name,
                    'priority' => $intervention->priority,
                    'planned_at' => $intervention->planned_at,
                    'description' => $intervention->description,
                    'link' => $intervention->interventionable->location_route
                ]
            ];


            $createdNotification = $intervention->notifications()->updateOrCreate(
                [
                    'recipient_email' => $user->email,
                    'status' => 'pending',
                    'notification_type' => 'planned_at',
                ],
                [
                    ...$notification,
                ]
            );

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
        // dump('--- removeScheduleForPlannedAtDate ---');
        $notifications = $intervention->notifications()->where('notification_type', 'planned_at')->where('scheduled_at', '>', now())->where('status', 'pending')->get();

        if (count($notifications) > 0)
            foreach ($notifications as $notification) {
                $notification->delete();
            }
    }

    public function removeNotificationsForOldMaintenanceManager(Intervention $intervention, User $user)
    {
        // dump('--- removeNotificationsForOldMaintenanceManager ---');
        $notifications = $intervention->notifications()->where('user_id', $user->id)->where('status', 'pending')->get();

        if (count($notifications) > 0)
            foreach ($notifications as $notification) {
                $notification->delete();
            }
    }
}
