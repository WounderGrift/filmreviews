<?php

namespace App\Http\Services;

use App\Models\Film;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class DetailService
{
    const PER_PAGE = 7;

    public function getFilmDetail($uri)
    {
        $filmQuery = Film::query()
            ->when(
                Auth::check() && Auth::user()->checkOwnerOrAdmin(),
                fn($query) => $query->withTrashed(),
                fn($query) => $query->where('status', Film::STATUS_PUBLISHED)
            );

        return $filmQuery->where('uri', $uri)->first();
    }

    public function showSeries($film): bool
    {
        $showSeries = false;
        if (isset($film->series)) {
            $showSeries = Film::query()->select('film.*')
                    ->where('film.is_waiting', 0)
                    ->where('film.status', Film::STATUS_PUBLISHED)
                    ->where('film.series_id', $film->series_id)
                    ->count() > 1;
        }

        return $showSeries;
    }

    public function getCommentPaginate($comments): LengthAwarePaginator
    {
        $comments = $comments->sortByDesc('created_at');
        $total    = $comments->count();

        $currentPage = request()->query('page', ceil($total / self::PER_PAGE));
        $comments    = $comments->forPage($currentPage, self::PER_PAGE);

        return new LengthAwarePaginator($comments, $total, self::PER_PAGE, $currentPage, [
            'path'     => LengthAwarePaginator::resolveCurrentPath(),
            'pageName' => 'page',
        ]);
    }
}
