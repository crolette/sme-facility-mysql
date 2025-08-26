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

class UserService
{
    public function uploadAndAttachAvatar(User $user, $file, string $name): User
    {

        $tenantId = tenancy()->tenant->id;
        $directory = "$tenantId/users/$user->id/avatar";

        $files = Storage::disk('tenants')->files($directory);

        if (count($files) > 0) {
            $this->deleteExistingFiles($files);
        }


        $fileName = Carbon::now()->isoFormat('YYYYMMDDHHMM') . '_avatar_' . Str::slug($name, '-') . '.' . $file->extension();
        $path = Storage::disk('tenants')->putFileAs($directory, $file, $fileName);

        $user->avatar = $path;

        return $user;
    }

    public function deleteExistingFiles($files)
    {
        foreach ($files as $file) {
            Storage::disk('tenants')->delete($file);
        }
    }

    public function attachProvider(User $user, int $providerId): User
    {
        if ($user->provider_id === $providerId)
            return $user;

        if ($user->provider_id !== $providerId) {
            $user = $this->detachProvider($user);
        }


        $provider = Provider::find($providerId);
        $user->provider()->associate($provider);

        return $user;
    }

    public function detachProvider(User $user): User
    {
        $user->provider()->disassociate()->save();
        return $user;
    }


    public function createDefaultUserNotificationPreferences(User $user)
    {

        if ($user->hasAnyRole(['Admin', 'Maintenance Manager'])) {

            $preferences = config('notifications');

            // dump($preferences);
            foreach ($preferences as $notificationTypes) {
                // dump($notificationTypes);
                foreach ($notificationTypes as $assetType => $notificationType) {
                    // dump($assetType);
                    // dump($notificationType);
                    foreach ($notificationType as $type) {

                        // dump($type);
                        $user->notification_preferences()->create([
                            'asset_type' => $assetType,
                            'notification_type' => $type,
                            'notification_delay_days' => 7,
                            'enabled' => true
                        ]);
                    }
                }
            };
        }
    }
};
