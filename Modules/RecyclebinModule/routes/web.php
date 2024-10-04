<?php

use App\Http\Middleware\CheckOwnerOrAdmin;
use Illuminate\Support\Facades\Route;
use Modules\RecyclebinModule\Http\Controllers\RecyclebinPageController;

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

Route::middleware([CheckOwnerOrAdmin::class])->group(function () {
    Route::prefix('recyclebin')->group(function () {
        Route::get('/trashed-games', [RecyclebinPageController::class, 'trashedGameIndex'])
            ->name('trashed.games');

        Route::delete('/remove-games', [RecyclebinPageController::class, 'removeGame'])
            ->name('trashed.removeGame');
        Route::delete('/restore-games', [RecyclebinPageController::class, 'restoreGame'])
            ->name('trashed.restoreGame');
        Route::delete('/cleaning-games', [RecyclebinPageController::class, 'emptyTrashGame'])
            ->name('trashed.cleaningGame');

        Route::get('/trashed-screen', [RecyclebinPageController::class, 'trashedScreenIndex'])
            ->name('trashed.screen');
        Route::delete('/cleaning-screen', [RecyclebinPageController::class, 'emptyTrashScreenshots'])
            ->name('trashed.cleaningScreen');

        Route::get('/trashed-files', [RecyclebinPageController::class, 'trashedTorrentIndex'])
            ->name('trashed.files');
        Route::delete('/cleaning-files', [RecyclebinPageController::class, 'emptyTrashTorrents'])
            ->name('trashed.cleaningFiles');
    });
});
