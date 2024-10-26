<?php

namespace Modules\MainModule\Http\Controllers;

use App\Models\Film;
use App\Models\Series;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class SearchFilmsController
{
    const TOTAL_SEARCH = true;

    const IS_WISHLIST_PAGE   = true;
    const IN_WISHLIST_SEARCH = true;

    const IN_OWNER_PANEL = true;
    const IS_UNPUBLISHED = true;
    const IS_UNPUBLISHED_SEARCH = true;

    const IS_SERIES = true;
    const IS_SERIES_SEARCH = true;

    const PER_PAGE = 28;

    public function index(Request $request)
    {
        $query = $request->input('query');
        $title = "Поиск $query";

        $films = Film::query()->where('film.name', 'like',  "%{$query}%")
            ->where('status', Film::STATUS_PUBLISHED)
            ->orderByRaw('STR_TO_DATE(film.date_release, "%d %M %Y") DESC')
            ->paginate(self::PER_PAGE);

        $categories = $route = $jsFile = null;

        Session::put('previous_tab', url()->current());
        return view('mainmodule::grid', [
            'title'  => $title,
            'route'  => $route,
            'jsFile' => $jsFile,
            'films'  => $films,
            'categories' => $categories,
            'justSearch' => self::TOTAL_SEARCH
        ]);
    }

    public function searchForWishlist(Request $request)
    {
        $query = $request->input('query');
        $title = "Поиск $query по желаемым";

        $films = Film::query()->whereHas('wishlist', function ($query) use ($request) {
            $query->where('user_id', $request->user()->id);
        })->where('name', 'like', "%{$query}%")->paginate(self::PER_PAGE);

        $categories = $route = $jsFile = null;

        return view('mainmodule::grid', [
            'title'  => $title,
            'route'  => $route,
            'jsFile' => $jsFile,
            'films'  => $films,
            'categories' => $categories,
            'isWishlistPage'   => self::IS_WISHLIST_PAGE,
            'inWishlistSearch' => self::IN_WISHLIST_SEARCH
        ]);
    }

    public function searchForUnpublished(Request $request)
    {
        $query = $request->input('query');
        $title = "Поиск $query по неопубликованным";

        $films = Film::query()->where('film.name', 'like', "%{$query}%")
            ->where('status', Film::STATUS_UNPUBLISHED)
            ->orderByRaw('STR_TO_DATE(film.date_release, "%d %M %Y") DESC')
            ->paginate(self::PER_PAGE);

        $categories = $route = $jsFile = null;

        return view('mainmodule::grid', [
            'title'  => $title,
            'route'  => $route,
            'jsFile' => $jsFile,
            'films'  => $films,
            'categories' => $categories,
            'isUnpublishedSearch' => self::IS_UNPUBLISHED_SEARCH,
            'isUnpublished' => self::IS_UNPUBLISHED,
            'inOwnerPanel'  => self::IN_OWNER_PANEL
        ]);
    }

    public function searchForSeries(Request $request)
    {
        $query = $request->input('query');
        $title = "Поиск $query по сериям";

        $series = Series::query()->where('uri', 'like', "%{$query}%")
            ->orWhere('name', 'like', "%{$query}%")->paginate(self::PER_PAGE);

        $categories = $route = $jsFile = null;

        return view('mainmodule::grid', [
            'title'  => $title,
            'route'  => $route,
            'jsFile' => $jsFile,
            'series' => $series,
            'categories' => $categories,
            'isSeriesSearch' => self::IS_SERIES_SEARCH,
            'isSeries' => self::IS_SERIES
        ]);
    }
}
