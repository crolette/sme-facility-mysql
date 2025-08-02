<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\Tenants\Document;
use App\Models\Tenants\Provider;
use App\Models\Tenants\User;
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
            $this->deleteExistingQR($files);
        }


        $fileName = Carbon::now()->isoFormat('YYYYMMDD') . '_avatar_' . Str::slug($name, '-') . '.' . $file->extension();
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
};
