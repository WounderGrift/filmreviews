<!DOCTYPE html>
<html lang="ru-RU">
<head>
    <title>Публикация игры на {{ config('app.app_name') }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            background-color: #f5f5f5;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ccc;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #d63e29;
            text-align: center;
        }

        .goto-button {
            display: inline-block;
            background-color: #16ab46;
            color: #fff !important;
            padding: 12px 25px;
            border-radius: 5px;
            text-decoration: none;
        }
    </style>
</head>
<body>

@if ($randomLetter == 1)
    <div class="container">
        <h1>Уведомление о публикации игры {{ $game->name }}</h1>
        <p>Здравствуйте {{ $user->name }},</p>
        <p>Представляю к вашему вниманию, свежий релиз в нашей пиратской бухте {{ config('app.app_name') }}</p>
        <p>{{ $game->name }} - новая игра, готовая к скачиванию.
            Всего-то нужно кликнуть на кнопку и скачать ее торрентом.</p>
        <p>Приятной игры!</p>
        <p><a href="{{ route('detail.index.uri', ['uri' => $game->uri]) }}" class="goto-button"> *ТЫК* </a></p>
        <p>Спасибо, что играете с нами!</p>
        <p>С уважением, автор {{ config('app.app_name') }}</p>
    </div>
@endif

@if ($randomLetter == 2)
    <div class="container">
        <h1>Уведомление о публикации игры {{ $game->name }}</h1>
        <p>Арррр! Новость для настоящих морских волков: наш сайт пополнился новой игрой!</p>
        <p>Достаточно просто кликнуть на кнопку и скачать {{ $game->name }} через торрент. Приятной игры!</p>
        <p><a href="{{ route('detail.index.uri', ['uri' => $game->uri]) }}" class="goto-button"> *ТЫК* </a></p>
        <p>Спасибо, что играете с нами!</p>
        <p>С уважением, автор {{ config('app.app_name') }}</p>
    </div>
@endif

@if ($randomLetter == 3)
    <div class="container">
        <h1>Уведомление о публикации игры {{ $game->name }}</h1>
        <p>Хорошие новости для всех, кто ищет приключения!</p>
        <p>На нашем сайте теперь доступна еще одна игра!</p>
        <p>Просто нажмите на кнопку и скачайте {{ $game->name }} через торрент. Приятной игры!</p>
        <p><a href="{{ route('detail.index.uri', ['uri' => $game->uri]) }}" class="goto-button"> *ТЫК* </a></p>
        <p>Спасибо за вашу поддержку!</p>
        <p>С уважением, автор {{ config('app.app_name') }}</p>
    </div>
@endif

@if ($randomLetter == 4)
    <div class="container">
        <h1>Уведомление о публикации игры {{ $game->name }}</h1>
        <p>Внимание, флибустьеры всех штормовых морей! Поднимайте якоря! Готовьтесь к абордажу!
            На нашем ресурсе появилась новая игра для вашей зеленой библиотеки!</p>
        <p>Все, что вам нужно сделать, это кликнуть на кнопку и скачать {{ $game->name }} через торрент. Приятной игры!</p>
        <p><a href="{{ route('detail.index.uri', ['uri' => $game->uri]) }}" class="goto-button"> *ТЫК* </a></p>
        <p>Спасибо за вашу поддержку!</p>
        <p>С уважением, автор {{ config('app.app_name') }}</p>
    </div>
@endif

@if ($randomLetter == 5)
    <div class="container">
        <h1>Новое приключение в вашей коллекции - {{ $game->name }}!</h1>
        <p>Арррр, флибустьеры общий сбор! Готовьтесь к захвату сокровищ!
            Мы только что пополнили коллекцию новой игрой!</p>
        <p>Достаточно просто кликнуть на кнопку и скачать {{ $game->name }} через торрент. Приятной игры!</p>
        <p><a href="{{ route('detail.index.uri', ['uri' => $game->uri]) }}" class="goto-button"> *ТЫК* </a></p>
        <p>Спасибо, что играете с нами!</p>
        <p>С уважением, команда {{ config('app.app_name') }}</p>
    </div>
@endif

<p>Если вы хотите отписаться от <a href="{{ route('unsubscribeFromEmailAboutPublicGame', [
        'code' => base64_encode($user->id)
    ]) }}">рассылки публикации новых игр</a></p>

<p>Если вы хотите отписаться от <a href="{{ route('unsubscribeFromEmailAboutUpdateGames', [
        'code' => base64_encode($user->id)
    ]) }}">рассылок обновлений по всем играм</a></p>

<p>Если вы хотите отписаться от <a href="{{ route('unsubscribeFromAllNewsletter', [
        'code' => base64_encode($user->id)
    ]) }}">всех рассылок</a></p>
</body>
</html>
