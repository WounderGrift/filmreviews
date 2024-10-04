<?php

use App\Http\Middleware\CheckOwnerOrAdmin;
use Illuminate\Support\Facades\Route;
use Modules\DetailModule\Http\Controllers\DetailsGamePageController;
use Modules\DetailModule\Http\Controllers\EditDetailsPageController;
use Modules\DetailModule\Http\Controllers\NewDetailPageController;

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
        Route::prefix('/detail')->group(function () {
            Route::get('/edit/{uri}', [EditDetailsPageController::class, 'index'])->name('detail.edit.index');
            Route::delete('/remove-game', [EditDetailsPageController::class, 'removeGame'])
                ->name('detail-game.remove');
            Route::delete('/remove-torrent-softly', [EditDetailsPageController::class, 'removeTorrentSoftly'])
                ->name('file-softly.remove');
            Route::delete('/remove-torrent-forced', [EditDetailsPageController::class, 'removeTorrentForced'])
                ->name('file-forced.remove');
            Route::delete('/remove-screen-softly', [EditDetailsPageController::class, 'removeScreenSoftly'])
                ->name('screen-softly.remove');
            Route::delete('/remove-screen-forced', [EditDetailsPageController::class, 'removeScreenForced'])
                ->name('screen-forced.remove');
            Route::post('/preview-set-existed', [EditDetailsPageController::class, 'setPreviewFromExists'])
                ->name('preview.setExisted');
            Route::post('/preview-remove-existed', [EditDetailsPageController::class, 'setPreviewRemoveExists'])
                ->name('preview.removeExisted');
            Route::post('/release', [EditDetailsPageController::class, 'release'])->name('detail.release');
            Route::get('/new', [NewDetailPageController::class, 'index'])->name('detail.new.index');
            Route::post('/create', [NewDetailPageController::class, 'create'])->name('detail.create');
        });
    });

    Route::prefix('/detail')->group(function () {
        Route::redirect('/', '/');
        Route::post('/subscribe', [DetailsGamePageController::class, 'subscribe'])->name('detail.subscribe');
        Route::post('/unsubscribe', [DetailsGamePageController::class, 'unsubscribe'])
            ->name('detail.unsubscribe');
        Route::post('/download', [DetailsGamePageController::class, 'download'])->name('detail.download');
        Route::put('/toggle-like', [DetailsGamePageController::class, 'toggleLike'])->name('detail.toggleLike');
        Route::post('/send-comment', [DetailsGamePageController::class, 'sendComment'])
            ->name('detail.sendComment');
        Route::delete('/remove-comment', [DetailsGamePageController::class, 'removeComment'])
            ->name('detail.removeComment');
        Route::post('/reset-comment', [DetailsGamePageController::class, 'resetComment'])
            ->name('detail.resetComment');
        Route::post('/send-report-error', [DetailsGamePageController::class, 'sendReportError'])
            ->name('detail.sendReportError');
        Route::redirect('/edit', '/');
        Route::get('/{uri}', [DetailsGamePageController::class, 'index'])->name('detail.index.uri');
    });
});
