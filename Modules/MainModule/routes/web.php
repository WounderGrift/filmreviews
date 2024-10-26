<?php

use App\Http\Middleware\isVerify;
use Illuminate\Support\Facades\Route;
use Modules\MainModule\Http\Controllers\AllFilmsController;
use Modules\MainModule\Http\Controllers\NewFilmsController;
use Modules\MainModule\Http\Controllers\RecommendedFilmsController;
use Modules\MainModule\Http\Controllers\SearchFilmsController;
use Modules\MainModule\Http\Controllers\SeriesFilmsController;
use Modules\MainModule\Http\Controllers\WaitingFilmsController;
use Modules\MainModule\Http\Controllers\WishlistFilmsController;
use Modules\MainModule\Http\Controllers\YearFilmsController;

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
            Route::get('/', [WishlistFilmsController::class, 'index'])->name('wishlist.index');
            Route::get('/search-for-wishlist', [SearchFilmsController::class, 'searchForWishlist'])
                ->name('search.wishlist');
            Route::get('/{category}', [WishlistFilmsController::class, 'index'])->name('wishlist.index.category');
            Route::put('/toggle-wishlist', [WishlistFilmsController::class, 'toggleWishlist'])
                ->name('wishlist.toggle');
        });
    });

    Route::prefix('unpublished')->group(function () {
        Route::get('/search-for-unpublished', [SearchFilmsController::class, 'searchForUnpublished'])
            ->name('search.unpublished');
    });

    Route::get('/search', [SearchFilmsController::class, 'index'])->name('search.index');
    Route::get('/search-for-series', [SearchFilmsController::class, 'searchForSeries'])
        ->name('search.series');

    Route::get('/', [RecommendedFilmsController::class, 'index'])->name('main.index');
    Route::redirect('/recommended', '/');
    Route::get('/recommended/{ids}', [RecommendedFilmsController::class, 'recommendedDetailIndex'])
        ->name('recommended.index');
    Route::get('/all', [AllFilmsController::class, 'index'])->name('all.index');
    Route::get('/series', [SeriesFilmsController::class, 'index'])->name('series.index');
    Route::get('/new', [NewFilmsController::class, 'index'])->name('new.index');
    Route::get('/waiting', [WaitingFilmsController::class, 'index'])->name('waiting.index');

    Route::get('/series/{uri}', [SeriesFilmsController::class, 'indexSeries'])
        ->where('uri',
            '^(?!all|edit|new|create|update|delete|preview-set-existed|preview-remove-existed).*$')
        ->name('series.indexSeries');
    Route::get('/all/{category}', [AllFilmsController::class, 'index'])->name('all.index.category');
    Route::get('/new/{category}', [NewFilmsController::class, 'index'])->name('new.index.category');
    Route::get('/waiting/{category}', [WaitingFilmsController::class, 'index'])
        ->name('waiting.index.category');
    Route::get('/year/{category}', [YearFilmsController::class, 'index'])->name('year.index.category');
});
