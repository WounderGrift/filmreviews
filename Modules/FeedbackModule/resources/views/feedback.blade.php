@extends('layouts.main')
@section('content')

    <div class="about">
        <div class="container">
            <h2>Обратная связь</h2>

            <p>
                Владельцы и создатели данного сайта не несут ответственность за использование ссылок,
                представленных на этом сайте.
                <br>
                На данном сайте представлены исключительно ссылки на другие ресурсы.
                <br>
                Данный проект является некоммерческим, поэтому авторы не несут никакой материальной ответственности.
                <br>
                Все торрент файлы, размещенные на сайте,
                принадлежат другим торрент-трекерам и являются свободно найденными и взятыми из сети Интернет.
                <br><br>
                Если вы являетесь правообладателем продукции и не хотите чтобы на нашем сайте находились ссылки на
                эту продукцию - напишите нам через форму обратной связи, мы все уладим в кратчайшие сроки!
            </p>

            <div class="error error_feedback">
                <h3></h3>
            </div>

            <div class="comment-form" style="margin-top: 10px;">
                @if (!Auth::check())
                <div class="input-container profile-center" style="width: 100%">
                    <input id="email" type="text" name="email" placeholder="Ваше мыло">
                </div>
                @endif

                <h5>300 / 0</h5>
                <textarea id="letter-textarea" type="text" placeholder="Ваше сообщение..."></textarea>
                <button id="send" class="btn btn-orange">
                    Отправить
                </button>
            </div>

        </div>
    </div>

    <script type="module" src="{{ asset('modules/feedbackmodule/resources/assets/js/feedback.js') }}?version={{config('app.version')}}"></script>
@endsection
