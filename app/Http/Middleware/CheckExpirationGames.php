<?php

namespace App\Http\Middleware;

use App\Helpers\TelegramLogHelper;
use App\Models\Game;
use App\Models\OverdueTgReport;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckExpirationGames
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $dateSend = OverdueTgReport::orderBy('date_send', 'DESC')->value('date_send');
        $dateSend = Carbon::parse($dateSend);
        $today    = Carbon::now();

        if (!$today->isSameDay($dateSend) || !$dateSend) {
            $games = Game::where('status', Game::STATUS_PUBLISHED)
                ->where('game.is_waiting', 1)
                ->whereRaw("STR_TO_DATE(date_release, '%Y-%m-%d') <= ?", [$today])
                ->orderBy('is_sponsor', 'DESC')
                ->orderBy('date_release', 'DESC')->get();

            OverdueTgReport::query()->create([
                'date_send' => $today
            ]);

            TelegramLogHelper::reportOverdueGame($games);
        }

        return $next($request);
    }
}
