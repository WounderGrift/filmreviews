<?php

namespace Modules\StatisticModule\Http\Controllers;

use App\Models\Banners;
use App\Models\Comments;
use App\Models\DownloadStatistics;
use App\Models\Game;
use App\Models\Likes;
use App\Models\Newsletter;
use App\Models\Users;
use App\Models\Wishlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\StatisticModule\Http\Abstractes\ChartPageAbstract;
use Modules\StatisticModule\Http\Interfaces\ChartPageInterface;

class ChartsPageController extends ChartPageAbstract implements ChartPageInterface
{
    const IN_OWNER_PANEL = true;
    const PER_PAGE = 28;

    public function profilesTable($search = null)
    {
        $title = $search ? "Статистика - Профили, $search" : "Статистика - Профили";

        $profiles = Users::query()
            ->select('name', 'cid', 'get_letter_release', 'avatar_path',
                'avatar_name', 'is_banned', 'last_activity', 'created_at', 'updated_at')
            ->withCount([
                'newsletters as newsletters_count',
                'wishlist as wishlist_count',
                'downloadStatistic as download_count' => function($query) {
                    $query->where('is_link', 0);
                },
                'downloadStatistic as link_count' => function($query) {
                    $query->where('is_link', 1);
                },
                'bannerStatistic as banner_count'
            ]);

        if ($search) {
            $profiles = $profiles->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%$search%")
                    ->orWhere('cid', 'LIKE', "%$search%")
                    ->orWhere('email', 'LIKE', "%$search%");
            });
        }

        $profiles = $profiles->orderBy('id', 'DESC')->paginate(self::PER_PAGE);

        return view('statisticmodule::profiles', [
            'title' => $title,
            'inOwnerPanel' => self::IN_OWNER_PANEL,
            'profiles'  => $profiles,
            'allValues' => Users::query()->count()
        ]);
    }

    public function profilesChart(Request $request): JsonResponse
    {
        $startDateType = $request->input('startDate');
        $endDate = now()->endOfDay();

        $groupedProfiles = Users::query()->select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d") as x'),
            DB::raw('COUNT(*) as y')
        );

        $startDate = match ($startDateType) {
            '7Д'    => now()->startOfDay()->subDays(7),
            '1МЕС'  => now()->startOfDay()->subMonth(),
            '1ГОД'  => now()->startOfDay()->subYear(),
            '5ЛЕТ'  => now()->startOfDay()->subYears(5),
            default => null,
        };

        $resultsArray = [];
        if (isset($startDate))
            $groupedProfiles->whereBetween('created_at', [$startDate, $endDate]);

        $groupedProfiles->orderBy('created_at')->groupBy('x')
            ->chunk(100, function ($results) use (&$resultsArray) {
                foreach ($results as $result) {
                    $resultsArray[] = $result->toArray();
                }
            });

        return response()->json([
            'data' => $resultsArray,
            'allValues' => Users::query()->count()
        ]);
    }

    public function activityChart(Request $request): JsonResponse
    {
        $startDateType = $request->input('startDate');
        $endDate = now()->endOfDay();

        $startDate = match ($startDateType) {
            '7Д'    => now()->startOfDay()->subDays(7),
            '1МЕС'  => now()->startOfDay()->subMonth(),
            '1ГОД'  => now()->startOfDay()->subYear(),
            '5ЛЕТ'  => now()->startOfDay()->subYears(5),
            default => null,
        };

        $resultsArray = [
            'downloads'        => parent::downloadsChartBuilder(startDate: $startDate, endDate: $endDate),
            'commentaries'     => parent::commentariesChartBuilder(startDate: $startDate, endDate: $endDate),
            'likesToGame'      => parent::likesChartBuilder(startDate: $startDate, endDate: $endDate, toGame: true),
            'likesToComments'  => parent::likesChartBuilder(startDate: $startDate, endDate: $endDate),
            'wishlist'         => parent::wishlistChartBuilder(startDate: $startDate, endDate: $endDate),
            'newsletterUpdate' => parent::newsletterChartBuilder(startDate: $startDate, endDate: $endDate),
        ];

        $allValueCheck = $request->boolean('allValueCheck');
        if (!$allValueCheck) {
            $allValue = [
                'downloads'        => DownloadStatistics::query()->count(),
                'commentaries'     => Comments::query()->count(),
                'likesToGame'      => Likes::query()->whereNull('comment_id')->count(),
                'likesToComments'  => Likes::query()->whereNotNull('comment_id')->count(),
                'wishlist'         => Wishlist::query()->count(),
                'newsletterUpdate' => Newsletter::query()->count(),
                'data'             => true
            ];
        }

        return response()->json([
            'data'     => $resultsArray,
            'allValue' => $allValue ?? null
        ]);
    }

    public function commentariesTable($search = null)
    {
        $title = $search ? "Статистика - Комментарии, $search" : "Статистика - Комментарии";
        $commentaries = Comments::withTrashed()->orderBy('id', 'DESC');

        if ($search) {
            $commentaries = $commentaries->where(function ($q) use ($search) {
                $q->where(function ($q1) use ($search) {
                    $q1->where('comment->quote', 'LIKE', "%$search%")
                        ->orWhere('comment->comment', 'LIKE', "%$search%");
                })->orWhereHas('user', function ($q2) use ($search) {
                    $q2->where('name', 'LIKE', "%$search%")
                        ->orWhere('email', 'LIKE', "%$search%")
                        ->orWhere('cid', 'LIKE', "%$search%");
                });
            });
        }

        $commentaries = $commentaries->orderBy('id', 'DESC')->paginate(self::PER_PAGE);
        return view('statisticmodule::activity', [
            'title' => $title,
            'inOwnerPanel' => self::IN_OWNER_PANEL,
            'commentaries' => $commentaries
        ]);
    }

    public function bannersChart(Request $request): JsonResponse
    {
        $startDateType = $request->input('startDate');
        $endDate = now()->endOfDay();

        $startDate = match ($startDateType) {
            '7Д'    => now()->startOfDay()->subDays(7),
            '1МЕС'  => now()->startOfDay()->subMonth(),
            '1ГОД'  => now()->startOfDay()->subYear(),
            '5ЛЕТ'  => now()->startOfDay()->subYears(5),
            default => null,
        };

        $resultsBanners  = parent::bannersChartBuilder($startDate, $endDate);
        $resultsSponsors = parent::sponsorsChartBuilder($startDate, $endDate);

        return response()->json(['data' => [
            'dataBanner'   => $resultsBanners['data'],
            'jumpBanner'   => $resultsBanners['jump'],
            'dataSponsors' => $resultsSponsors['data'],
            'jumpSponsors' => $resultsSponsors['jump']
        ]]);
    }

    public function bannersTable()
    {
        $title   = "Статистика - Баннеры";
        $banners = Banners::query()->orderBy('id', 'DESC')->get();
        $gamesSponsors = Game::query()->where('is_sponsor', 1)
            ->orderBy('id', 'DESC')->get();

        return view('statisticmodule::banners', [
            'title' => $title,
            'inOwnerPanel' => self::IN_OWNER_PANEL,
            'banners' => $banners,
            'gamesSponsors' => $gamesSponsors,
            'allBanners' => Banners::query()->count()
        ]);
    }
}
