<?php

namespace Modules\MainModule\Http\Abstractes;

use App\Http\Controllers\Controller;
use App\Models\DownloadStatistics;
use App\Models\Game;
use App\Models\Likes;
use App\Models\Newsletter;
use App\Models\Wishlist;
use Illuminate\Support\Facades\DB;
use Modules\MainModule\Http\Interfaces\RecommendedPageAbstractInterface;

abstract class RecommendedPageAbstract extends Controller implements RecommendedPageAbstractInterface
{
    public function getPopularCategoriesFromLikes(int $userId = null): array
    {
        $gameIds = Likes::query()->select('game_id', DB::raw('COUNT(game_id) as like_count'))
            ->when($userId, function ($query, $userId) {
                return $query->where('user_id', $userId);
            })
            ->whereNull('comment_id')
            ->groupBy('game_id')
            ->take(5)->inRandomOrder()
            ->get()->pluck('game_id')->toArray();

        return Game::query()->select('games_categories_link.category_id')
            ->leftJoin('games_categories_link', 'games_categories_link.game_id', '=', 'game.id')
            ->whereIn('games_categories_link.game_id', $gameIds)
            ->get()->pluck('category_id')->toArray();
    }

    public function getPopularCategoriesFromWishlist(int $userId = null): array
    {
        $gameIds = Wishlist::query()->select('game_id', DB::raw('COUNT(game_id) as wishlist_count'))
            ->when($userId, function ($query, $userId) {
                return $query->where('user_id', $userId);
            })
            ->groupBy('game_id')
            ->take(5)->inRandomOrder()
            ->get()->pluck('game_id')->toArray();

        return Game::query()->select('games_categories_link.category_id')
            ->leftJoin('games_categories_link', 'games_categories_link.game_id', '=', 'game.id')
            ->whereIn('games_categories_link.game_id', $gameIds)
            ->get()->pluck('category_id')->toArray();
    }

    public function getPopularCategoriesFromNewsletter(int $userId = null): array
    {
        $gameIds = Newsletter::query()->select('game_id', DB::raw('COUNT(game_id) as newsletter_count'))
            ->when($userId, function ($query, $userId) {
                return $query->where('user_id', $userId);
            })
            ->groupBy('game_id')
            ->take(5)->inRandomOrder()
            ->get()->pluck('game_id')->toArray();

        return Game::query()->select('games_categories_link.category_id')
            ->leftJoin('games_categories_link', 'games_categories_link.game_id', '=', 'game.id')
            ->whereIn('games_categories_link.game_id', $gameIds)
            ->get()->pluck('category_id')->toArray();
    }

    public function getPopularCategoriesFromDownloads(int $userId = null): array
    {
        $downloadsIds = DownloadStatistics::query()
            ->select('torrent_id', DB::raw('COUNT(torrent_id) as downloads_count'))
            ->when($userId, function ($query, $userId) {
                return $query->where('user_id', $userId);
            })
            ->groupBy('torrent_id')
            ->take(5)->inRandomOrder()
            ->get();

        $gameIds = [];
        foreach ($downloadsIds as $downloadsId) {
            if ($downloadsId?->torrents?->game) {
                $gameId = $downloadsId->torrents->game->id;
                if (!in_array($gameId, $gameIds))
                    $gameIds[] = $gameId;
            }
        }
        return Game::query()->select('games_categories_link.category_id')
            ->leftJoin('games_categories_link', 'games_categories_link.game_id', '=', 'game.id')
            ->whereIn('games_categories_link.game_id', $gameIds)
            ->get()->pluck('category_id')->toArray();
    }

    abstract public function index();
    abstract public function recommendedDetailIndex($ids = null);
}
