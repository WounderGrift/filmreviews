<?php

use App\Http\Middleware\CheckOwnerOrAdmin;
use App\Http\Middleware\isNotVerify;
use Illuminate\Support\Facades\Route;
use Modules\ProfileModule\Http\Controllers\EditProfilePageController;
use Modules\ProfileModule\Http\Controllers\ProfilePageController;

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
    Route::prefix('/profile')->group(function () {
        Route::post('/create', [ProfilePageController::class, 'create'])->name('profile.create');
        Route::post('/login', [ProfilePageController::class, 'login'])->name('profile.login');
        Route::post('/restore', [ProfilePageController::class, 'restore'])->name('profile.restore');
    });

    Route::middleware(['auth', isNotVerify::class])->group(function () {
        Route::prefix('/profile')->group(function () {
            Route::post('/send-email-verify', [ProfilePageController::class, 'sendEmailVerify'])
                ->name('profile.send-email-verify');
            Route::get('/verify/{token}', [ProfilePageController::class, 'verify'])
                ->name('profile.verify');
        });
    });

    Route::middleware('auth')->group(function () {
        Route::prefix('/profile')->group(function () {
            Route::get('/', [ProfilePageController::class, 'index'])->name('profile.index');
            Route::get('/logout', [ProfilePageController::class, 'logout'])->name('profile.logout');
            Route::get('/edit/{cid}', [EditProfilePageController::class, 'index'])->name('profile.edit');
            Route::put('/update', [EditProfilePageController::class, 'update'])->name('profile.update');
            Route::get('/{cid}', [ProfilePageController::class, 'index'])->name('profile.index.cid');
            Route::post('/chart', [ProfilePageController::class, 'profileChart'])->name('profile.chart');
        });
    });

    Route::middleware([CheckOwnerOrAdmin::class])->group(function () {
        Route::post('/banned', [ProfilePageController::class, 'banned'])->prefix('profile')
            ->name('profile.banned');
    });
});
