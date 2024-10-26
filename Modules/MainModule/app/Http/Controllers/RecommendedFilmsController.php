<?php

namespace Modules\MainModule\Http\Controllers;

use App\Http\Services\RecommendedFilmsService;
use App\Models\Film;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class RecommendedFilmsController
{
    const IS_RECOMMENDED = true;
    const IN_DETAIL_PAGE = true;
    const JS_FILE  = 'Modules/MainModule/resources/assets/js/page/main.js';
    const PER_PAGE = 7;

    protected RecommendedFilmsService $filmService;

    public function __construct(RecommendedFilmsService $filmService)
    {
        $this->filmService = $filmService;
    }

    public function index()
    {
        $popularAndRecommended = Film::query()->select('film.*')
            ->where('film.is_waiting', 0)
            ->where('status', Film::STATUS_PUBLISHED)
            ->orderByDesc(Film::query()->selectRaw('COUNT(*)')
                ->from('download_statistics')
                ->join('file', 'file.id', '=', 'download_statistics.file_id')
                ->whereColumn('file.film_id', 'film.id')
            )->take(14)->get();

        $lastUpdate = Film::query()->select('film.*')
            ->where('film.is_waiting', 0)
            ->where('status', Film::STATUS_PUBLISHED)
            ->orderBy('film.updated_at', 'DESC')
            ->take(14)->get();

        $lastPublication = Film::query()->select('film.*')
            ->where('film.is_waiting', 0)
            ->where('status', Film::STATUS_PUBLISHED)
            ->orderByRaw('STR_TO_DATE(date_release, "%d %M %Y") DESC')
            ->take(4)->get();

        $userId = Auth::user()->id ?? null;
        $categoriesFromLikes      = $this->filmService->getPopularCategoriesFromLikes($userId);
        $categoriesFromWishlist   = $this->filmService->getPopularCategoriesFromWishlist($userId);
        $categoriesFromNewsletter = $this->filmService->getPopularCategoriesFromNewsletter($userId);
        $categoriesFromDownloads  = $this->filmService->getPopularCategoriesFromDownloads($userId);

        $categoriesMerge = array_unique(array_merge(
            $categoriesFromLikes,
            $categoriesFromWishlist,
            $categoriesFromNewsletter,
            $categoriesFromDownloads
        ));

        $forRecommended = Film::query()->select('film.id')
            ->where('film.is_waiting', 0)
            ->where('film.status', Film::STATUS_PUBLISHED)
            ->inRandomOrder()
            ->leftJoin('film_categories_link', 'film_categories_link.film_id', '=', 'film.id')
            ->when($categoriesMerge, function ($query, $categoriesMerge) {
                return $query->leftJoin('film_categories_link as gcl', 'gcl.film_id', '=', 'film.id')
                    ->whereIn('gcl.category_id', $categoriesMerge);
            })->distinct()->take(15)->inRandomOrder()->get()->pluck('id')->toArray();

        $recommended = base64_encode(implode(',', $forRecommended));
        $showWarning = $popularAndRecommended->isEmpty() && empty($recommended)
            && $lastPublication->isEmpty() && $lastUpdate->isEmpty();

        Session::put('previous_tab', url()->current());
        return view('mainmodule::recommended', [
            'jsFile' => self::JS_FILE,
            'popularAndRecommended' => $popularAndRecommended,
            'lastUpdate' => $lastUpdate,
            'lastPublication' => $lastPublication,
            'recommended'   => $recommended,
            'isRecommended' => self::IS_RECOMMENDED,
            'showWarning'   => $showWarning
        ]);
    }

    public function recommendedDetailIndex($ids = null)
    {
        $ids     = explode(',', base64_decode($ids));
        $firstId = array_shift($ids);

        $film = Film::query()->where('id', $firstId)
            ->where('film.is_waiting', 0)
            ->where('status', Film::STATUS_PUBLISHED)
            ->first();

        $detail   = $film?->detail;
        $info     = json_decode($detail?->info);
        $comments = $detail?->comments;

        $showSeries = false;
        if (isset($film->series)) {
            $showSeries = Film::query()->select('film.*')
                    ->where('film.is_waiting', 0)
                    ->where('film.status', Film::STATUS_PUBLISHED)
                    ->where('film.series_id', $film->series_id)
                    ->count() > 1;
        }

        if ($comments) {
            $comments = $comments->sortByDesc('created_at');
            $total    = $comments->count();

            $currentPage = request()->query('page', ceil($total / self::PER_PAGE));
            $comments    = $comments->forPage($currentPage, self::PER_PAGE);

            $comments = new LengthAwarePaginator($comments, $total, self::PER_PAGE, $currentPage, [
                'path'     => LengthAwarePaginator::resolveCurrentPath(),
                'pageName' => 'page',
            ]);
        }

        $buttonTitle = 'Следующая (' . count($ids) . ')';
        $recommended = base64_encode(implode(',', $ids));

        return view('detailmodule::detail', [
            'inDetailPage'  => self::IN_DETAIL_PAGE,
            'isRecommended' => self::IS_RECOMMENDED,
            'film' => $film,
            'buttonTitle' => $buttonTitle,
            'showSeries'  => $showSeries,
            'detail'   => $detail,
            'info'     => $info,
            'comments' => $comments,
            'recommended' => $recommended
        ]);
    }
}
