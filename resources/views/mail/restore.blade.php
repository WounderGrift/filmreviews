<!DOCTYPE html>
<html lang="ru-RU">
<head>
    <title>Восстановление пароля</title>
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
        <h1>Восстановление пароля</h1>
        <p>Здравствуйте {{ $name }},</p>
        <p>Вы получаете это письмо, потому что запросили восстановление пароля для вашей учетной записи на нашем
            торрент-сайте {{ config('app.app_name') }}.
            Не волнуйтесь, мы готовы помочь вам восстановить доступ к вашему аккаунту.</p>
        <p>Используйте единоразовый токен для входа в ваш профиль:</p>
        <p><a class="reset-button">{{ $generatedToken }}</a></p>
        <p>С уважением, автор {{ config('app.app_name') }}</p>
    </div>
</body>
</html>
