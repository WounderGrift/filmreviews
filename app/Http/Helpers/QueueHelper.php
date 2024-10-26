<?php

namespace App\Http\Helpers;

use App\Http\Jobs\NewsletterQueue;
use App\Models\Newsletter;
use App\Models\Users;

class QueueHelper
{
    //php artisan queue:work --queue=email_public_film,email_update_film --tries=9999999 --backoff=60
    public static function QueueSendEmailAboutPublicfilm($film): void
    {
        $users = Users::query()->where('get_letter_release', 1)->get();
        $randomLetter = mt_rand(1, 5);

        foreach ($users as $user) {
            $template = view('mail.publish', compact('film',
                'user', 'randomLetter'))->render();
            NewsletterQueue::dispatch($template, $film, $user->email, 'Release new film')
                ->onQueue('email_public_film');
        }
    }

    public static function QueueSendEmailAboutUpdatefilm($film): bool
    {
        $newsletters = Newsletter::query()->where('film_id', $film->id)->get();

        if (!$film->files()->orderBy('created_at', 'desc')->exists())
            return false;
        $filmVersion = $film->files()->orderBy('created_at', 'desc')->first()->version;

        $randomLetter = mt_rand(1, 5);

        foreach ($newsletters as $newsletter) {
            $email = $newsletter->email;

            $template = view('mail.update', compact('film', 'filmVersion',
                'email', 'randomLetter'))->render();
            NewsletterQueue::dispatch($template, $film, $email, 'Update the film')
                ->onQueue('email_update_film');
        }

        return true;
    }
}
