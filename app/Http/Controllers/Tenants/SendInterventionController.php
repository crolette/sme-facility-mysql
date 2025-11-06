<?php

namespace App\Http\Controllers\Tenants;

use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller;
use App\Models\Tenants\Intervention;
use Illuminate\Support\Facades\Auth;
use Barryvdh\Debugbar\Facades\Debugbar;
use App\Events\SendInterventionToProviderEvent;
use App\Models\Tenants\Provider;
use App\Models\Tenants\User;
use Illuminate\Support\Facades\Validator;

class SendInterventionController extends Controller
{
    public function store(Intervention $intervention, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'provider_id' => 'nullable|exists:providers,id|required_without:user_id',
            'user_id' => 'nullable|exists:users,id|required_without:provider_id'
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Missing infos', $validator->errors());
        }

        $validated = $validator->validated();

        if (isset($validated['provider_id'])) {
            $intervention->assignable()->associate(Provider::find($validated['provider_id']))->save();
        }
        if (isset($validated['user_id'])) {
            $intervention->assignable()->associate(User::find($validated['user_id']))->save();
        }

        $url = URL::temporarySignedRoute(
            'tenant.intervention.provider',
            now()->addDays(7),
            ['intervention' => $intervention->id, 'email' => $validated['email']]
        );

        event(new SendInterventionToProviderEvent($intervention, $validated['email'], $url));

        return ApiResponse::success([], 'Email sent');
    }
}
