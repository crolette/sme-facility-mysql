<?php

namespace App\Observers;

use App\Models\Tenants\User;
use App\Services\UserNotificationPreferenceService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class UserObserver implements ShouldHandleEventsAfterCommit
{
    public function created(User $user)
    {
        if ($user->notification_preferences()->exists()) {
            return;
        }
        app(UserNotificationPreferenceService::class)->createDefaultUserNotificationPreferences($user);
    }

    public function updated(User $user)
    {
        if ($user->notification_preferences()->exists()) {
            return;
        }
        app(UserNotificationPreferenceService::class)->createDefaultUserNotificationPreferences($user);
    }

    public function saving(User $user)
    {
        // \Log::info('UserObserver::saving called', ['user_id' => $user->id]);
    }
}
