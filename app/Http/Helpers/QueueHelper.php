<?php

namespace App\Http\Helpers;

use App\Http\Jobs\NewsletterQueue;
use App\Models\Newsletter;
use App\Models\Users;

class QueueHelper
{
    //php artisan queue:work --queue=email_public_game,email_update_game --tries=9999999 --backoff=60
    public static function QueueSendEmailAboutPublicGame($game): void
    {
        $users = Users::where('get_letter_release', 1)->get();
        $randomLetter = mt_rand(1, 5);

        foreach ($users as $user) {
            $template = view('mail.publish', compact('game',
                'user', 'randomLetter'))->render();
            NewsletterQueue::dispatch($template, $game, $user->email, 'Release new game')
                ->onQueue('email_public_game');
        }
    }

    public static function QueueSendEmailAboutUpdateGame($game): bool
    {
        $newsletters = Newsletter::where('game_id', $game->id)->get();

        if (!$game->torrents()->orderBy('created_at', 'desc')->exists())
            return false;
        $gameVersion = $game->torrents()->orderBy('created_at', 'desc')->first()->version;

        $randomLetter = mt_rand(1, 5);

        foreach ($newsletters as $newsletter) {
            $email = $newsletter->email;

            $template = view('mail.update', compact('game', 'gameVersion',
                'email', 'randomLetter'))->render();
            NewsletterQueue::dispatch($template, $game, $email, 'Update the game')
                ->onQueue('email_update_game');
        }

        return true;
    }
}
