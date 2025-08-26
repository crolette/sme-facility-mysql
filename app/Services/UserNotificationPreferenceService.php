<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\Tenants\Document;
use App\Models\Tenants\Provider;
use App\Models\Tenants\User;
use App\Models\Tenants\UserNotificationPreference;
use Illuminate\Support\Facades\Auth;
use Barryvdh\Debugbar\Facades\Debugbar;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class UserNotificationPreferenceService
{

    /**
     * createDefaultUserNotificationPreferences
     *
     * @param  User $user
     * @return void
     */
    public function createDefaultUserNotificationPreferences(User $user)
    {

        if ($user->hasAnyRole(['Admin', 'Maintenance Manager'])) {

            $preferences = config('notifications.notification_types');

            foreach ($preferences as $assetType => $notificationType) {
                foreach ($notificationType as $type) {

                    $user->notification_preferences()->create([
                        'asset_type' => $assetType,
                        'notification_type' => $type,
                        'notification_delay_days' => 7,
                        'enabled' => true
                    ]);
                }
            }
        }
    }

    /**
     * storeUserNotificationPreference
     *
     * @param  mixed $user
     * @param  mixed $request
     * @return bool
     */
    public function create(User $user, $request): bool
    {

        $user->notification_preferences()->create([
            'asset_type' => $request['asset_type'],
            'notification_type' => $request['notification_type'],
            'notification_delay_days' => $request['notification_delay_days'],
            'enabled' => $request['enabled']
        ]);


        return true;
    }

    public function update(UserNotificationPreference $preference, $request): bool
    {
        $preference->update([...$request]);

        return true;
    }
};
