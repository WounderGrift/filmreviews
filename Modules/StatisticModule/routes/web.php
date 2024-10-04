<?php

use App\Http\Middleware\CheckOwnerOrAdmin;
use Illuminate\Support\Facades\Route;
use Modules\DetailModule\Http\Controllers\DetailsGamePageController;
use Modules\StatisticModule\Http\Controllers\ChartsPageController;

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

Route::group(['namespace' => 'App\Http\Controllers'], function () {
    Route::middleware([CheckOwnerOrAdmin::class])->group(function () {
        Route::prefix('chart')->group(function () {
            Route::get('/profiles', [ChartsPageController::class, 'profilesTable'])
                ->name('profiles.chart.table');
            Route::post('/profiles/range', [ChartsPageController::class, 'profilesChart'])
                ->name('profiles.chart.range');
            Route::get('/profiles/{search}', [ChartsPageController::class, 'profilesTable'])
                ->name('profiles.chart.search');

            Route::get('/activity', [ChartsPageController::class, 'commentariesTable'])
                ->name('activity.chart.table');
            Route::post('/activity/range', [ChartsPageController::class, 'activityChart'])
                ->name('activity.chart.range');
            Route::get('/commentaries/{search}', [ChartsPageController::class, 'commentariesTable'])
                ->name('commentaries.chart.search');
            Route::post('/reset-comment', [DetailsGamePageController::class, 'resetComment'])
                ->name('detail.resetComment');

            Route::get('/banners', [ChartsPageController::class, 'bannersTable'])
                ->name('banners.chart.table');
            Route::post('/banners/range', [ChartsPageController::class, 'bannersChart'])
                ->name('banners.chart.range');
        });
    });
});
