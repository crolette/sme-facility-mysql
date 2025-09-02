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

class AssetNotificationSchedulingService
{
    public function scheduleForAsset(Asset $asset)
    {
        // $notificationTypes = collect(config('notifications.notification_types.asset'));

        // warranty date : maintainable under warranty
        // depcriation : depreciable (true/false)
        // maintenance : maintainable : need_maintenance

        $users = User::role('Admin')->get();

        if ($asset->depreciable) {

            $notification = [
                'status' => ScheduledNotificationStatusEnum::PENDING->value,
                'notification_type' => 'depreciation_end_date',

                'data' => [
                    'subject' => $asset->name,
                    'depreciation_end_date' => $asset->depreciation_end_date
                ]
            ];

            if ($asset->maintainable->manager) {
                $manager = $asset->maintainable->manager;
                $preference = $manager->notification_preferences()->where('notification_type', 'depreciation_end_date')->first();

                if ($preference && $preference->enabled) {
                    $delay = $preference->notification_delay_days;

                    $createdNotification = $asset->notifications()->create(
                        [
                            ...$notification,
                            'recipient_name' => $manager->fullName,
                            'recipient_email' => $manager->email,
                            'scheduled_at' => $asset->depreciation_end_date->subDays($delay),
                        ]
                    );

                    $createdNotification->user()->associate($manager);
                    $createdNotification->save();
                }
            }

            foreach ($users as $user) {
                $preference = $user->notification_preferences()->where('notification_type', 'depreciation_end_date')->first();

                if (!$preference || !$preference->enabled)
                    continue;

                $delay = $preference->notification_delay_days;

                $createdNotification = $asset->notifications()->create(
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




        //

    }

    public function updateForAsset(Asset $asset)
    {
        dump('updateForAsset');
        // dump($asset->maintainable->wasChanged('next_maintenance_date'));
        // dump($asset, $asset->maintainable->wasChanged('maintenance_manager_id'), $asset->maintainable->getOriginal('maintenance_manager_id'));

        if ($asset->wasChanged('depreciable') && $asset->depreciable === false) {
            dump('updateForAsset depreciation_end_date changed to FALSE');
            $this->removeScheduleForDepreciable($asset);
        }

        if ($asset->wasChanged('depreciable') && $asset->depreciable === true) {
            dump('updateForAsset depreciation_end_date changed to TRUE');
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
