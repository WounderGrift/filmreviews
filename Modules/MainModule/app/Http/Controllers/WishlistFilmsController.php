<?php

namespace Modules\MainModule\Http\Controllers;

use App\Http\Services\FilmService;
use App\Models\Categories;
use App\Models\Film;
use App\Models\Newsletter;
use App\Models\Wishlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class WishlistFilmsController
{
    const TITLE    = 'ЖЕЛАЕМЫЕ';
    const ROUTE    = 'wishlist.index.category';
    const JS_FILE  = null;
    const IS_WISHLIST_PAGE = true;
    const PER_PAGE = 28;

    protected FilmService $filmService;

    public function __construct(FilmService $filmService)
    {
        $this->filmService = $filmService;
    }

    public function index($category = null)
    {
        $films = Film::query()->select('film.*')
            ->rightJoin('wishlist', function ($join) {
                $join->on('wishlist.film_id', '=', 'film.id')
                    ->where('wishlist.user_id', '=', Auth::user()->id);
            })->where(function ($query) {
                $query->where('status', Film::STATUS_PUBLISHED);
                if (Auth::check() && Auth::user()->checkOwnerOrAdmin()) {
                    $query->orWhere('status', Film::STATUS_UNPUBLISHED);
                }
            });

        $categories = $this->filmService->getCategories($films);

        if (isset($category)) {
            $films = $this->filmService->foundByCategory($films, $category);

            $label = Categories::query()->where('url', $category)->value('label');
            if ($label)
                $label = self::TITLE . ', ' . mb_strtoupper($label);
        }

        $films  = $films->paginate(self::PER_PAGE);

        Session::put('previous_tab', url()->current());
        return view('mainmodule::grid', [
            'title'  => $label ?? self:: TITLE,
            'route'  => self::ROUTE,
            'jsFile' => self::JS_FILE,
            'films'  => $films,
            'categories' => $categories,
            'isWishlistPage' => self::IS_WISHLIST_PAGE
        ]);
    }

    public function toggleWishlist (Request $request): JsonResponse
    {
        if (!$request->user())
            return response()->json(['message' => 'Forbidden'], 403);

        $data = $request->validate([
            'film_id' => ['string'],
            'toggleWishlist'  => ['boolean'],
        ]);

        $data['film_id'] = base64_decode($data['film_id']);
        if (!Film::query()->where('id', $data['film_id'])->exists())
            return response()->json(['message' => 'Недопустимый ID фильма'], 403);

        Film::query()->findOrFail($data['film_id']);

        $wishitem = Wishlist::query()->firstOrcreate([
            'film_id' => $data['film_id'],
            'user_id' => $request->user()->id,
        ]);

        if (!$data['toggleWishlist']) {
            $wishitem->delete();
        } else {
            $newsletterData = [
                'user_id' => $request->user()->id,
                'film_id' => $data['film_id'],
                'email'   => $request->user()->email
            ];

            Newsletter::query()->firstOrCreate($newsletterData);
        }

        return response()->json(['bool' => $data['toggleWishlist']]);
    }
}
