<?php

namespace App\Http\Middleware;

use App\Models\Users;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckOwner
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->redirectTo('/');
        }

        $isBannedOrNotVerify = $user->is_banned || !$user->is_verify;
        if ($isBannedOrNotVerify) {
            return response()->redirectTo('/');
        }

        if ($user->role != Users::ROLE_OWNER) {
            return response()->redirectTo('/');
        }

        return $next($request);
    }
}
