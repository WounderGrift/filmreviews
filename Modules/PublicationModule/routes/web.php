<?php

use App\Http\Middleware\CheckOwnerOrAdmin;
use Illuminate\Support\Facades\Route;
use Modules\PublicationModule\Http\Controllers\PublicationPageController;
use Modules\PublicationModule\Http\Controllers\UnpublishedPageController;

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
        Route::post('/publishing', [PublicationPageController::class, 'publish'])->name('publishing');

        Route::prefix('publish')->group(function () {
            Route::get('/{uri}', [PublicationPageController::class, 'indexPreview'])->name('publish.uri');
            Route::get('/detail/{uri}', [PublicationPageController::class, 'indexDetail'])
                ->name('publish.detail.uri');
            Route::delete('/remove', [PublicationPageController::class, 'removefilm'])
                ->name('publishing.remove');
        });

        Route::prefix('unpublished')->group(function () {
            Route::get('/', [UnpublishedPageController::class, 'index'])->name('unpublished.index');
            Route::get('/{uri}', [UnpublishedPageController::class, 'detail'])->name('unpublished.detail');
        });
    });
});
