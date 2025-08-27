<?php

namespace App\Observers;

use App\Models\Tenants\UserNotificationPreference;
use App\Services\NotificationSchedulingService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;


class NotificationPreferenceObserver implements ShouldHandleEventsAfterCommit
{
    public function updated(UserNotificationPreference $preference)
    {
        app(NotificationSchedulingService::class)->updateScheduleOfUserForNotificationType($preference);
    }
}
