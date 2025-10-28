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
            $this->removeScheduleForDepreciable($asset);
        }

        if (($asset->wasChanged('depreciable') && $asset->depreciable === true) || ($asset->wasChanged('depreciation_end_date') && $asset->depreciation_end_date > Carbon::now()->toDateString())) {

            $notifications = $asset->notifications()->where('notification_type', 'depreciation_end_date')->where('scheduled_at', '>', now())->get();

            if (count($notifications)) {
                $this->updateScheduleForDepreciable($asset, $notifications);
            } else {
                if ($asset->maintainable->manager) {
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

            $notification->update(['scheduled_at' => $asset->depreciation_end_date->subDays($notificationPreference->notification_delay_days)]);
        }
    }

    public function createScheduleForDepreciable(Asset $asset, User $user)
    {
        $preference = $user->notification_preferences()->where('notification_type', 'depreciation_end_date')->first();
        $delay = $preference->notification_delay_days;


        if ($preference && $preference->enabled && $asset->depreciation_end_date?->toDateString() > Carbon::now()->toDateString()) {
            // if ($preference && $preference->enabled && $asset->depreciation_end_date->subDays($delay) < now()) {

            $notification = [
                'status' => ScheduledNotificationStatusEnum::PENDING->value,
                'notification_type' => 'depreciation_end_date',
                'recipient_name' => $user->fullName,
                'recipient_email' => $user->email,
                'scheduled_at' => $asset->depreciation_end_date->subDays($delay),

                'data' => [
                    'subject' => $asset->name,
                    'reference' => $asset->reference_code,
                    'location' => $asset->location->name,
                    'depreciation_end_date' => $asset->depreciation_end_date,
                    'link' => route('tenant.assets.show', $asset->reference_code)
                ]
            ];

            $createdNotification = $asset->notifications()->updateOrCreate(
                [
                    'recipient_email' => $user->email,
                    'status' => 'pending',
                    'notification_type' => 'depreciation_end_date',
                ],
                [
                    ...$notification,
                ]
            );

            $createdNotification->user()->associate($user);
            $createdNotification->save();
        }
    }


    public function removeScheduleForDepreciable(Asset $asset)
    {
        $notifications = $asset->notifications()->where('notification_type', 'depreciation_end_date')->where('status', 'pending')->get();

        if (count($notifications) > 0)
            foreach ($notifications as $notification) {
                $notification->delete();
            }
    }

    public function removeDepreciableNotificationForUser(Asset $asset, User $user)
    {
        if ($user->hasAnyRole('Admin'))
            return;

        $notifications = $asset->notifications()->where('notification_type', 'depreciation_end_date')->where('user_id', $user->id)->where('status', 'pending')->get();

        if (count($notifications) > 0)
            foreach ($notifications as $notification) {
                $notification->delete();
            }
    }
}
