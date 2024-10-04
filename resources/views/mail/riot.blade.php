<!DOCTYPE html>
<html lang="ru-RU">
<head>
    <title> {{ $bot }} бунтует</title>
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
    </style>
</head>
<body>
<div class="container">
    <h1>ВНИМАНИЕ</h1>
    <p>Телеграм бот - {{ $bot }} торрент-сайта {{ config('app.app_name') }}, взбунтовался.</p>
    <p>Надо что-то с этим сделать.</p>
    <p>{{ $error }}</p>
</div>
</body>
</html>
