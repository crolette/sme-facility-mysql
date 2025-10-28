<?php

namespace App\Services;

use Exception;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\Tenants\User;
use App\Models\Tenants\Asset;
use App\Models\Tenants\Document;
use App\Models\Tenants\Provider;
use Illuminate\Support\Facades\Auth;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Models\Tenants\UserNotificationPreference;

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
                    $user->notification_preferences()->updateOrCreate([
                        'user_id' => $user->id,
                        'asset_type' => $assetType,
                        'notification_type' => $type,
                    ], [
                        'asset_type' => $assetType,
                        'notification_type' => $type,
                        'notification_delay_days' => 7,
                        'enabled' => true
                    ]);
                }
            }
        }

        if ($user->hasRole('Admin', 'tenant')) {

            // $assets = Asset::all();
            // dump(count($assets));
            // dump('CREATE NOTIFS FOR ADMIN');

            // dump($user->notification_preferences);

            app(NotificationSchedulingService::class)->createNotificationsForNewAdmin($user);

            // if (count($assets) > 0) {
            //     foreach ($assets as $asset) {
            //         app(AssetNotificationSchedulingService::class)->createScheduleForDepreciable($asset, $user);
            //     }
            // }
        }
    }

    /**
     * storeUserNotificationPreference
     *
     * @param  User $user
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

    /**
     * update
     *
     * @param  UserNotificationPreference $preference
     * @param  mixed $request
     * @return bool
     */
    public function update(UserNotificationPreference $preference, $request): bool
    {
        $preference->update([...$request]);

        return true;
    }
};
