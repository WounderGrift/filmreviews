<?php

namespace App\Http\Middleware;

use App\Helpers\TelegramLogHelper;
use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDOException;
use Symfony\Component\HttpFoundation\Response;

class HealthCheckerDb
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            DB::connection()->getPdo();
        } catch (QueryException $e) {
            TelegramLogHelper::reportDatabaseError();
        } catch (PDOException $e) {
            TelegramLogHelper::reportDatabaseError();
        }

        return $next($request);
    }
}
