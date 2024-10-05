<?php

namespace Modules\MainModule\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\YearReleases;
use Illuminate\Support\Facades\Session;
use Modules\MainModule\Http\Interfaces\YearPageInterface;

class YearGamesPageController extends Controller implements YearPageInterface
{
    const TITLE    = 'ИГРЫ ПО ГОДАМ';
    const ROUTE    = 'year.index.category';
    const JS_FILE  = 'Modules/MainModule/resources/assets/js/page/year.js';
    const PER_PAGE = 28;

    public function index($category)
    {
        $games = Game::query()->where('game.is_soft', 0)
            ->where('game.is_waiting', 0)
            ->where('status', Game::STATUS_PUBLISHED);

        $categories = YearReleases::query()->where('year', '<=', date('Y'))
            ->orderBy('years_releases.year', 'DESC')
            ->pluck('years_releases.year', 'years_releases.year')
            ->filter(function ($item) {
                return !empty($item);
            });

        if (isset($category)) {
            $games = $games->whereRaw("YEAR(STR_TO_DATE(game.date_release, '%d %M %Y')) = ?", [$category]);
            $label = YearReleases::query()->where('year', $category)->value('year');
            if ($label)
                $label = 'ИГРЫ ЗА ' . $label . ' ГОД';
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
