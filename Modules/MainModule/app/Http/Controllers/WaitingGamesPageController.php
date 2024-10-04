<?php

namespace Modules\MainModule\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Categories;
use App\Models\Game;
use Illuminate\Support\Facades\Session;
use Modules\MainModule\Http\Interfaces\MainPageInterface;

class WaitingGamesPageController extends Controller implements MainPageInterface
{
    const TITLE    = 'ЖДЕМ';
    const ROUTE    = 'waiting.index.category';
    const JS_FILE  = 'modules/mainmodule/resources/assets/js/page/waiting.js';
    const PER_PAGE = 28;

    public function index($category = null)
    {
        $games = Game::query()->select('game.*')
            ->where('game.is_soft', 0)
            ->where('game.is_waiting', 1)
            ->where('status', Game::STATUS_PUBLISHED);

        $gamesCopy  = clone $games;
        $categories = $gamesCopy->select('categories.label', 'categories.url')
            ->leftJoin('games_categories_link', 'games_categories_link.game_id', '=', 'game.id')
            ->leftJoin('categories', 'categories.id', '=', 'games_categories_link.category_id')
            ->orderBy('categories.label', 'DESC')
            ->groupBy('categories.label', 'categories.url')
            ->distinct()
            ->pluck('categories.label', 'categories.url')
            ->filter(function ($item) {
                return !empty($item);
            });

        if (isset($category)) {
            $games->leftJoin('games_categories_link', 'games_categories_link.game_id', '=', 'game.id')
                ->leftJoin('categories', 'categories.id', '=', 'games_categories_link.category_id')
                ->where('categories.url', $category);

            $label = Categories::query()->where('url', $category)->value('label');
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
