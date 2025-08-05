<?php

namespace App\Http\Controllers\API\V1;

use Illuminate\Support\Str;
use App\Helpers\ApiResponse;
use App\Models\Tenants\User;
use App\Services\UserService;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Support\Facades\Password;
use App\Http\Requests\Tenant\UserRequest;

class APIUserController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {}

    public function show(User $user)
    {
        return ApiResponse::success($user->load('provider:id,name'));
    }

    public function store(UserRequest $request)
    {
        Debugbar::info($request->validated());
        try {

            DB::beginTransaction();

            $user = new User($request->validated());

            if ($request->validated('can_login') === true) {
                $password = Str::password(12);
                $user->password = Hash::make($password);
            }

            if ($request->validated('avatar'))
                $user = $this->userService->uploadAndAttachAvatar($user, $request->validated('avatar'), $request->validated('first_name') . ' ' . $request->validated('last_name'));

            if ($request->validated('provider_id'))
                $user = $this->userService->attachProvider($user, $request->validated('provider_id'));

            $user->save();

            DB::commit();

            return ApiResponse::success(['password' => $password ?? null], 'User created');
        } catch (Exception $e) {
            DB::rollback();
            return ApiResponse::error($e->getMessage());
        }

        return ApiResponse::error();
    }

    public function update(UserRequest $request, User $user)
    {

        Debugbar::info($request->validated());
        try {

            DB::beginTransaction();

            $user->update($request->safe()->except('avatar'));

            if ($request->validated('provider_id'))
                $user = $this->userService->attachProvider($user, $request->validated('provider_id'));

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

        // If the password was successfully reset, we will redirect the user back to
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
        $user->delete();
        return ApiResponse::success('', 'User deleted');
    }
};
