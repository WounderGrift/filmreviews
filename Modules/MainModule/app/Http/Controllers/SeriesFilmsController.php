<?php

namespace Modules\MainModule\Http\Controllers;

use App\Models\Film;
use App\Models\Series;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SeriesFilmsController
{
    const TITLE     = "СЕРИЙНЫЕ ИГРЫ";
    const JS_FILE   = 'Modules/MainModule/resources/assets/js/page/series.js';
    const IS_SERIES = true;

    const PER_PAGE_SERIES = 7;
    const PER_PAGE_filmS  = 28;

    public function index()
    {
        $series = Series::query()->whereHas('films', function ($query) {
            $query->where('status',  Film::STATUS_PUBLISHED);
        }, '>=', 0)->orderBy('created_at', 'DESC')
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
        $series = (Auth::check() && !Auth::user()->checkOwnerOrAdmin() || !Auth::check())
            ? Series::query()->where('uri', $uri)->first()
            : Series::withTrashed()->where('uri', $uri)->first();

        if ((!Auth::check() || Auth::check() && !Auth::user()->checkOwnerOrAdmin()) && !$series)
            return redirect()->route('series.index');

        $films  = Film::query()
            ->where('status', Film::STATUS_PUBLISHED)
            ->whereHas('series', function ($query) use ($uri) {
                $query->where('uri', 'like', "%{$uri}%");
            })->orderByRaw('STR_TO_DATE(date_release, "%d %M %Y") ASC')
            ->paginate(self::PER_PAGE_filmS);


        $label = Series::query()->where('uri', $uri)->value('name');
        if ($label)
            $label = self::TITLE . ', ' . mb_strtoupper($label);

        if (Auth::check() && !Auth::user()->checkOwnerOrAdmin() && $films->isEmpty())
            throw new NotFoundHttpException();

        return view('mainmodule::series-view', [
            'title'  => $label ?? self:: TITLE,
            'series' => $series,
            'films'  => $films,
            'isSeries' => self::IS_SERIES
        ]);
    }
}
