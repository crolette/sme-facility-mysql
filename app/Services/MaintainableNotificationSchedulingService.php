<?php

namespace App\Services;

use App\Enums\MaintenanceFrequency;
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
            $this->createScheduleForEndWarrantyDate($maintainable, $user);
        }
    }

    public function updateScheduleOfMaintainable(Maintainable $maintainable)
    {
        // 1. il faut rechercher toutes les  scheduled_notifications avec le notification_type et le user_id ET l'asset_type

        $users = User::role('Admin')->get();

        if (($maintainable->wasChanged('under_warranty') && $maintainable->under_warranty === true)  || ($maintainable->under_warranty === true && $maintainable->wasChanged('end_warranty_date'))) {
            $notifications = $maintainable->maintainable->notifications()->where('notification_type', 'end_warranty_date')->where('scheduled_at', '>', now())->get();

            if (count($notifications)) {
                $this->updateScheduleForEndWarrantyDate($maintainable, $notifications);
            } else {
                if ($maintainable->manager) {
                    $this->createScheduleForEndWarrantyDate($maintainable, $maintainable->manager);
                }

                foreach ($users as $user) {
                    $this->createScheduleForEndWarrantyDate($maintainable, $user);
                }
            }
        };

        if ($maintainable->wasChanged('under_warranty') && $maintainable->under_warranty === false) {
            $this->removeScheduleForEndWarrantyDate($maintainable);
        };

        if ($maintainable->need_maintenance === true || ($maintainable->need_maintenance === true && $maintainable->wasChanged('next_maintenance_date'))) {

            $notifications = $maintainable->maintainable->notifications()->where('notification_type', 'next_maintenance_date')->where('scheduled_at', '>', now())->where('status', 'pending')->get();

            if (count($notifications)) {
                $this->updateScheduleForNextMaintenanceDate($maintainable, $notifications);
            } else {
                if ($maintainable->manager) {
                    $this->createScheduleForNextMaintenanceDate($maintainable, $maintainable->manager);
                }

                foreach ($users as $user) {
                    $this->createScheduleForNextMaintenanceDate($maintainable, $user);
                }
            }
        };

        if ($maintainable->need_maintenance === false) {
            $this->removeScheduleForNextMaintenanceDate($maintainable);
        };

        if ($maintainable->wasChanged('maintenance_manager_id') && $maintainable->manager) {
            $this->createScheduleForUser($maintainable, $maintainable->manager);

            app(AssetNotificationSchedulingService::class)->createScheduleForDepreciable($maintainable->maintainable, $maintainable->manager);

            // add notifications to the manager for the interventions linked to the maintainable
            $interventions = $maintainable->maintainable->interventions;
            if (count($interventions) > 0)
                foreach ($interventions as $intervention) {
                    app(InterventionNotificationSchedulingService::class)->scheduleForIntervention($intervention, $maintainable->manager);
                }
        }
    }

    public function updateScheduleForEndWarrantyDate(Maintainable $maintainable, Collection $notifications)
    {

        foreach ($notifications as $notification) {
            // changer scheduled_at en fonction de la nouvelle date de maintenance et en fonction des préférences utilisateurs
            $notificationPreference = $notification->user->notification_preferences()->where('notification_type', 'end_warranty_date')->first();

            if ($maintainable->end_warranty_date->subDays($notificationPreference->notification_delay_days) < now())
                continue;

            $notification->update(['scheduled_at' => $maintainable->end_warranty_date->subDays($notificationPreference->notification_delay_days)]);
        }
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
        $preference = $user->notification_preferences()->where('notification_type', 'next_maintenance_date')->first();

        // if ($preference && $preference->enabled && $maintainable->maintenance_frequency != MaintenanceFrequency::ONDEMAND->value) {
        if ($preference && $preference->enabled && $maintainable->next_maintenance_date->toDateString() > Carbon::now()->toDateString()) {
            $notification = [
                'status' => ScheduledNotificationStatusEnum::PENDING->value,
                'notification_type' => 'next_maintenance_date',
                'recipient_name' => $user->fullName,
                'recipient_email' => $user->email,
                'scheduled_at' => $maintainable->next_maintenance_date->subDays($preference->notification_delay_days),
                'data' => [
                    'subject' => $maintainable->name,
                    'reference' => $maintainable->maintainable->reference_code,
                    'location' => $maintainable->maintainable->location->name ?? $maintainable->maintainable->level->name ?? null,
                    'next_maintenance_date' => $maintainable->next_maintenance_date,
                    'link' => match ($maintainable->maintainable_type) {
                        'App\Models\Tenants\Site' => route('tenant.sites.show', $maintainable->maintainable->reference_code),
                        'App\Models\Tenants\Building' => route('tenant.buildings.show', $maintainable->maintainable->reference_code),
                        'App\Models\Tenants\Floor' => route('tenant.floors.show', $maintainable->maintainable->reference_code),
                        'App\Models\Tenants\Room' => route('tenant.rooms.show', $maintainable->maintainable->reference_code),
                        'App\Models\Tenants\Asset' => route('tenant.assets.show', $maintainable->maintainable->reference_code),
                        default => route('tenant.dashboard')
                    }
                ]
            ];

            $createdNotification = $maintainable->maintainable->notifications()->updateOrCreate(
                [
                    'recipient_email' => $user->email,
                    'status' => 'pending',
                    'notification_type' => 'next_maintenance_date',
                ],
                [
                    ...$notification,

                ]
            );

            $createdNotification->user()->associate($user);
            $createdNotification->save();
        }
    }

    public function createScheduleForEndWarrantyDate(Maintainable $maintainable, User $user)
    {
        $preference = $user->notification_preferences()->where('notification_type', 'end_warranty_date')->first();

        if ($preference && $preference->enabled  && $maintainable->end_warranty_date->toDateString() > Carbon::now()->toDateString()) {
            $delay = $preference->notification_delay_days;

            $notification = [
                'status' => ScheduledNotificationStatusEnum::PENDING->value,
                'notification_type' => 'end_warranty_date',
                'recipient_name' => $user->fullName,
                'recipient_email' => $user->email,
                'scheduled_at' => $maintainable->end_warranty_date->subDays($delay),
                'data' => [
                    'subject' => $maintainable->name,
                    'reference' => $maintainable->maintainable->reference_code,
                    'location' => $maintainable->maintainable->location->name ?? $maintainable->maintainable->level->name ?? null,
                    'end_warranty_date' => $maintainable->end_warranty_date,
                    'link' => match ($maintainable->maintainable_type) {
                        'App\Models\Tenants\Site' => route('tenant.sites.show', $maintainable->maintainable->reference_code),
                        'App\Models\Tenants\Building' => route('tenant.buildings.show', $maintainable->maintainable->reference_code),
                        'App\Models\Tenants\Floor' => route('tenant.floors.show', $maintainable->maintainable->reference_code),
                        'App\Models\Tenants\Room' => route('tenant.rooms.show', $maintainable->maintainable->reference_code),
                        'App\Models\Tenants\Asset' => route('tenant.assets.show', $maintainable->maintainable->reference_code),
                        default => route('tenant.dashboard')
                    }
                ]
            ];

            $createdNotification = $maintainable->maintainable->notifications()->updateOrCreate(
                [
                    'recipient_email' => $user->email,
                    'status' => 'pending',
                    'notification_type' => 'end_warranty_date',
                ],
                [
                    ...$notification,
                ]
            );

            $createdNotification->user()->associate($user);
            $createdNotification->save();
        }
    }

    public function removeScheduleForNextMaintenanceDate(Maintainable $maintainable)
    {
        $notifications = $maintainable->maintainable->notifications()->where('notification_type', 'next_maintenance_date')->where('status', 'pending')->get();

        if (count($notifications) > 0)
            foreach ($notifications as $notification) {
                $notification->delete();
            }
    }

    public function removeScheduleForEndWarrantyDate(Maintainable $maintainable)
    {
        $notifications = $maintainable->maintainable->notifications()->where('notification_type', 'end_warranty_date')->where('status', 'pending')->get();

        if (count($notifications) > 0)
            foreach ($notifications as $notification) {
                $notification->delete();
            }
    }

    public function removeNotificationsForOldMaintenanceManager(Maintainable $maintainable, User $user)
    {
        // dump('--- removeNotificationsForOldMaintenanceManager');
        // only remove notification if the user has the maintenance manager role
        if ($user->hasAnyRole('Admin'))
            return;

        $notifications = $maintainable->maintainable->notifications()->where('user_id', $user->id)->where('status', 'pending')->get();

        if (count($notifications) > 0) {
            foreach ($notifications as $notification) {
                $notification->delete();
            }
        }

        $interventions = $maintainable->maintainable->interventions;
        if (count($interventions) > 0)
            foreach ($interventions as $intervention) {
                app(InterventionNotificationSchedulingService::class)->removeNotificationsForOldMaintenanceManager($intervention, $user);
            }
    }
}
