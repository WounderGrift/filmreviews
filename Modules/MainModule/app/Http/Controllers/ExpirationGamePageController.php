<?php

namespace Modules\MainModule\Http\Controllers;

use App\Http\Helpers\FileHelper;
use App\Http\Controllers\Controller;
use App\Models\Game;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;

class ExpirationGamePageController extends Controller
{
    const TITLE    = "УЖЕ ДОЛЖНЫ ВЫЙТИ";
    const IN_OWNER_PANEL = true;
    const PER_PAGE = 28;

    public function index()
    {
        $today = Carbon::now();
        $games = Game::query()->where('status', Game::STATUS_PUBLISHED)
            ->where('is_waiting', 1)
            ->whereRaw("STR_TO_DATE(date_release, '%d %M %Y') <= ?", [$today])
            ->orderByRaw("STR_TO_DATE(date_release, '%d %M %Y') DESC")
            ->paginate(self::PER_PAGE);

        return view('mainmodule::grid', [
            'title' => self::TITLE,
            'inOwnerPanel'  => self::IN_OWNER_PANEL,
            'expirationWarning' => $games->isEmpty(),
            'games' => $games
        ]);
    }

    public function detail($uri)
    {
        $game = Game::query()->where('uri', $uri)
            ->where('game.status', Game::STATUS_PUBLISHED)
            ->where('game.is_waiting', 1)
            ->first();

        $gameOriginal = Game::query()->where('game.name', 'like', "%{$game->name}%")
            ->where('game.status', Game::STATUS_PUBLISHED)
            ->first();

        $detail = $game?->detail;
        $info   = json_decode($detail?->info);

        $mimeTypeImage = implode(', ', FileHelper::ACCESS_IMAGE_MIME_TYPE);
        $mimeTypeFile  = implode(', ', FileHelper::ACCESS_FILE_MIME_TYPE);

        Session::put('previous_tab', url()->current());

        return view('mainmodule::grid', [
            'game'   => $game,
            'detail' => $detail,
            'info'   => $info,
            'inOwnerPanel'  => self::IN_OWNER_PANEL,
            'gameOriginal'  => $gameOriginal,
            'mimeTypeImage' => $mimeTypeImage,
            'mimeTypeFile'  => $mimeTypeFile
        ]);
    }
}
