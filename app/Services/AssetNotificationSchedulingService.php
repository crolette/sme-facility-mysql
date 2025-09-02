<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Tenants\User;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Contract;
use App\Models\Tenants\Maintainable;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Tenants\ScheduledNotification;
use App\Enums\ScheduledNotificationStatusEnum;
use App\Models\Tenants\UserNotificationPreference;

class AssetNotificationSchedulingService
{
    public function scheduleForAsset(Asset $asset)
    {

        $users = User::role('Admin')->get();

        if ($asset->depreciable) {

            if ($asset->maintainable->manager) {
                $this->createScheduleForDepreciable($asset, $asset->maintainable->manager);
            }

            foreach ($users as $user) {
                $this->createScheduleForDepreciable($asset, $user);
            }
        }
    }

    public function updateForAsset(Asset $asset)
    {
        $users = User::role('Admin')->get();

        if ($asset->wasChanged('depreciable') && $asset->depreciable === false) {
            // dump('updateForAsset depreciation_end_date changed to FALSE');
            $this->removeScheduleForDepreciable($asset);
        }

        if (($asset->wasChanged('depreciable') && $asset->depreciable === true) || $asset->wasChanged('depreciation_end_date')) {
            // dump('updateForAsset depreciation_end_date changed to TRUE');
            $notifications = $asset->notifications()->where('notification_type', 'depreciation_end_date')->where('scheduled_at', '>', now())->get();

            if (count($notifications)) {
                $this->updateScheduleForDepreciable($asset, $notifications);
            } else {
                if ($asset->maintainable->manager) {
                    // dump('--- ASSET MAINTAINABLE MANAGER ---');
                    $this->createScheduleForDepreciable($asset, $asset->maintainable->manager);
                }

                foreach ($users as $user) {
                    $this->createScheduleForDepreciable($asset, $user);
                }
            }
        }
    }

    public function updateScheduleForDepreciable(Asset $asset, Collection $notifications)
    {
        foreach ($notifications as $notification) {
            // changer scheduled_at en fonction de la nouvelle date de maintenance et en fonction des préférences utilisateurs
            $notificationPreference = $notification->user->notification_preferences()->where('notification_type', 'depreciation_end_date')->first();
            if ($asset->depreciation_end_date->subDays($notificationPreference->notification_delay_days) < now())
                continue;

            $notification->update(['scheduled_at' => $asset->depreciation_end_date->subDays($notificationPreference->notification_delay_days)]);
        }
    }

    public function createScheduleForDepreciable(Asset $asset, User $user)
    {
        $preference = $user->notification_preferences()->where('notification_type', 'depreciation_end_date')->first();
        $delay = $preference->notification_delay_days;



        if ($preference && $preference->enabled && $asset->depreciation_end_date->subDays($delay) > now()) {
            // if ($preference && $preference->enabled && $asset->depreciation_end_date->subDays($delay) < now()) {

            $notification = [
                'status' => ScheduledNotificationStatusEnum::PENDING->value,
                'notification_type' => 'depreciation_end_date',

                'data' => [
                    'subject' => $asset->name,
                    'depreciation_end_date' => $asset->depreciation_end_date
                ]
            ];

            $createdNotification = $asset->notifications()->updateOrCreate(
                [
                    'recipient_email' => $user->email,
                    'notification_type' => 'depreciation_end_date',
                ],
                [
                    ...$notification,
                    'recipient_name' => $user->fullName,
                    'recipient_email' => $user->email,
                    'scheduled_at' => $asset->depreciation_end_date->subDays($delay),
                ]
            );

            $createdNotification->user()->associate($user);
            $createdNotification->save();
        }
    }


    public function removeScheduleForDepreciable(Asset $asset)
    {
        // dump('--- removeScheduleForEndWarrantyDate ---');
        $notifications = $asset->notifications()->where('notification_type', 'depreciation_end_date')->where('scheduled_at', '>', now())->get();

        if (count($notifications) > 0)
            foreach ($notifications as $notification) {
                // dump('--- DELETE NOTIF ---');
                // dump($notification->id);
                $notification->delete();
            }
    }
}
