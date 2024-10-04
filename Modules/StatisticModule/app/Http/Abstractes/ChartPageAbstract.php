<?php

namespace Modules\StatisticModule\Http\Abstractes;

use App\Http\Controllers\Controller;
use App\Models\Banners;
use App\Models\BannerStatistics;
use App\Models\Comments;
use App\Models\DownloadStatistics;
use App\Models\Likes;
use App\Models\Newsletter;
use App\Models\Torrents;
use App\Models\Wishlist;
use Illuminate\Support\Facades\DB;
use Modules\StatisticModule\Http\Interfaces\ChartPageAbstractInterface;

abstract class ChartPageAbstract extends Controller implements ChartPageAbstractInterface
{
    public function newsletterChartBuilder($startDate, $endDate): array
    {
        $groupedNewsletterUpdate = Newsletter::query()->select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d") as x'),
            DB::raw('COUNT(*) as y'));

        if (isset($startDate))
            $groupedNewsletterUpdate->whereBetween('created_at', [$startDate, $endDate]);

        $resultsArray = [];
        $groupedNewsletterUpdate->orderBy('created_at')->groupBy('x')
            ->chunk(100, function ($results) use (&$resultsArray) {
                foreach ($results as $result) {
                    $resultsArray[] = $result->toArray();
                }
            });

        return $resultsArray;
    }

    public function wishlistChartBuilder($startDate, $endDate): array
    {
        $groupedWishlist = Wishlist::query()->select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d") as x'),
            DB::raw('COUNT(*) as y'));

        if (isset($startDate))
            $groupedWishlist->whereBetween('created_at', [$startDate, $endDate]);

        $resultsArray = [];
        $groupedWishlist->orderBy('created_at')->groupBy('x')
            ->chunk(100, function ($results) use (&$resultsArray) {
                foreach ($results as $result) {
                    $resultsArray[] = $result->toArray();
                }
            });

        return $resultsArray;
    }

    public function downloadsChartBuilder($startDate, $endDate): array
    {
        $groupedDownloads = DownloadStatistics::query()->select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d") as x'),
            DB::raw('COUNT(*) as y'))
            ->where('is_link', 0);

        if (isset($startDate))
            $groupedDownloads->whereBetween('created_at', [$startDate, $endDate]);

        $resultsArray = [];
        $groupedDownloads->orderBy('created_at')->groupBy('x')
            ->chunk(100, function ($results) use (&$resultsArray) {
                foreach ($results as $result) {
                    $resultsArray[] = $result->toArray();
                }
            });

        return $resultsArray;
    }

    public function likesChartBuilder($startDate, $endDate, $toGame = false): array
    {
        $groupedLikes = Likes::query()->select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d") as x'),
            DB::raw('COUNT(*) as y'));

        if (isset($startDate))
            $groupedLikes->whereBetween('created_at', [$startDate, $endDate]);

        if ($toGame)
            $groupedLikes->whereNull('comment_id');
        else
            $groupedLikes->whereNotNull('comment_id');

        $resultsArray = [];
        $groupedLikes->orderBy('created_at')->groupBy('x')
            ->chunk(100, function ($results) use (&$resultsArray) {
                foreach ($results as $result) {
                    $resultsArray[] = $result->toArray();
                }
            });

        return $resultsArray;
    }

    public function commentariesChartBuilder($startDate, $endDate): array
    {
        $groupedCommentaries = Comments::query()->select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d") as x'),
            DB::raw('COUNT(*) as y'));

        if (isset($startDate))
            $groupedCommentaries->whereBetween('created_at', [$startDate, $endDate]);

        $resultsArray = [];
        $groupedCommentaries->orderBy('created_at')->groupBy('x')
            ->chunk(100, function ($results) use (&$resultsArray) {
                foreach ($results as $result) {
                    $resultsArray[] = $result->toArray();
                }
            });

        return $resultsArray;
    }

    public function bannersChartBuilder($startDate, $endDate): array
    {
        $groupedBanners = BannerStatistics::query()->select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d") as x'),
            DB::raw('COUNT(*) as y'));

        if (isset($startDate))
            $groupedBanners->whereBetween('created_at', [$startDate, $endDate]);

        $resultsArray = [];
        $groupedBanners->orderBy('created_at')->groupBy('x')
            ->chunk(100, function ($results) use (&$resultsArray) {
                foreach ($results as $result) {
                    $resultsArray[] = $result->toArray();
                }
            });

        $banners = Banners::query()->get();
        $jump = [];
        foreach ($banners as $banner) {
            $jump[$banner->id] = $banner->bannersStatistics();

            if (isset($startDate))
                $jump[$banner->id] = $jump[$banner->id]->whereBetween('created_at', [$startDate, $endDate]);
            $jump[$banner->id] = $jump[$banner->id]->count();
        }

        return [
            'data' => $resultsArray,
            'jump' => $jump
        ];
    }

    public function sponsorsChartBuilder($startDate, $endDate): array
    {
        $groupedSponsors = DownloadStatistics::query()->select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d") as x'),
            DB::raw('COUNT(*) as y'))
            ->where('is_link', 1);

        if (isset($startDate))
            $groupedSponsors->whereBetween('created_at', [$startDate, $endDate]);

        $resultsArray = [];
        $groupedSponsors->orderBy('created_at')->groupBy('x')
            ->chunk(100, function ($results) use (&$resultsArray) {
                foreach ($results as $result) {
                    $resultsArray[] = $result->toArray();
                }
            });

        $gameSponsors = Torrents::query()->where('is_link', 1)->get();
        $jump = [];

        foreach ($gameSponsors as $sponsor) {
            $query = $sponsor->downloadStatistic();

            if (isset($startDate))
                $query->whereBetween('created_at', [$startDate, $endDate]);
            $gameId = $sponsor->game->id;
            $jump[$gameId] = ($jump[$gameId] ?? 0) + $query->count();
        }

        return [
            'data' => $resultsArray,
            'jump' => $jump
        ];
    }
}
