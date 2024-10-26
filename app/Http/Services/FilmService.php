<?php

namespace App\Http\Services;

use App\Models\YearReleases;
use Illuminate\Support\Collection;

class FilmService
{
    public function getCategories($films)
    {
        $filmsCopy = clone $films;
        return $filmsCopy->select('categories.label', 'categories.url')
            ->leftJoin('film_categories_link', 'film_categories_link.film_id', '=', 'film.id')
            ->leftJoin('categories', 'categories.id', '=', 'film_categories_link.category_id')
            ->orderBy('categories.label', 'DESC')
            ->groupBy('categories.label', 'categories.url')
            ->distinct()
            ->pluck('categories.label', 'categories.url')
            ->filter(function ($item) {
                return !empty($item);
            });
    }

    public function foundByCategory($films, $category = null)
    {
        return $films->leftJoin('film_categories_link', 'film_categories_link.film_id', '=', 'film.id')
            ->leftJoin('categories', 'categories.id', '=', 'film_categories_link.category_id')
            ->where('categories.url', $category);
    }

    public function foundByYear(): Collection
    {
        return YearReleases::query()->where('year', '<=', date('Y'))
            ->orderBy('years_releases.year', 'DESC')
            ->pluck('years_releases.year', 'years_releases.year')
            ->filter(function ($item) {
                return !empty($item);
            });
    }
}
