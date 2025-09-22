<?php

namespace App\Http\Controllers\Tenants;

use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller;
use App\Models\Tenants\Intervention;
use Illuminate\Support\Facades\Auth;
use App\Events\SendInterventionToProviderEvent;

class SendInterventionController extends Controller
{
    public function store(Intervention $intervention, Request $request)
    {
        // if(Auth::cannot('update', $intervention))
        //     return ApiResponse::notAuthorized();
        
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $url = URL::temporarySignedRoute(
            'tenant.intervention.provider',
            now()->addDays(7),
            ['intervention' => $intervention->id, 'email' => $validated['email']]
        );

        event(new SendInterventionToProviderEvent($intervention, $validated['email'], $url));

        return ApiResponse::success([], 'Email sent');
    }

}
