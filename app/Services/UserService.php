<?php

namespace App\Services;

use Exception;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\Tenants\User;
use App\Models\Tenants\Provider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UserService
{
    public function create($request): User | Exception
    {
        try {
            DB::beginTransaction();
            $user = new User([...$request]);

            if (isset($request['avatar']))
                $user = $this->uploadAndAttachAvatar($user, $request['avatar'], $request['first_name'] . ' ' . $request['last_name']);

            if (isset($request['provider_id']))
                $user = $this->attachProvider($user, $request['provider_id']);

            $user->save();

            if(isset($request['role']))
                $user->assignRole($request['role']);

            DB::commit();
            return $user;
        } catch (Exception $e) {
            DB::rollBack();
            return $e;
        }
    }
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

    public function deleteAvatar($user)
    {
        Storage::disk('tenants')->delete($user->avatar);

        $user->avatar = null ;
        $user->save();
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
