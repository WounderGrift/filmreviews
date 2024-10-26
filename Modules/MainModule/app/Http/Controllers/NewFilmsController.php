<?php

namespace Modules\MainModule\Http\Controllers;

use App\Http\Services\FilmService;
use App\Models\Categories;
use App\Models\Film;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Session;

class NewFilmsController
{
    const TITLE    = 'НОВИНОЧКИ';
    const ROUTE    = 'new.index.category';
    const JS_FILE  = 'Modules/MainModule/resources/assets/js/page/new.js';
    const PER_PAGE = 28;

    protected FilmService $filmService;

    public function __construct(FilmService $filmService)
    {
        $this->filmService = $filmService;
    }

    public function index($category = null)
    {
        $now   = Carbon::now();
        $films = Film::query()->select('film.*')
            ->whereRaw("STR_TO_DATE(film.date_release, '%d %M %Y')
                BETWEEN STR_TO_DATE(?, '%Y-%m-%d') AND STR_TO_DATE(?, '%Y-%m-%d')", [
                $now->copy()->subYear()->startOfMonth()->format('Y-m-d'),
                $now->copy()->startOfMonth()->format('Y-m-d')
            ])->where('film.is_waiting', 0)
            ->where('film.status', Film::STATUS_PUBLISHED);

        $categories = $this->filmService->getCategories($films);

        if (isset($category)) {
            $films = $this->filmService->foundByCategory($films, $category);

            $label = Categories::query()->where('url', $category)->value('label');
            if ($label)
                $label = self::TITLE . ', ' . mb_strtoupper($label);
        }

        $films = $films->orderBy('film.is_sponsor', 'DESC')
            ->orderByRaw('STR_TO_DATE(film.date_release, "%d %M %Y") DESC')
            ->paginate(self::PER_PAGE);

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
