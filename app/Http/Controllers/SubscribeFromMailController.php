<?php

namespace App\Http\Controllers;

use App\Http\Helpers\TelegramLogHelper;
use App\Models\Game;
use App\Models\Newsletter;
use App\Models\Users;

class SubscribeFromMailController extends Controller
{
    public function unsubscribeFromEmailAboutPublicGame($code)
    {
        $unsubscribeFromEmailAboutPublicGame = true;
        $userId = base64_decode($code);
        $user   = Users::find($userId);

        if ($user->get_letter_release) {
            $user->update([
                'get_letter_release' => false
            ]);

            TelegramLogHelper::reportToggleSubscribeToPublicGame($user, false);
        }

        return view('unsubscribe', compact('unsubscribeFromEmailAboutPublicGame', 'user'));
    }

    public function subscribeFromUnsubscribeToPublicGame($code)
    {
        $subscribeFromUnsubscribeAboutPublicGame = true;
        $userId = base64_decode($code);
        $user   = Users::find($userId);

        if (!$user->get_letter_release) {
            $user->update([
                'get_letter_release' => true
            ]);

            TelegramLogHelper::reportToggleSubscribeToPublicGame($user, true);
        }

        return view('unsubscribe', compact('subscribeFromUnsubscribeAboutPublicGame', 'user'));
    }

    public function unsubscribeFromEmailAboutUpdateGame($code, $id)
    {
        $unsubscribeFromEmailAboutUpdateGame = true;
        $email = base64_decode($code);
        $game  = Game::find($id);

        $newsletter = Newsletter::where('game_id', $id)
            ->where('email', $email);

        $user = Users::where('email', $email)->first();

        if ($user)
            TelegramLogHelper::reportUserToggleNewsletter($user, $game, $newsletter->delete());
        else
            TelegramLogHelper::reportAnonToggleNewsletter($game, $newsletter->delete());

        return view('unsubscribe', compact('unsubscribeFromEmailAboutUpdateGame',
            'email', 'game'));
    }

    public function subscribeFromUnsubscribeAboutUpdateGame($code, $id)
    {
        $subscribeFromUnsubscribeAboutUpdateGame = true;
        $email = base64_decode($code);
        $user   = Users::where('email', $email)->first();
        $game   = Game::find($id);

        $newsletterData = [
            'user_id' => $user->id ?? null,
            'game_id' => $id,
            'email'   => $email
        ];

        $newsletter = Newsletter::query()->firstOrCreate($newsletterData, $newsletterData);

        if (!$newsletter->wasRecentlyCreated) {
            if ($user)
                TelegramLogHelper::reportUserToggleNewsletter($user, $game, true);
            else
                TelegramLogHelper::reportAnonToggleNewsletter($user, $game, true);
        }

        return view('unsubscribe', compact('subscribeFromUnsubscribeAboutUpdateGame',
            'user', 'game'));
    }

    public function unsubscribeFromEmailAboutUpdateGames($code)
    {
        $unsubscribeFromUnsubscribeAboutUpdateGames = true;
        $email = base64_decode($code);

        $newsletters = Newsletter::where('email', $email)->get();
        $count = 0;
        foreach ($newsletters as $newsletter) {
            $newsletter->delete();
            $count++;
        }

        if ($count > 0) {
            $user = Users::where('email', $email)->first();

            if ($user)
                TelegramLogHelper::reportUserUnsubscribeFromAllNewsletter($user, $count);
            else
                TelegramLogHelper::reportAnonUnsubscribeFromAllNewsletter($count);
        }
        return view('unsubscribe', compact('unsubscribeFromUnsubscribeAboutUpdateGames'));
    }

    public function unsubscribeFromAllNewsletter($code)
    {
        $unsubscribeFromAllNewsletter = true;
        $email = base64_decode($code);

        $newsletters = Newsletter::where('email', $email)->get();
        $count = 0;
        foreach ($newsletters as $newsletter) {
            $newsletter->delete();
            $count++;
        }

        $user = Users::where('email', $email)->first();
        if ($user && $user->get_letter_release) {
            $user->update([
                'get_letter_release' => false
            ]);

            TelegramLogHelper::reportToggleSubscribeToPublicGame($user, false);
        }

        if ($count > 0) {
            if ($user)
                TelegramLogHelper::reportUserUnsubscribeFromAllNewsletter($user, $count);
            else
                TelegramLogHelper::reportAnonUnsubscribeFromAllNewsletter($count);
        }
        return view('unsubscribe', compact('unsubscribeFromAllNewsletter',
            'user'));
    }
}
