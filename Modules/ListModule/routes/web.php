<?php

use Modules\ListModule\Http\Controllers\DynamicMenuController;
use App\Http\Middleware\CheckOwnerOrAdmin;
use Illuminate\Support\Facades\Route;

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
        Route::prefix('/dynamic-menu')->group(function () {
            Route::get('/', [DynamicMenuController::class, 'index'])->name('dynamic-menu.index');
            Route::post('/save', [DynamicMenuController::class, 'save'])->name('dynamic-menu.save');
        });
    });
});
