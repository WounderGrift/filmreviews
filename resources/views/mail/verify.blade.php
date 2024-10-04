<!DOCTYPE html>
<html lang="ru-RU">
<head>
    <title>Верификация</title>
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
            color: #ffa500;
            text-align: center;
        }

        .reset-button {
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
    <div class="container">
        <h1>Верификация</h1>
        <p>Здравствуйте {{ $name }},</p>
        <p>Cпасибо за регистрацию на моем торрент-сайте {{ config('app.app_name') }}.</p>
        <p>Для активации вашего профиля, пожалуйста,
            подтвердите свой адрес электронной почты. Ведь после этого вам откроются дополнительные функции сайта.</p>
        <p><a href="{{ route('profile.verify', ['token' => $token]) }}" class="reset-button"> *ТЫК* </a></p>
        <p>С наилучшими пожеланиями, автор {{ config('app.app_name') }}</p>
    </div>
</body>
</html>
