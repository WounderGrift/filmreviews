<?php

use App\Http\Middleware\ResetLockIpFeedback;
use Modules\FeedbackModule\Http\Controllers\FeedbackPageController;
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
    Route::middleware(resetLockIpFeedback::class)->get('/feedback', [FeedbackPageController::class, 'index'])
        ->name('feedback.index');
    Route::post('/feedback/send-feedback', [FeedbackPageController::class, 'sendFeedback'])
        ->name('feedback.send');
});
