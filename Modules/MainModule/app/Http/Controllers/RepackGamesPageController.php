<?php

namespace Modules\MainModule\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Repacks;
use Illuminate\Support\Facades\Session;
use Modules\MainModule\Http\Interfaces\MainPageInterface;

class RepackGamesPageController extends Controller implements MainPageInterface
{
    const TITLE    = 'ПОПУЛЯРНЫЕ РЕПАКИ';
    const ROUTE    = 'repacks.index.category';
    const JS_FILE  = 'modules/mainmodule/resources/assets/js/page/repack.js';
    const PER_PAGE = 28;

    public function index($category = null)
    {
        $games = Game::query()->select('game.*')
            ->where('game.is_soft', 0)
            ->where('game.is_waiting', 0)
            ->where('status', Game::STATUS_PUBLISHED)
            ->distinct('game.id');

        $gamesCopy = clone $games;
        $categories = $gamesCopy->select('repacks.label', 'repacks.url')
            ->leftJoin('torrents', 'torrents.game_id', '=', 'game.id')
            ->leftJoin('repacks', 'repacks.id', '=', 'torrents.repack_id')
            ->whereNotNull('torrents.repack_id')
            ->orderBy('repacks.label', 'DESC')
            ->groupBy('repacks.label', 'repacks.url')
            ->distinct()
            ->pluck('repacks.label', 'repacks.url')
            ->filter(function ($item) {
                return !empty($item);
            });

        if (isset($category)) {
            $games->leftJoin('torrents', 'torrents.game_id', '=', 'game.id')
                ->leftJoin('repacks', 'repacks.id', '=', 'torrents.repack_id')
                ->where('repacks.url', $category);

            $label = Repacks::query()->where('url', $category)->value('label');
            if ($label)
                $label = self::TITLE . ', ' . mb_strtoupper($label);
        }

        $games = $games->orderBy('game.is_sponsor', 'DESC')
            ->orderByRaw('STR_TO_DATE(game.date_release, "%d %M %Y") DESC')->paginate(self::PER_PAGE);

        $showWarning = $games->isEmpty();

        Session::put('previous_tab', url()->current());
        return view('mainmodule::grid', [
            'title'  => $label ?? self:: TITLE,
            'route'  => self::ROUTE,
            'jsFile' => self::JS_FILE,
            'games'  => $games,
            'categories'  => $categories,
            'showWarning' => $showWarning
        ]);
    }
}
