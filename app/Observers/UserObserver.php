<?php

namespace App\Observers;

use App\Models\Tenants\Contract;
use App\Models\Tenants\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Barryvdh\Debugbar\Facades\Debugbar;
use App\Services\NotificationSchedulingService;
use App\Services\UserNotificationPreferenceService;
use App\Services\UserService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class UserObserver implements ShouldHandleEventsAfterCommit
{
    public function created(User $user)
    {
        app(UserNotificationPreferenceService::class)->createDefaultUserNotificationPreferences($user);
    }
}
