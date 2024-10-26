<?php

use App\Http\Middleware\CheckOwnerOrAdmin;
use Illuminate\Support\Facades\Route;
use Modules\DetailModule\Http\Controllers\DetailFilmController;
use Modules\DetailModule\Http\Controllers\EditDetailFilmController;

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
            Route::get('/edit/{uri}', [EditDetailFilmController::class, 'index'])->name('detail.edit.index');
            Route::delete('/remove-film', [EditDetailFilmController::class, 'removeFilm'])
                ->name('detail-film.remove');
            Route::delete('/remove-file-softly', [EditDetailFilmController::class, 'removeFileSoftly'])
                ->name('file-softly.remove');
            Route::delete('/remove-file-forced', [EditDetailFilmController::class, 'removeFileForced'])
                ->name('file-forced.remove');
            Route::delete('/remove-screen-softly', [EditDetailFilmController::class, 'removeScreenSoftly'])
                ->name('screen-softly.remove');
            Route::delete('/remove-screen-forced', [EditDetailFilmController::class, 'removeScreenForced'])
                ->name('screen-forced.remove');
            Route::post('/preview-set-existed', [EditDetailFilmController::class, 'setPreviewFromExists'])
                ->name('preview.setExisted');
            Route::post('/preview-remove-existed', [EditDetailFilmController::class, 'setPreviewRemoveExists'])
                ->name('preview.removeExisted');
            Route::post('/release', [EditDetailFilmController::class, 'release'])->name('detail.release');
        });
    });

    Route::prefix('/detail')->group(function () {
        Route::redirect('/', '/');
        Route::post('/subscribe', [DetailFilmController::class, 'subscribe'])->name('detail.subscribe');
        Route::post('/unsubscribe', [DetailFilmController::class, 'unsubscribe'])
            ->name('detail.unsubscribe');
        Route::post('/download', [DetailFilmController::class, 'download'])->name('detail.download');
        Route::put('/toggle-like', [DetailFilmController::class, 'toggleLike'])->name('detail.toggleLike');
        Route::post('/send-comment', [DetailFilmController::class, 'sendComment'])
            ->name('detail.sendComment');
        Route::delete('/remove-comment', [DetailFilmController::class, 'removeComment'])
            ->name('detail.removeComment');
        Route::post('/reset-comment', [DetailFilmController::class, 'resetComment'])
            ->name('detail.resetComment');
        Route::post('/send-report-error', [DetailFilmController::class, 'sendReportError'])
            ->name('detail.sendReportError');
        Route::redirect('/edit', '/');
        Route::get('/{uri}', [DetailFilmController::class, 'index'])->name('detail.index.uri');
    });
});
