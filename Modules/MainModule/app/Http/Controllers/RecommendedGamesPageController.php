<?php

namespace Modules\MainModule\Http\Controllers;

use App\Models\Game;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Modules\MainModule\Http\Abstractes\RecommendedPageAbstract;

class RecommendedGamesPageController extends RecommendedPageAbstract
{
    const IS_RECOMMENDED = true;
    const IN_DETAIL_PAGE = true;
    const JS_FILE  = 'modules/mainmodule/resources/assets/js/page/main.js';
    const PER_PAGE = 7;

    public function index()
    {
        $popularAndRecommended = Game::query()->select('game.*')
            ->where('game.is_soft', 0)
            ->where('game.is_waiting', 0)
            ->where('status', Game::STATUS_PUBLISHED)
            ->orderByDesc(Game::selectRaw('COUNT(*)')
                ->from('download_statistics')
                ->join('torrents', 'torrents.id', '=', 'download_statistics.torrent_id')
                ->whereColumn('torrents.game_id', 'game.id')
            )->take(14)->get();

        $lastUpdate = Game::query()->select('game.*')
            ->where('game.is_soft', 0)
            ->where('game.is_waiting', 0)
            ->where('status', Game::STATUS_PUBLISHED)
            ->orderBy('game.updated_at', 'DESC')
            ->take(14)->get();

        $lastPublication = Game::query()->select('game.*')
            ->where('game.is_soft', 0)
            ->where('game.is_waiting', 0)
            ->where('status', Game::STATUS_PUBLISHED)
            ->orderByRaw('STR_TO_DATE(date_release, "%d %M %Y") DESC')
            ->take(4)->get();

        $userId = Auth::user()->id ?? null;
        $categoriesFromLikes      = parent::getPopularCategoriesFromLikes($userId);
        $categoriesFromWishlist   = parent::getPopularCategoriesFromWishlist($userId);
        $categoriesFromNewsletter = parent::getPopularCategoriesFromNewsletter($userId);
        $categoriesFromDownloads  = parent::getPopularCategoriesFromDownloads($userId);

        $categoriesMerge = array_unique(array_merge(
            $categoriesFromLikes,
            $categoriesFromWishlist,
            $categoriesFromNewsletter,
            $categoriesFromDownloads
        ));

        $forRecommended = Game::query()->select('game.id')
            ->where('game.is_soft', 0)
            ->where('game.is_waiting', 0)
            ->where('game.status', Game::STATUS_PUBLISHED)
            ->inRandomOrder()
            ->leftJoin('games_categories_link', 'games_categories_link.game_id', '=', 'game.id')
            ->when($categoriesMerge, function ($query, $categoriesMerge) {
                return $query->leftJoin('games_categories_link as gcl', 'gcl.game_id', '=', 'game.id')
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

        $game = Game::query()->where('id', $firstId)
            ->where('game.is_soft', 0)
            ->where('game.is_waiting', 0)
            ->where('status', Game::STATUS_PUBLISHED)
            ->first();

        $detail   = $game?->detail;
        $info     = json_decode($detail?->info);
        $comments = $detail?->comments;

        $showSeries = false;
        if (isset($game->series)) {
            $showSeries = Game::query()->select('game.*')
                    ->where('game.is_soft', 0)
                    ->where('game.is_waiting', 0)
                    ->where('game.status', Game::STATUS_PUBLISHED)
                    ->where('game.series_id', $game->series_id)
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
            'game' => $game,
            'buttonTitle' => $buttonTitle,
            'showSeries'  => $showSeries,
            'detail'   => $detail,
            'info'     => $info,
            'comments' => $comments,
            'recommended' => $recommended
        ]);
    }
}
