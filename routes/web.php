<?php

use App\Http\Controllers\SubscribeFromMailController;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'App\Http\Controllers'], function() {
    Route::prefix('/mail')->group(function () {
        Route::get('/unsubscribe-from-email-about-public-film/{code}',
            [SubscribeFromMailController::class, 'unsubscribeFromEmailAboutPublicfilm'])
            ->name('unsubscribeFromEmailAboutPublicfilm');
        Route::get('/subscribe-from-unsubscribe-about-public-film/{code}',
            [SubscribeFromMailController::class, 'subscribeFromUnsubscribeToPublicfilm'])
            ->name('subscribeFromUnsubscribeToPublicfilm');
        Route::get('/unsubscribe-from-email-about-update-film/{code}/{id}',
            [SubscribeFromMailController::class, 'unsubscribeFromEmailAboutUpdatefilm'])
            ->name('unsubscribeFromEmailAboutUpdatefilm');
        Route::get('/subscribe-from-unsubscribe-about-update-film/{code}/{id}',
            [SubscribeFromMailController::class, 'subscribeFromUnsubscribeAboutUpdatefilm'])
            ->name('subscribeFromUnsubscribeAboutUpdatefilm');
        Route::get('/unsubscribe-from-email-about-update-films/{code}',
            [SubscribeFromMailController::class, 'unsubscribeFromEmailAboutUpdatefilms'])
            ->name('unsubscribeFromEmailAboutUpdatefilms');
        Route::get('/unsubscribe-from-all-newsletter/{code}',
            [SubscribeFromMailController::class, 'unsubscribeFromAllNewsletter'])
            ->name('unsubscribeFromAllNewsletter');
    });

    Route::get('/faq', function () {return view('faq.index');})->name('faq');
});
