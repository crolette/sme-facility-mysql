<?php

namespace App\Observers;

use App\Models\Tenants\User;
use App\Services\UserService;
use App\Models\Tenants\Contract;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Barryvdh\Debugbar\Facades\Debugbar;
use App\Services\NotificationSchedulingService;
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

    // Ajoutez aussi les autres événements pour voir
    public function updated(User $user)
    {
        // \Log::info('UserObserver::updated called', ['user_id' => $user->id]);
    }

    public function saving(User $user)
    {
        // \Log::info('UserObserver::saving called', ['user_id' => $user->id]);
    }
}
