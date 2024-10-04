<?php

namespace Modules\MainModule\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Helpers\TelegramLogHelper;
use App\Models\Categories;
use App\Models\Game;

use App\Models\Newsletter;
use App\Models\Wishlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Modules\MainModule\Http\Interfaces\WishlistGameInterface;

class WishlistGamePageController extends Controller implements WishlistGameInterface
{
    const TITLE    = 'ЖЕЛАЕМЫЕ';
    const ROUTE    = 'wishlist.index.category';
    const JS_FILE  = null;
    const IS_WISHLIST_PAGE = true;
    const PER_PAGE = 28;

    public function index($category = null)
    {
        $games = Game::query()->select('game.*')
            ->rightJoin('wishlist', function ($join) {
                $join->on('wishlist.game_id', '=', 'game.id')
                    ->where('wishlist.user_id', '=', Auth::user()->id);
            })->where(function ($query) {
                $query->where('status', Game::STATUS_PUBLISHED);
                if (Auth::check() && Auth::user()->checkOwnerOrAdmin()) {
                    $query->orWhere('status', Game::STATUS_UNPUBLISHED);
                }
            });

        $gamesCopy = clone $games;
        $categories = $gamesCopy->select('categories.label', 'categories.url')
            ->leftJoin('games_categories_link', 'games_categories_link.game_id', '=', 'game.id')
            ->leftJoin('categories', 'categories.id', '=', 'games_categories_link.category_id')
            ->orderBy('categories.label', 'DESC')
            ->groupBy('categories.label', 'categories.url')
            ->distinct()
            ->pluck('categories.label', 'categories.url')
            ->filter(function ($item) {
                return !empty($item);
            });

        if (isset($category)) {
            $games->leftJoin('games_categories_link', 'games_categories_link.game_id', '=', 'game.id')
                ->leftJoin('categories', 'categories.id', '=', 'games_categories_link.category_id')
                ->where('categories.url', $category);

            $label = Categories::query()->where('url', $category)->value('label');
            if ($label)
                $label = self::TITLE . ', ' . mb_strtoupper($label);
        }

        $games  = $games->paginate(self::PER_PAGE);

        Session::put('previous_tab', url()->current());
        return view('mainmodule::grid', [
            'title'  => $label ?? self:: TITLE,
            'route'  => self::ROUTE,
            'jsFile' => self::JS_FILE,
            'games'  => $games,
            'categories' => $categories,
            'isWishlistPage' => self::IS_WISHLIST_PAGE
        ]);
    }

    public function toggleWishlist (Request $request): JsonResponse
    {
        if (!$request->user())
            return response()->json(['message' => 'Forbidden'], 403);

        $data = $request->validate([
            'game_id' => ['string'],
            'toggleWishlist'  => ['boolean'],
        ]);

        $data['game_id'] = base64_decode($data['game_id']);
        if (!Game::query()->where('id', $data['game_id'])->exists())
            return response()->json(['message' => 'Недопустимый ID игры'], 403);

        $game = Game::query()->findOrFail($data['game_id']);

        $wishitem = Wishlist::query()->firstOrcreate([
            'game_id' => $data['game_id'],
            'user_id' => $request->user()->id,
        ]);

        if (!$data['toggleWishlist']) {
            $wishitem->delete();
        } else {
            $newsletterData = [
                'user_id' => $request->user()->id,
                'game_id' => $data['game_id'],
                'email'   => $request->user()->email
            ];

            Newsletter::query()->firstOrCreate($newsletterData);
        }

        TelegramLogHelper::reportToggleWishlist($request->user(), $game, $data['toggleWishlist'], !$wishitem);
        return response()->json(['bool' => $data['toggleWishlist']]);
    }
}
