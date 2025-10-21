<?php

namespace App\Services;

use Exception;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\Tenants\User;
use App\Models\Tenants\Company;
use App\Models\Tenants\Provider;
use Illuminate\Support\Facades\DB;
use App\Jobs\CompressUserAvatarJob;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class UserService
{
    protected $tenantId;

    public function __construct()
    {
        $this->tenantId = tenancy()->tenant->id ?? null;
    }

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

            if (isset($request['role']))
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

        $file = $file[0];

        $tenantId = tenancy()->tenant->id;
        $directory = "$tenantId/users/$user->id/avatar";

        $files = Storage::disk('tenants')->files($directory);

        if (count($files) > 0) {
            $this->deleteExistingFiles($files);
        }

        $fileName = Carbon::now()->isoFormat('YYYYMMDDHHMM') . '_avatar_' . Str::slug($name, '-') . '.' . $file->extension();
        $path = Storage::disk('tenants')->putFileAs($directory, $file, $fileName);

        $user->avatar = $path;
        $user->save();

        Company::incrementDiskSize(Storage::disk('tenants')->size($user->avatar));

        Log::info('DISPATCH COMPRESS AVATAR JOB');
        CompressUserAvatarJob::dispatch($user)->onQueue('default');

        return $user;
    }

    public function compressAvatar(User $user)
    {
        Log::info('--- START COMPRESSING USER AVATAR : ' . $user->id . ' - ' . $user->avatar);

        $path = $user->avatar;

        Company::decrementDiskSize(Storage::disk('tenants')->size($user->avatar));

        $newFileName =  Str::chopEnd(basename(Storage::disk('tenants')->path($user->avatar)), ['.png', '.jpg', '.jpeg']) . '.webp';

        $image = Image::read(Storage::disk('tenants')->path($user->avatar))->scaleDown(width: 1200, height: 1200)
            ->toWebp(quality: 75);

        $saved = Storage::disk('tenants')->put("$this->tenantId/users/$user->id/avatar/$newFileName", $image);

        $user->update(
            [
                'avatar' => "$this->tenantId/users/$user->id/avatar/$newFileName"
            ]
        );

        if ($saved)
            Storage::disk('tenants')->delete($path);

        Company::incrementDiskSize(Storage::disk('tenants')->size($user->avatar));

        Log::info('--- END COMPRESSING USER AVATAR : ' . $user->id . ' - ' . $user->avatar);
    }

    public function deleteExistingFiles($files)
    {
        foreach ($files as $file) {
            Company::decrementDiskSize(Storage::disk('tenants')->size($file));
            Storage::disk('tenants')->delete($file);
        }
    }

    public function deleteAvatar($user)
    {
        Company::decrementDiskSize(Storage::disk('tenants')->size($user->avatar));
        Storage::disk('tenants')->delete($user->avatar);

        $user->avatar = null;
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
