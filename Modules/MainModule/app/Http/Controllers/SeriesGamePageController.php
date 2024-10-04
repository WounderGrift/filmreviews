<?php

namespace Modules\MainModule\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Series;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Modules\MainModule\Http\Interfaces\SeriesPageInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SeriesGamePageController extends Controller implements SeriesPageInterface
{
    const TITLE     = "СЕРИЙНЫЕ ИГРЫ";
    const JS_FILE   = 'modules/mainmodule/resources/assets/js/page/series.js';
    const IS_SERIES = true;

    const PER_PAGE_SERIES = 7;
    const PER_PAGE_GAMES  = 28;

    public function index()
    {
        $series = Series::query()->whereHas('games', function ($query) {
            $query->where('status',  Game::STATUS_PUBLISHED);
        }, '>', 1)->orderBy('created_at', 'DESC')
            ->paginate(self::PER_PAGE_SERIES);

        Session::put('previous_tab', url()->current());
        return view('mainmodule::series', [
            'title'    => self:: TITLE,
            'series'   => $series,
            'jsFile'   => self::JS_FILE,
            'isSeries' => self::IS_SERIES,
        ]);
    }

    public function indexSeries($uri)
    {
        $series = Series::query()->where('uri', $uri)->first();
        $games  = Game::query()->where('is_soft', 0)
            ->where('status', Game::STATUS_PUBLISHED)
            ->whereHas('series', function ($query) use ($uri) {
                $query->where('uri', 'like', "%{$uri}%");
            })->orderByRaw('STR_TO_DATE(date_release, "%d %M %Y") ASC')
            ->paginate(self::PER_PAGE_GAMES);


        $label = Series::query()->where('uri', $uri)->value('name');
        if ($label)
            $label = self::TITLE . ', ' . mb_strtoupper($label);

        if (!Auth::user()->checkOwnerOrAdmin() && $games->isEmpty())
            throw new NotFoundHttpException();

        return view('mainmodule::series-view', [
            'title'  => $label ?? self:: TITLE,
            'series' => $series,
            'games'  => $games,
            'isSeries' => self::IS_SERIES
        ]);
    }
}
