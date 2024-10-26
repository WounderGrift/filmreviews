<?php

namespace App\Http\Controllers;

use App\Http\Helpers\TelegramLogHelper;
use App\Models\Film;
use App\Models\Newsletter;
use App\Models\Users;

class SubscribeFromMailController extends Controller
{
    public function unsubscribeFromEmailAboutPublicfilm($code)
    {
        $unsubscribeFromEmailAboutPublicfilm = true;
        $userId = base64_decode($code);
        $user   = Users::query()->find($userId);

        if ($user->get_letter_release) {
            $user->update([
                'get_letter_release' => false
            ]);

            TelegramLogHelper::reportToggleSubscribeToPublicfilm($user, false);
        }

        return view('unsubscribe', compact('unsubscribeFromEmailAboutPublicfilm', 'user'));
    }

    public function subscribeFromUnsubscribeToPublicfilm($code)
    {
        $subscribeFromUnsubscribeAboutPublicfilm = true;
        $userId = base64_decode($code);
        $user   = Users::query()->find($userId);

        if (!$user->get_letter_release) {
            $user->update([
                'get_letter_release' => true
            ]);

            TelegramLogHelper::reportToggleSubscribeToPublicfilm($user, true);
        }

        return view('unsubscribe', compact('subscribeFromUnsubscribeAboutPublicfilm', 'user'));
    }

    public function unsubscribeFromEmailAboutUpdatefilm($code, $id)
    {
        $unsubscribeFromEmailAboutUpdatefilm = true;
        $email = base64_decode($code);
        $film  = Film::query()->find($id);

        $newsletter = Newsletter::query()->where('film_id', $id)
            ->where('email', $email);

        $user = Users::query()->where('email', $email)->first();

        if ($user)
            TelegramLogHelper::reportUserToggleNewsletter($user, $film, $newsletter->delete());
        else
            TelegramLogHelper::reportAnonToggleNewsletter($film, $newsletter->delete());

        return view('unsubscribe', compact('unsubscribeFromEmailAboutUpdatefilm',
            'email', 'film'));
    }

    public function subscribeFromUnsubscribeAboutUpdatefilm($code, $id)
    {
        $subscribeFromUnsubscribeAboutUpdatefilm = true;
        $email = base64_decode($code);
        $user   = Users::query()->where('email', $email)->first();
        $film   = Film::query()->find($id);

        $newsletterData = [
            'user_id' => $user->id ?? null,
            'film_id' => $id,
            'email'   => $email
        ];

        $newsletter = Newsletter::query()->firstOrCreate($newsletterData, $newsletterData);

        if (!$newsletter->wasRecentlyCreated) {
            if ($user)
                TelegramLogHelper::reportUserToggleNewsletter($user, $film, true);
            else
                TelegramLogHelper::reportAnonToggleNewsletter($user, $film, true);
        }

        return view('unsubscribe', compact('subscribeFromUnsubscribeAboutUpdatefilm',
            'user', 'film'));
    }

    public function unsubscribeFromEmailAboutUpdatefilms($code)
    {
        $unsubscribeFromUnsubscribeAboutUpdatefilms = true;
        $email = base64_decode($code);

        $newsletters = Newsletter::query()->where('email', $email)->get();
        $count = 0;
        foreach ($newsletters as $newsletter) {
            $newsletter->delete();
            $count++;
        }

        if ($count > 0) {
            $user = Users::query()->where('email', $email)->first();

            if ($user)
                TelegramLogHelper::reportUserUnsubscribeFromAllNewsletter($user, $count);
            else
                TelegramLogHelper::reportAnonUnsubscribeFromAllNewsletter($count);
        }
        return view('unsubscribe', compact('unsubscribeFromUnsubscribeAboutUpdatefilms'));
    }

    public function unsubscribeFromAllNewsletter($code)
    {
        $unsubscribeFromAllNewsletter = true;
        $email = base64_decode($code);

        $newsletters = Newsletter::query()->where('email', $email)->get();
        $count = 0;
        foreach ($newsletters as $newsletter) {
            $newsletter->delete();
            $count++;
        }

        $user = Users::query()->where('email', $email)->first();
        if ($user && $user->get_letter_release) {
            $user->update([
                'get_letter_release' => false
            ]);

            TelegramLogHelper::reportToggleSubscribeToPublicfilm($user, false);
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
