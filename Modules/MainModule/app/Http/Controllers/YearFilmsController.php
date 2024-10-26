<?php

namespace Modules\MainModule\Http\Controllers;

use App\Http\Services\FilmService;
use App\Models\Film;
use App\Models\YearReleases;
use Illuminate\Support\Facades\Session;

class YearFilmsController
{
    const TITLE    = 'ФИЛЬМЫ ПО ГОДАМ';
    const ROUTE    = 'year.index.category';
    const JS_FILE  = 'Modules/MainModule/resources/assets/js/page/year.js';
    const PER_PAGE = 28;

    protected FilmService $filmService;

    public function __construct(FilmService $filmService)
    {
        $this->filmService = $filmService;
    }

    public function index($category)
    {
        $films = Film::query()
            ->where('film.is_waiting', 0)
            ->where('status', Film::STATUS_PUBLISHED);

        $categories = $this->filmService->foundByYear();

        if (isset($category)) {
            $films = $films->whereRaw("YEAR(STR_TO_DATE(film.date_release, '%d %M %Y')) = ?", [$category]);
            $label = YearReleases::query()->where('year', $category)->value('year');
            if ($label)
                $label = 'ИГРЫ ЗА ' . $label . ' ГОД';
        }

        $films = $films->orderBy('film.is_sponsor', 'DESC')
            ->orderByRaw('STR_TO_DATE(film.date_release, "%d %M %Y") DESC')->paginate(self::PER_PAGE);

        $showWarning = $films->isEmpty();

        Session::put('previous_tab', url()->current());
        return view('mainmodule::grid', [
            'title'  => $label ?? self:: TITLE,
            'route'  => self::ROUTE,
            'jsFile' => self::JS_FILE,
            'films'  => $films,
            'categories'  => $categories,
            'showWarning' => $showWarning
        ]);
    }
}
