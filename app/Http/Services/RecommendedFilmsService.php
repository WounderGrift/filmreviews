<?php

namespace App\Http\Services;

use App\Models\DownloadStatistics;
use App\Models\Film;
use App\Models\Likes;
use App\Models\Newsletter;
use App\Models\Wishlist;
use Illuminate\Support\Facades\DB;

class RecommendedFilmsService
{
    public function getPopularCategoriesFromLikes(int $userId = null): array
    {
        $filmIds = Likes::query()->select('film_id', DB::raw('COUNT(film_id) as like_count'))
            ->when($userId, function ($query, $userId) {
                return $query->where('user_id', $userId);
            })
            ->whereNull('comment_id')
            ->groupBy('film_id')
            ->take(5)->inRandomOrder()
            ->get()->pluck('film_id')->toArray();

        return Film::query()->select('film_categories_link.category_id')
            ->leftJoin('film_categories_link', 'film_categories_link.film_id', '=', 'film.id')
            ->whereIn('film_categories_link.film_id', $filmIds)
            ->get()->pluck('category_id')->toArray();
    }

    public function getPopularCategoriesFromWishlist(int $userId = null): array
    {
        $filmIds = Wishlist::query()->select('film_id', DB::raw('COUNT(film_id) as wishlist_count'))
            ->when($userId, function ($query, $userId) {
                return $query->where('user_id', $userId);
            })
            ->groupBy('film_id')
            ->take(5)->inRandomOrder()
            ->get()->pluck('film_id')->toArray();

        return Film::query()->select('film_categories_link.category_id')
            ->leftJoin('film_categories_link', 'film_categories_link.film_id', '=', 'film.id')
            ->whereIn('film_categories_link.film_id', $filmIds)
            ->get()->pluck('category_id')->toArray();
    }

    public function getPopularCategoriesFromNewsletter(int $userId = null): array
    {
        $filmIds = Newsletter::query()->select('film_id', DB::raw('COUNT(film_id) as newsletter_count'))
            ->when($userId, function ($query, $userId) {
                return $query->where('user_id', $userId);
            })
            ->groupBy('film_id')
            ->take(5)->inRandomOrder()
            ->get()->pluck('film_id')->toArray();

        return Film::query()->select('film_categories_link.category_id')
            ->leftJoin('film_categories_link', 'film_categories_link.film_id', '=', 'film.id')
            ->whereIn('film_categories_link.film_id', $filmIds)
            ->get()->pluck('category_id')->toArray();
    }

    public function getPopularCategoriesFromDownloads(int $userId = null): array
    {
        $downloadsIds = DownloadStatistics::query()
            ->select('file_id', DB::raw('COUNT(file_id) as downloads_count'))
            ->when($userId, function ($query, $userId) {
                return $query->where('user_id', $userId);
            })
            ->groupBy('file_id')
            ->take(5)->inRandomOrder()
            ->get();

        $filmIds = [];
        foreach ($downloadsIds as $downloadsId) {
            if ($downloadsId?->files?->film) {
                $filmId = $downloadsId->files->film->id;
                if (!in_array($filmId, $filmIds))
                    $filmIds[] = $filmId;
            }
        }
        return Film::query()->select('film_categories_link.category_id')
            ->leftJoin('film_categories_link', 'film_categories_link.film_id', '=', 'film.id')
            ->whereIn('film_categories_link.film_id', $filmIds)
            ->get()->pluck('category_id')->toArray();
    }
}
