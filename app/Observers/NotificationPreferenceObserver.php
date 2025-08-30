<?php

namespace App\Observers;

use App\Models\Tenants\UserNotificationPreference;
use App\Services\AssetNotificationSchedulingService;
use App\Services\NotificationSchedulingService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;


class NotificationPreferenceObserver implements ShouldHandleEventsAfterCommit
{
    public function updated(UserNotificationPreference $preference)
    {
        match ($preference->asset_type) {
            'contract' => app(NotificationSchedulingService::class)->updateScheduleOfUserForNotificationType($preference),
            'asset' => app(AssetNotificationSchedulingService::class)->updateScheduleOfAssetForNotificationType($preference),
        };
    }
}
