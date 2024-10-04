<?php

namespace App\Http\Helpers;

use App\Models\Game;
use App\Models\Torrents;
use App\Models\Users;
use Exception;
use Illuminate\Support\Facades\Storage;

class TelegramLogHelper
{
    public static function sendMessageToChannel(string $text, string $photoUrl = null): bool
    {
        try {
            $postFields = [
                'chat_id' => config('app.keys.telegram_channel_id'),
                'photo' => $photoUrl,
                'caption' => $text,
                'parse_mode' => 'Markdown'
            ];

//            TODO telegram messages
            $ch = curl_init("https://api.telegram.org/bot". config('app.keys.telegram_quartermaster_token') . "/sendPhoto");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HEADER, false);

            $response = curl_exec($ch);
            if ($response === false)
                throw new Exception('Не пришел ответ');
            curl_close($ch);
            sleep(1);

            return true;
        } catch (Exception $error) {
            $bot = 'Наблюдатель';
            $template = view('mail.riot', compact('bot', 'error'))->render();
            MailHelper::compose($template, config('mail.owner_mail'), 'Telegram bot rebelled');

            return false;
        }
    }

    public static function sendLog(string $text, string $theme = null, int $topic = null): bool
    {
        $timestamp = now()->format('ymd');
        $message   = '';

        if (isset($theme))
            $message = '#' . $theme;

        $message .= match ($topic) {
            305     => ' #publish_'   . $timestamp . "\n" . $text,
            296     => ' #report_'    . $timestamp . "\n" . $text,
            214     => ' #overdue_'   . $timestamp . "\n" . $text,
            178     => ' #changed_'   . $timestamp . "\n" . $text,
            135     => ' #feedback_'  . $timestamp . "\n" . $text,
            132     => ' #subscribe_' . $timestamp . "\n" . $text,
            128     => ' #wishlist_'  . $timestamp . "\n" . $text,
            61      => ' #comment_'   . $timestamp . "\n" . $text,
            5       => ' #error_'     . $timestamp . "\n" . $text,
            default => ' #info_'      . $timestamp . "\n" . $text,
        };

        try {
            $getQuery = [
                "chat_id" => config('app.keys.telegram_log_chat_id'),
                "text" => $message,
                "message_thread_id" => $topic,
                "parse_mode" => "html"
            ];

//            TODO telegram messages
            $ch = curl_init("https://api.telegram.org/bot". config('app.keys.telegram_quartermaster_token')
                ."/sendMessage?" . http_build_query($getQuery));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_exec($ch);
            curl_close($ch);
            sleep(1);

            return true;
        } catch (Exception $error) {
            $bot = 'Наблюдатель';
            $template = view('mail.riot', compact('bot', 'error'))->render();
            MailHelper::compose($template, config('mail.owner_mail'), 'Telegram bot rebelled');

            return false;
        }
    }

    public static function whatSendMessageToChannel(Game $game, bool $isPublish): void
    {
        $file = Torrents::where('version', $game->torrents->max('version'))
            ->where('game_id', $game->id)
            ->first();

        if ($isPublish)
            $response = "*Публикация \n$game->name";
        else
            $response = "*Обновление \n$game->name ($file->version)";

        $repackFrom = $file?->repacks?->label;
        if ($repackFrom)
            $response .= " RePack от $repackFrom";

        $size = $file?->size;
        if ($size)
            $response .= " | $size*";

        $url = route('detail.index.uri', ['uri' => $game->uri]);

        $parsedUrl = parse_url($url);
        $previewDetail = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . Storage::url($game->detail->preview_detail);

        $info = strip_tags(json_decode($game->detail?->info)->description);
        $resultText = TextHelper::fiveSentences($info, 2);
        $response .= "\n\n$resultText..";

        $response .= "\n\n[Ссылка на игру]($url)";

        if ($game?->series?->uri) {
            $url = route('series.indexSeries', ['uri' => $game->series->uri]);
            $response .= "\n[Ссылка на серию]($url)";
        }

        $tag = empty($game?->series?->uri) ? $game->uri : $game->series->uri;
        $tag = str_replace('-', ' ', trim($tag));
        $tag = ucwords($tag);

        $response .= "\n\n#" . str_replace(' ', '\_', $tag);
        $response .= $game->is_soft ? " #Software" : " #Games";

        self::sendMessageToChannel($response, $previewDetail);
    }

    public static function reportComment(Users $user, $quote, string $text, string $url, bool $isError): void
    {
        $url = route('detail.index.uri', ['uri' => $url]);

        $response = "Пользователь: $user->name (#$user->cid)";
        if ($isError)
            $response .= "\nНе получается оставить комментарий.";
        if ($quote)
            $response .= "\nОтвет на: $quote";
        $response .= "\nКомментарий: $text\n\nСсылка: $url";

        $profileUrl = route('profile.index.cid', ['cid' => $user->cid]);
        $response  .= "\n\nСсылка на профиль: $profileUrl";

        $isError
            ? self::sendLog($response,"comment", 5)
            : self::sendLog($response,"comment", 61);
    }

    public static function reportDeleteComment(Users $user, $quote, string $text, string $url, bool $isError): void
    {
        $url = route('detail.index.uri', ['uri' => $url]);

        $response = "Пользователь: $user->name (#$user->cid)";
        if ($isError)
            $response .= "\nНе получается удалить комментарий.";

        if ($quote) {
            $response .= "\nУдалил ответ: $text";
            $response .= "\nНа комментарий: $quote\n\nСсылка: $url";
        } else
            $response .= "\nУдалил комментарий: $text\n\nСсылка: $url";

        $profileUrl = route('profile.index.cid', ['cid' => $user->cid]);
        $response  .= "\n\nСсылка на профиль: $profileUrl";

        $isError
            ? self::sendLog($response,"comment", 5)
            : self::sendLog($response,"comment", 61);
    }

    public static function reportWarningTimezone(Users $user, string $tzDenied, string $tzAccess): void
    {
        $response  = "Пользователь: $user->name (#$user->cid)";
        $response .= "\nВременная зона ($tzDenied) не прошла проверку и была выставлена ($tzAccess)";

        self::sendLog($response,"timezone", 5);
    }

    public static function reportCreateProfile(Users $user, bool $isError): void
    {
        $url = route('profile.index.cid', ['cid' => $user->cid]);
        $response = "Пользователь: $user->name (#$user->cid) ";
        $response .= $isError
            ? "получил ошибку при создании профиля"
            : "создал профиль";
        $response .= "\n\nСсылка: $url";

        $isError
            ? self::sendLog($response, "create_profile", 5)
            : self::sendLog($response, "create_profile");
    }

    public static function reportCreateProfileError($eMessage, $eCode): void
    {
        $response  = "Получена ошибка при создании профиля";
        $response .= "\n\nОшибка $eCode: $eMessage";

        self::sendLog($response, "create_profile", 5);
    }

    public static function reportLoginError(Users $user, $eMessage, $eCode): void
    {
        $url = route('profile.index.cid', ['cid' => $user->cid]);

        $response  = "Пользователь: $user->name (#$user->cid) ";
        $response .= "получил ошибку при входе в профиль";
        $response .= "\n\nОшибка $eCode: $eMessage";
        $response .= "\n\nСсылка: $url";

        self::sendLog($response, "login_profile", 5);
    }

    public static function reportCantSendEmailForRestoreProfile($eMessage, $eCode): void
    {
        $response  = "\nНе удается отправить письмо с восстановлением профиля";
        $response .= "\n\nОшибка $eCode: $eMessage";

        self::sendLog($response,"send_email", 5);
    }

    public static function reportCantSendEmail(Users $user, $eMessage, $eCode): void
    {
        $url = route('profile.index.cid', ['cid' => $user->cid]);

        $response  = "Пользователь: $user->name (#$user->cid)";
        $response .= "\nНе удалось отправить письмо";
        $response .= "\n\nОшибка $eCode: $eMessage";
        $response .= "\n\nСсылка: $url";

        self::sendLog($response,"send_email", 5);
    }

    public static function reportCantGenerateToken(Users $user): void
    {
        $url = route('profile.index.cid', ['cid' => $user->cid]);

        $response  = "Пользователь: $user->name (#$user->cid)";
        $response .= "\nНе удалось сгенерировать разовый токен";
        $response .= "\n\nСсылка: $url";

        self::sendLog($response,"database", 5);
    }

    public static function reportVerifyProfile($user, bool $isError): void
    {
        $url = route('profile.index.cid', ['cid' => $user->cid]);

        $response  = "Пользователь: $user->name (#$user->cid)";
        $response .= $isError
            ? "\nНе удалось верифицировать профиль"
            : "\nВерифицировал профиль";
        $response .= "\n\nСсылка: $url";

        $isError
            ? self::sendLog($response,"verify_profile",5)
            : self::sendLog($response,"verify_profile");
    }

    public static function reportToggleWishlist(Users $user, $game, bool $add, bool $isError): void
    {
        $profileUrl = route('profile.index.cid', ['cid' => $user->cid]);
        $gameUri    = route('detail.index.uri', ['uri' => $game->uri]);

        $response  = "Пользователь: $user->name (#$user->cid)";
        if ($add) {
            $response .= $isError
                ? "\nНе удалось добавить игру ($game->name) в список желаемого"
                : "\nДобавил игру ($game->name) в список желаемого";
        } else {
            $response .= $isError
                ? "\nНе удалось удалить игру ($game->name) из списка желаемого"
                : "\nУдалил игру ($game->name) из списка желаемого";
        }
        $response .= "\n\nСсылка на профиль: $profileUrl";
        $response .= "\n\nСсылка на игру: $gameUri";

        $isError
            ? self::sendLog($response,"wishlist",5)
            : self::sendLog($response,"wishlist", 128);
    }

    public static function reportToggleLikeForGame(Users $user, string $gameUri, bool $add): void
    {
        $profileUrl = route('profile.index.cid', ['cid' => $user->cid]);
        $gameUri    = route('detail.index.uri', ['uri' => $gameUri]);

        $response  = "Пользователь: $user->name (#$user->cid)";
        if ($add) {
            $response .= "\nПоставил лайк на игру ($gameUri)";
        } else {
            $response .= "\nУбрал лайк с игры  ($gameUri)";
        }

        $response .= "\n\nСсылка на профиль: $profileUrl";

        self::sendLog($response,"like", 128);
    }

    public static function reportToggleLikeForComment(Users $who, string $gameUri, array $like, bool $add): void
    {
        $profileUrl = route('profile.index.cid', ['cid' => $who->cid]);
        $gameUri    = route('detail.index.uri', ['uri' => $gameUri]);

        $response  = "Пользователь: $who->name (#$who->cid)";

        if ($add) {
            $response .= "\nПоставил лайк на комментарий пользователя (#{$like['whomCid']})";
        } else {
            $response .= "\nУбрал лайк с комментария пользователя (#{$like['whomCid']})";
        }

        $response .= "\n\nКомментарий: {$like['comment']}";

        $response .= "\n\nСсылка на профиль: $profileUrl";
        $response .= "\n\nСсылка на игру: $gameUri";

        self::sendLog($response,"like",128);
    }

    public static function reportUserToggleNewsletter(Users $user, Game $game, bool $isExist): void
    {
        $profileUrl = route('profile.index.cid', ['cid' => $user->cid]);
        $gameUri    = route('detail.index.uri', ['uri' => $game->uri]);

        $response  = "Пользователь: $user->name (#$user->cid)";

        $response .= $isExist
            ? "\nПодписался на уведомления игры  $game->name"
            : "\nОтписался от уведомлений на игру  $game->name";

        $response .= "\n\nСсылка на профиль: $profileUrl";
        $response .= "\n\nСсылка на игру: $gameUri";

        self::sendLog($response,$isExist ? "subscribe" : "unsubscribe",132);
    }

    public static function reportAnonToggleNewsletter(Game $game, bool $isExist): void
    {
        $gameUri  = route('detail.index.uri', ['uri' => $game->uri]);
        $response = "Аноним";

        $response .= $isExist
            ? "\nПодписался на уведомления игры  $game->name"
            : "\nОтписался от уведомлений на игру  $game->name";

        $response .= "\n\nСсылка на игру: $gameUri";

        self::sendLog($response,$isExist ? "subscribe" : "unsubscribe",132);
    }

    public static function reportCustomerError(Game $game, string $text): void
    {
        $gameUri  = route('detail.index.uri', ['uri' => $game->uri]);
        $response = "Отчет об ошибке:\n";
        $response .= "$text\n";

        $response .= "\nСсылка на игру: $gameUri";
        self::sendLog($response,"customer_report",296);
    }

    public static function reportToggleSubscribeToPublicGame(Users $user, bool $isExist): void
    {
        $profileUrl = route('profile.index.cid', ['cid' => $user->cid]);

        $response  = "Пользователь: $user->name (#$user->cid)";

        $response .= $isExist
            ? "\nПодписался на уведомления о новых играх"
            : "\nОтписался от уведомлений о новых играх";

        $response .= "\n\nСсылка на профиль: $profileUrl";

        self::sendLog($response,$isExist ? "subscribe" : "unsubscribe" ,132);
    }

    public static function reportUserUnsubscribeFromAllNewsletter(Users $user, $count): void
    {
        $profileUrl = route('profile.index.cid', ['cid' => $user->cid]);

        $response  = "Пользователь: $user->name (#$user->cid)";
        $response .= "\nОтписался от всех уведомлений об обновлении игр в количестве " . $count . " штук";
        $response .= "\n\nСсылка на профиль: $profileUrl";

        self::sendLog($response,"unsubscribe",132);
    }

    public static function reportAnonUnsubscribeFromAllNewsletter($count): void
    {
        $response  = "Аноним";
        $response .= "\nОтписался от всех уведомлений об обновлении игр в количестве " . $count . " штук";

        self::sendLog($response,"unsubscribe",132);
    }

    public static function reportFeedback(?Users $user, string $email, string $message): void
    {
        $response = $user
            ? "Пользователь: $user->name (#$user->cid)\nОт емейла $email"
            : "\nАноним под емейлом $email";

        $message = json_decode($message);

        $response .= "\n\nОтправил вам сообщение: \n$message";

        if ($user) {
            $profileUrl = route('profile.index.cid', ['cid' => $user->cid]);
            $response  .= "\n\nСсылка на профиль: $profileUrl";
        }

        self::sendLog($response,"feedback",135);
    }

    public static function reportFeedbackError(?Users $user, string $email, string $message, $eMessage, $eCode): void
    {
        $response = $user
            ? "Пользователь: $user->name (#$user->cid)\nОт емейла $email"
            : "\nАноним под емейлом $email";

        $message = json_decode($message);

        $response .= "\nНе смог отправить вам сообщение: \n$message";
        $response .= "\n\nОшибка ($eCode): \n$eMessage";

        if ($user) {
            $profileUrl = route('profile.index.cid', ['cid' => $user->cid]);
            $response  .= "\n\nСсылка на профиль: $profileUrl";
        }

        self::sendLog($response,"feedback",5);
    }

    public static function reportChangeUser(Users $who, Users $userOld, Users $userNew, bool $isError): void
    {
        $response = "Пользователь: $who->name (#$who->cid)";
        $changed  = false;

        if (!$isError)
        {
            $response .= "\nИзменил профиль $userOld->name (#$userOld->cid) на:\n$userNew->name (#$userNew->cid)\n";

            if ($userOld->email !== $userNew->email) {
                $response .= "Емейл: $userOld->email => $userNew->email\n";
                $changed = true;
            }

            if ($userOld->password !== $userNew->password) {
                $response .= "Возможно был изменен пароль\n";
                $changed = true;
            }

            if ($userOld->avatar_name !== $userNew->avatar_name)
            {
                if ($userOld->avatar_name)
                    $response .= "Обложка прфоиля: $userOld->avatar_name => $userNew->avatar_name\n";
                else
                    $response .= "Обложка прфоиля: $userNew->avatar_name\n";
                $changed = true;
            }

            if ($userOld->status !== $userNew->status) {
                $response .= "Статус: $userOld->status => $userNew->status\n";
                $changed = true;
            }

            if ($userOld->role !== $userNew->role) {
                $response .= "Роль: {$userOld->getRoleOption()[$userOld->role]} =>"
                    . " {$userNew->getRoleOption()[$userNew->role]}\n";
                $changed = true;
            }

            if ($userOld->about_me !== $userNew->about_me) {
                $response .= "Обо мне: $userOld->about_me => $userNew->about_me\n";
                $changed = true;
            }

            $profileUrl = route('profile.index.cid', ['cid' => $userNew->cid]);
        } else {
            $response  .= "\nНе смог изменить свой профиль:\n$userNew->name (#$userNew->cid)";
            $profileUrl = route('profile.index.cid', ['cid' => $userOld->cid]);
        }

        $response .= "\n\nСсылка на профиль: $profileUrl";

        if ($changed) {
            $isError
                ? self::sendLog($response, "changed", 5)
                : self::sendLog($response, "changed", 178);
        }
    }

    public static function reportBannedUser($user, $who, $isBanned): void {
        $response = "$who->name (#$who->name)";
        if ($isBanned)
            $response .= "\nЗаблокировал пользователя $user->name (#$user->cid)";
        else
            $response .= "\nРазблокировал пользователя $user->name (#$user->cid)";

        $url = route('profile.index.cid', ['cid' => $user->cid]);
        $response .= "\n\nСсылка на профиль: $url";

        self::sendLog($response,"changed",178);
    }

    public static function reportCantUpdateGame($game, $error): void
    {
        $response = "ВНИМАНИЕ\n\n";
        $response .= "Не удалось сохранить игру: $game->name (#$game->id)";

        $response .= "\nОшибка:\n$error";

        $url = route('detail.index.uri', ['uri' => $game->uri]);
        $response .= "\n\nСсылка на игру: $url";

        self::sendLog($response,"changed",5);
    }

    public static function reportCantPublishGame($game, $error): void
    {
        $response = "ВНИМАНИЕ\n\n";
        $response .= "Не удалось опубликовать игру: $game->name (#$game->id)";

        $response .= "\nОшибка:\n$error";

        $url = route('detail.index.uri', ['uri' => $game->uri]);
        $response .= "\n\nСсылка на игру: $url";

        self::sendLog($response,"changed",5);
    }

    public static function reportCreateSeries($series, $user, $isCreate = false): void
    {
        if ($isCreate) {
            $response = "\nСоздание серии игры: $series->name\n"
                . route('series.detail', ['uri' => $series->uri]);
            $response .= "\n\n $user->name (#$user->cid) Создал серию";
            self::sendLog($response, "create", 305);
        } else {
            $response  = "\nОбновление серии игры:  $series->name\n"
                . route('series.detail', ['uri' => $series->uri]);
            $response .= "\n\n $user->name (#$user->cid) Обновил серию";

            self::sendLog($response, "update", 305);
        }
    }

    public static function reportOverdueGame($games): void
    {
        $plural     = false;
        $aboutGames = "\n";

        foreach ($games as $index => $game)
        {
            if ($index > 0)
                $plural = true;

            $url = route('detail.index.uri', ['uri' => $game->uri]);
            $aboutGames .= "\n<a href=\"$url\">$game->name (#$game->id)</a>";
        }

        $response = $plural
            ? "Игры уже должны была выйти:"
            : "Игра уже должна была выйти:";

        $response .= $aboutGames;
        self::sendLog($response,"overdue",214);
    }

    public static function reportCantSendEmailNewsletter($game, $error): void
    {
        $response = "\nНе удалось отправить письмо о релизе игры\n"
            . route('detail.index.uri', ['uri' => $game->uri]);

        $response .= "\n\nОшибка: $error\n";
        self::sendLog($response,"queue",5);
    }

    public static function reportPublishAndUpdateGame($game, $user, $isPublish = false): void
    {
        if ($isPublish) {
            $response = "\nПубликация игры\n"
                . route('detail.index.uri', ['uri' => $game->uri]);
            $response .= "\n\n $user->name (#$user->cid) Создал запись игры $game->name и скрыл ее";

            self::sendLog($response, "publish", 305);
        } else {
            $response = "\nОбновление игры\n"
                . route('detail.index.uri', ['uri' => $game->uri]);
            $response .= "\n\n $user->name (#$user->cid) Обновил и скрыл игру $game->name";

            self::sendLog($response, "update", 305);
        }
    }

    public static function reportDatabaseError(): void
    {
        $response  = "ВНИМАНИЕ";
        $response .= "\nГлавная база данных не работает, предпримите меры";

        self::sendLog($response,"database",5);
    }
}
