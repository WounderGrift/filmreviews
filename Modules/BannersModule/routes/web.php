<?php

use App\Http\Middleware\CheckOwner;
use Illuminate\Support\Facades\Route;
use Modules\BannersModule\Http\Controllers\BannerPageController;

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
    Route::middleware(CheckOwner::class)->group(function () {
        Route::prefix('banners')->group(function () {
            Route::get('/big', [BannerPageController::class, 'indexBigBanner'])
                ->name('big-banner.index');
            Route::get('/detail', [BannerPageController::class, 'indexDetailBanner'])
                ->name('detail-banner.index');
            Route::get('/basement', [BannerPageController::class, 'indexBasementBanner'])
                ->name('basement-banner.index');
            Route::post('/jump', [BannerPageController::class, 'bannerJump'])->name('jump-banner');
            Route::post('/banners-save', [BannerPageController::class, 'bannersSave'])->name('banners.save');
            Route::delete('/banner-remove-softly', [BannerPageController::class, 'bannerRemoveSoftly'])
                ->name('banner.remove-softly');
            Route::delete('/banner-remove-forced', [BannerPageController::class, 'bannerRemoveForced'])
                ->name('banner.remove-forced');
            Route::post('/activate-banner', [BannerPageController::class, 'bannerActivate'])
                ->name('banner.activate');
        });
    });
});
