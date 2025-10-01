<?php

namespace App\Http\Controllers\API\V1;

use App\Helpers\ApiResponse;
use App\Models\Tenants\User;
use App\Services\UserService;
use App\Http\Controllers\Controller;
use Barryvdh\Debugbar\Facades\Debugbar;
use App\Http\Requests\Tenant\ImageUploadRequest;

class APIUploadProfilePictureController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {}

    public function store(ImageUploadRequest $request, User $user)
    {
        $user = $this->userService->uploadAndAttachAvatar($user, $request->validated('pictures'), $user->fullName);
        $user->save();
        return ApiResponse::success('', 'Profile picture uploaded');
    }

    public function destroy(User $user)
    {
        $this->userService->deleteAvatar($user);
        return ApiResponse::success('', 'Profile picture deleted');
    }
};
