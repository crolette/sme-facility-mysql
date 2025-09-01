<?php

namespace App\Observers;

use App\Services\NotificationSchedulingService;
use App\Models\Tenants\UserNotificationPreference;
use App\Services\AssetNotificationSchedulingService;
use App\Services\MaintainableNotificationSchedulingService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;


class NotificationPreferenceObserver implements ShouldHandleEventsAfterCommit
{
    public function updated(UserNotificationPreference $preference)
    {
        app(NotificationSchedulingService::class)->updateScheduleOfUserForNotificationType($preference);
    }
}
