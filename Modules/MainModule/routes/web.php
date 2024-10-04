<?php

use App\Http\Middleware\CheckOwnerOrAdmin;
use App\Http\Middleware\isVerify;
use Illuminate\Support\Facades\Route;
use Modules\MainModule\Http\Controllers\AllGamesPageController;
use Modules\MainModule\Http\Controllers\AllSoftPageController;
use Modules\MainModule\Http\Controllers\ExpirationGamePageController;
use Modules\MainModule\Http\Controllers\NewGamesPageController;
use Modules\MainModule\Http\Controllers\RecommendedGamesPageController;
use Modules\MainModule\Http\Controllers\RepackGamesPageController;
use Modules\MainModule\Http\Controllers\RussianGamesPageController;
use Modules\MainModule\Http\Controllers\SearchGamesPageController;
use Modules\MainModule\Http\Controllers\SeriesGamePageController;
use Modules\MainModule\Http\Controllers\WaitingGamesPageController;
use Modules\MainModule\Http\Controllers\WeakGamesPageController;
use Modules\MainModule\Http\Controllers\WishlistGamePageController;
use Modules\MainModule\Http\Controllers\YearGamesPageController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['namespace' => 'App\Http\Controllers'], function() {
    Route::middleware(['auth', isVerify::class])->group(function () {
        Route::prefix('/wishlist')->group(function () {
            Route::get('/', [WishlistGamePageController::class, 'index'])->name('wishlist.index');
            Route::get('/search-for-wishlist', [SearchGamesPageController::class, 'searchForWishlist'])
                ->name('search.wishlist');
            Route::get('/{category}', [WishlistGamePageController::class, 'index'])->name('wishlist.index.category');
            Route::put('/toggle-wishlist', [WishlistGamePageController::class, 'toggleWishlist'])
                ->name('wishlist.toggle');
        });
    });

    Route::prefix('unpublished')->group(function () {
        Route::get('/search-for-unpublished', [SearchGamesPageController::class, 'searchForUnpublished'])
            ->name('search.unpublished');
    });

    Route::get('/search', [SearchGamesPageController::class, 'index'])->name('search.index');
    Route::get('/search-for-series', [SearchGamesPageController::class, 'searchForSeries'])
        ->name('search.series');

    Route::get('/', [RecommendedGamesPageController::class, 'index'])->name('main.index');
    Route::redirect('/recommended', '/');
    Route::get('/recommended/{ids}', [RecommendedGamesPageController::class, 'recommendedDetailIndex'])
        ->name('recommended.index');
    Route::get('/all', [AllGamesPageController::class, 'index'])->name('all.index');
    Route::get('/series', [SeriesGamePageController::class, 'index'])->name('series.index');
    Route::get('/new', [NewGamesPageController::class, 'index'])->name('new.index');
    Route::get('/waiting', [WaitingGamesPageController::class, 'index'])->name('waiting.index');
    Route::get('/russian', [RussianGamesPageController::class, 'index'])->name('russian.index');
    Route::get('/weak', [WeakGamesPageController::class, 'index'])->name('weak.index');
    Route::get('/repacks', [RepackGamesPageController::class, 'index'])->name('repacks.index');
    Route::get('/soft', [AllSoftPageController::class, 'index'])->name('soft.index');

    Route::get('/series/{uri}', [SeriesGamePageController::class, 'indexSeries'])
        ->where('uri', '^(?!all).*$')->name('series.indexSeries');
    Route::get('/all/{category}', [AllGamesPageController::class, 'index'])->name('all.index.category');
    Route::get('/new/{category}', [NewGamesPageController::class, 'index'])->name('new.index.category');
    Route::get('/waiting/{category}', [WaitingGamesPageController::class, 'index'])
        ->name('waiting.index.category');
    Route::get('/russian/{category}', [RussianGamesPageController::class, 'index'])
        ->name('russian.index.category');
    Route::get('/weak/{category}', [WeakGamesPageController::class, 'index'])->name('weak.index.category');
    Route::get('/repacks/{category}', [RepackGamesPageController::class, 'index'])
        ->name('repacks.index.category');
    Route::get('/year/{category}', [YearGamesPageController::class, 'index'])->name('year.index.category');
    Route::get('/soft/{category}', [AllSoftPageController::class, 'index'])->name('soft.index.category');

    Route::middleware([CheckOwnerOrAdmin::class])->group(function () {
        Route::prefix('expiration')->group(function () {
            Route::get('/', [ExpirationGamePageController::class, 'index'])->name('expiration.index');
            Route::get('/{uri}', [ExpirationGamePageController::class, 'detail'])->name('expiration.detail');
        });
    });
});
