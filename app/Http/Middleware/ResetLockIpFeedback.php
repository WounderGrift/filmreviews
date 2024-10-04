<?php

namespace App\Http\Middleware;

use App\Models\LockIpFeedback;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResetLockIpFeedback
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $lockIps = LockIpFeedback::get();
        foreach ($lockIps as $lockIp) {
            if ($date = $lockIp?->updated_at) {
                $date = Carbon::parse($date);

                if ($date->diffInHours(Carbon::now()) >= 24)
                    $lockIp->delete();
            }
        }

        return $next($request);
    }
}
