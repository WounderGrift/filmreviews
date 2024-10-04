<?php

use App\Http\Middleware\CheckOwnerOrAdmin;
use Illuminate\Support\Facades\Route;
use Modules\SeriesModule\Http\Controllers\EditSeriesPageController;
use Modules\SeriesModule\Http\Controllers\NewSeriesPageController;

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
    Route::middleware([CheckOwnerOrAdmin::class])->group(function () {
        Route::prefix('/series')->group(function () {
            Route::get('/all', [EditSeriesPageController::class, 'index'])->name('series.list');
            Route::get('/all/{uri}', [EditSeriesPageController::class, 'indexView'])
                ->name('series.list.view');
            Route::get('/new', [NewSeriesPageController::class, 'index'])->name('series.new');
            Route::post('/create', [NewSeriesPageController::class, 'create'])->name('series.create');
            Route::get('/edit/{uri}', [EditSeriesPageController::class, 'indexSeriesDetail'])
                ->name('series.edit');
            Route::post('/update', [EditSeriesPageController::class, 'update'])->name('series.update');
            Route::delete('/delete', [EditSeriesPageController::class, 'delete'])->name('series.delete');

            Route::post('/preview-set-existed', [EditSeriesPageController::class, 'setPreviewFromExists'])
                ->name('series.setPreviewExisted');
            Route::delete('/preview-remove-existed', [EditSeriesPageController::class, 'setPreviewRemoveExists'])
                ->name('series.removeExisted');
        });
    });
});
