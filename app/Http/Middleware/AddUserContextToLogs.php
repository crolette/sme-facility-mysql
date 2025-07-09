<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AddUserContextToLogs
{
    /**
     * Add User Context to every log to know which user "throw" the error
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            Log::withContext([
                'user_id' => $user->id,
                'user_email' => $user->email,
                'user_name' => $user->name,
                // 'user_roles' => $user->getRoleNames(),
            ]);
        } else {
            Log::withContext([
                'user' => 'guest',
            ]);
        }

        return $next($request);
    }
}
