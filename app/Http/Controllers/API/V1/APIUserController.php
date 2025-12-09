<?php

namespace App\Http\Controllers\API\V1;

use Exception;
use Illuminate\Support\Str;
use App\Helpers\ApiResponse;
use App\Models\Tenants\User;
use App\Services\UserService;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Support\Facades\Password;
use App\Http\Requests\Tenant\UserRequest;
use App\Services\AssetNotificationSchedulingService;
use App\Services\NotificationSchedulingService;
use App\Services\TenantLimits;
use App\Services\UserNotificationPreferenceService;

class APIUserController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {}

    public function show(User $user)
    {
        if (Auth::user()->cannot('view', $user)) {
            return ApiResponse::notAuthorized();
        }

        try {
            return ApiResponse::success($user->load('provider:id,name'));
        } catch (Exception $e) {
            Log::info('Error during retrieving user : ' . $e->getMessage());
            return ApiResponse::error('Error during retrieving user');
        }
    }

    public function store(UserRequest $request)
    {
        if ($request->user()->cannot('create', User::class)) {
            return ApiResponse::notAuthorized();
        }

        if ($request->validated('can_login') && !TenantLimits::canCreateLoginableUser()) {
            return ApiResponse::notAuthorized();
        }

        try {
            $user = $this->userService->create($request->validated());

            if ($user->hasAnyRole('Admin', 'Maintenance Manager')) {
                Password::sendResetLink(
                    $request->only('email')
                );

                TenantLimits::setUsersUsage();
            }



            return ApiResponse::successFlash([], 'User created');
        } catch (Exception $e) {
            Log::info($e->getMessage());
            return ApiResponse::error($e->getMessage());
        }

        return ApiResponse::error();
    }

    public function update(UserRequest $request, User $user)
    {

        if ($request->user()->cannot('update', $user)) {
            return ApiResponse::notAuthorized();
        }

        try {

            DB::beginTransaction();

            $previousRoles = $user->getRoleNames();
            $user->syncRoles($request->validated('role'));
            $newRoles = $user->getRoleNames();

            if (!$user->can_login && $request->validated('can_login') === true && $user->hasAnyRole('Admin', 'Maintenance Manager')) {
                app(UserNotificationPreferenceService::class)->createDefaultUserNotificationPreferences($user);
                Password::sendResetLink(
                    $request->only('email')
                );
            }

            if ($user->can_login && $request->validated('can_login') === false) {
                app(UserNotificationPreferenceService::class)->deleteNotifications($user);
            }

            if ([...$previousRoles] !== [...$newRoles] && $newRoles->contains('Maintenance Manager')) {
                app(NotificationSchedulingService::class)->removeNotificationsForOldAdminRole($user);
            }

            if ([...$previousRoles] !== [...$newRoles] && $newRoles->contains('Admin')) {
                app(NotificationSchedulingService::class)->createNotificationsForNewAdmin($user);
            }

            $user = $this->userService->update($user, $request->safe()->except('avatar'));

            // if ($user->provider && !$request->validated('provider_id')) {
            //     $user = $user->provider()->disassociate();
            // }

            // if ($request->validated('provider_id'))
            //     $user = $this->userService->attachProvider($user, $request->validated('provider_id'));

            $user->save();

            DB::commit();

            return ApiResponse::success('', 'User updated');
        } catch (Exception $e) {
            DB::rollback();
            return ApiResponse::error($e->getMessage());
        }

        return ApiResponse::error();
    }

    public function updatePassword(Request $request, User $user)
    {

        $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $status = Password::reset(
            $request->only('password', 'password_confirmation'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                // event(new PasswordReset($user));
            }
        );

        // If the pas sword was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        if ($status == Password::PasswordReset) {
            // return to_route('central.login')->with('status', __($status));
        }

        // throw ValidationException::withMessages([
        //     'email' => [__($status)],
        // ]);
    }

    public function destroy(User $user)
    {
        if (Auth::user()->cannot('delete', $user)) {
            return ApiResponse::notAuthorized();
        }

        if ($user->can_login) {
            TenantLimits::setUsersUsage();
        }

        try {
            $user->delete();
            return ApiResponse::successFlash('', 'User deleted');
        } catch (Exception $e) {
            Log::info('Error during deleting user : ' . $e->getMessage());
            return ApiResponse::error('Error during deleting user');
        }
    }
};
