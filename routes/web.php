<?php

use App\Http\Controllers\SubscribeFromMailController;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'App\Http\Controllers'], function() {
    Route::prefix('/mail')->group(function () {
        Route::get('/unsubscribe-from-email-about-public-game/{code}',
            [SubscribeFromMailController::class, 'unsubscribeFromEmailAboutPublicGame'])
            ->name('unsubscribeFromEmailAboutPublicGame');
        Route::get('/subscribe-from-unsubscribe-about-public-game/{code}',
            [SubscribeFromMailController::class, 'subscribeFromUnsubscribeToPublicGame'])
            ->name('subscribeFromUnsubscribeToPublicGame');
        Route::get('/unsubscribe-from-email-about-update-game/{code}/{id}',
            [SubscribeFromMailController::class, 'unsubscribeFromEmailAboutUpdateGame'])
            ->name('unsubscribeFromEmailAboutUpdateGame');
        Route::get('/subscribe-from-unsubscribe-about-update-game/{code}/{id}',
            [SubscribeFromMailController::class, 'subscribeFromUnsubscribeAboutUpdateGame'])
            ->name('subscribeFromUnsubscribeAboutUpdateGame');
        Route::get('/unsubscribe-from-email-about-update-games/{code}',
            [SubscribeFromMailController::class, 'unsubscribeFromEmailAboutUpdateGames'])
            ->name('unsubscribeFromEmailAboutUpdateGames');
        Route::get('/unsubscribe-from-all-newsletter/{code}',
            [SubscribeFromMailController::class, 'unsubscribeFromAllNewsletter'])
            ->name('unsubscribeFromAllNewsletter');
    });

    Route::get('/faq', function () {return view('faq.index');})->name('faq');
});
