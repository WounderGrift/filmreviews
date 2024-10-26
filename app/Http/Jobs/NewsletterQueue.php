<?php

namespace App\Http\Jobs;

use App\Http\Helpers\MailHelper;
use App\Http\Helpers\TelegramLogHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NewsletterQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $template;
    protected $film;
    protected $email;
    protected $theme;

    /**
     * Create a new job instance.
     */
    public function __construct($template, $film, $email, $theme)
    {
        $this->template = $template;
        $this->film  = $film;
        $this->email = $email;
        $this->theme = $theme;
    }

    public function handle(): void
    {
        try {
            $result = MailHelper::compose($this->template, $this->email, $this->theme);

            if (!$result->getData()->success)
                throw new \Exception($result->getData()->message);
        } catch (\Exception $error) {
            TelegramLogHelper::reportCantSendEmailNewsletter($this->film, $error->getMessage());
        }
    }
}
