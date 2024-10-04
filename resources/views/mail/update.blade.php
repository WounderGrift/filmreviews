<!DOCTYPE html>
<html lang="ru-RU">
<head>
    <title>Обновление игры на {{ config('app.app_name') }}</title>
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
        <h1>Обновление игры {{ $game->name }} ({{ $gameVersion }})</h1>
        <p>Аррр, морские волки!</p>
        <p>Мы рады сообщить вам о новой версии игры {{ $game->name }}.</p>
        <p>Подробнее ознакомится можно, пришвартовавшись по ссылке ниже:</p>
        <p><a href="{{ route('detail.index.uri', ['uri' => $game->uri]) }}" class="goto-button">*ТЫК*</a></p>
        <p>Спасибо за вашу лояльность!</p>
        <p>С уважением, автор {{ config('app.app_name') }}</p>
    </div>
@endif

@if ($randomLetter == 2)
    <div class="container">
        <h1>Обновление игры {{ $game->name }} ({{ $gameVersion }})</h1>
        <p>Здравствуйте, товарищи пираты!</p>
        <p>Мы рады сообщить, что в нашей пирасткой бухте вышло обновление игры {{ $game->name }}.
            Приключение продолжается!</p>
        <p>Чтобы обновить ваше пиратское сокровище, перейдите по ссылке ниже:</p>
        <p><a href="{{ route('detail.index.uri', ['uri' => $game->uri]) }}" class="goto-button">*ТЫК*</a></p>
        <p>Спасибо за вашу поддержку!</p>
        <p>С уважением, автор {{ config('app.app_name') }}</p>
    </div>
@endif

@if ($randomLetter == 3)
    <div class="container">
        <h1>Обновление пиратской игры {{ $game->name }} ({{ $gameVersion }})</h1>
        <p>Ахои, пираты морей и океанов!</p>
        <p>С гордостью сообщаем вам о новой версии игры {{ $game->name }}.
            Это обновление приносит еще больше бури и приключений в вашей пиратской истории.</p>
        <p>Для подробностей, милости прошу в нашу гавань:</p>
        <p><a href="{{ route('detail.index.uri', ['uri' => $game->uri]) }}" class="goto-button">*ТЫК*</a></p>
        <p>С ветром в парусах и бутылкой рома в руках!</p>
        <p>С уважением, автор {{ config('app.app_name') }}</p>
    </div>
@endif

@if ($randomLetter == 4)
    <div class="container">
        <h1>Обновление игры {{ $game->name }} ({{ $gameVersion }})</h1>
        <p>Арррр, морские разбойники!</p>
        <p>У нас для вас важные новости. Игра {{ $game->name }} получила обновление до версии {{ $gameVersion }}.</p>
        <p>Перейдя по ссылке ниже, вы не только сможете обновить пиратское сокровище
            и продолжить свое пиратское путешествие во всей красе, но поддержать нашу славную пиратскую бухту:</p>
        <p><a href="{{ route('detail.index.uri', ['uri' => $game->uri]) }}" class="goto-button">*ТЫК*</a></p>
        <p>С уважением, автор {{ config('app.app_name') }}</p>
    </div>
@endif

@if ($randomLetter == 5)
    <div class="container">
        <h1>Обновление игры {{ $game->name }} ({{ $gameVersion }})</h1>
        <p>Здравствуйте, пираты 7 морей!</p>
        <p>Мы рады сообщить о выходе новой версии игры {{ $game->name }}. Обновление уже доступно на нашем ресурсе.</p>
        <p>Всего то нужно кликнуть вооот сюда:</p>
        <p><a href="{{ route('detail.index.uri', ['uri' => $game->uri]) }}" class="goto-button">*ТЫК*</a></p>
        <p>С уважением, автор {{ config('app.app_name') }}</p>
    </div>
@endif

<p>Если вы хотите отписаться от <a href="{{ route('unsubscribeFromEmailAboutUpdateGame', [
        'code' => base64_encode($email),
        'id' => $game->id
    ]) }}">рассылки обновлений по этой игре</a></p>

<p>Если вы хотите отписаться от <a href="{{ route('unsubscribeFromEmailAboutUpdateGames', [
        'code' => base64_encode($email)
    ]) }}">рассылок обновлений по всем играм</a></p>

<p>Если вы хотите отписаться от <a href="{{ route('unsubscribeFromAllNewsletter', [
        'code' => base64_encode($email)
    ]) }}">всех рассылок</a></p>
</body>
</html>
