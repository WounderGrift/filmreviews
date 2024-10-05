@extends('layouts.main')
@section('content')
    <div class="blog">

        @if (!isset($game))
            <h2 class="title">{{ $title }}</h2>

            <div class="info-block">
                <div class="info_title"><b>Кажется тут пусто</b></div>
                <div class="news_content">Игра по этому адресу уже опубликована.</div>
            </div>
        @else
            <div class="container">
                <h2 class="title">{{ $title }}</h2>
                <input type="checkbox" id="nav-toggle" hidden>

                <div class="error error_public">
                    <h3></h3>
                </div>

                <div class="blog-left">
                    <div class="blog-info">
                        <div class="blog-info-text">
                            <div class="col-12 order-2">
                                <div class="poster-box"
                                     style="background: rgba(251, 251, 251, 0.9) url({{ isset($game?->detail?->preview_detail) && Storage::disk('public')->exists($game?->detail?->preview_detail) ? Storage::url($game?->detail?->preview_detail) : asset('images/730.png') }}) center center; background-size: cover;">
                                    @if (isset($game?->detail?->preview_detail) && Storage::disk('public')->exists($game?->detail?->preview_detail))
                                        <div>
                                            <img class="poster-games"
                                                 src="{{ Storage::url($game?->detail?->preview_detail) }}?timestamp={{ $game?->detail?->updated_at->timestamp }}"
                                                 height="350" alt="{{ $game?->name ?? 'preview' }}">
                                        </div>
                                    @endif
                                    <div id="media-normal" class="summary">
                                        <div id="buttons-publish"
                                             style="display: flex; justify-content: space-between; align-items: center; flex-direction: column;">
                                            <label class="radio-container update-radio" for="is_publish">
                                                Отправить письма о публикации
                                                <input type="radio" id="is_publish" name="email_type"
                                                       value="publish">
                                                <span class="checkmark"></span>
                                            </label>
                                            <label class="radio-container update-radio" for="is_update"
                                                   style="margin-top: 10px; margin-bottom: 10px;">
                                                Отправить письма об обновлении
                                                <input type="radio" id="is_update" name="email_type" value="update">
                                                <span class="checkmark"></span>
                                            </label>
                                            <label class="radio-container update-radio" for="is_nothing"
                                                   style="margin-top: 10px; margin-bottom: 10px;">
                                                Не отправлять письма
                                                <input type="radio" id="is_nothing" name="email_type"
                                                       value="nothing">
                                                <span class="checkmark"></span>
                                            </label>
                                            <label>__________</label>
                                            <label class="radio-container update-radio" for="send_message_publish"
                                                   style="margin-top: 10px; margin-bottom: 10px;">
                                                Отправить весть в канал о публикации
                                                <input type="radio" id="send_message_publish" name="message_type"
                                                       value="publish">
                                                <span class="checkmark"></span>
                                            </label>
                                            <label class="radio-container update-radio" for="send_message_update"
                                                   style="margin-top: 10px; margin-bottom: 10px;">
                                                Отправить весть в канал об обновлении
                                                <input type="radio" id="send_message_update" name="message_type"
                                                       value="update">
                                                <span class="checkmark"></span>
                                            </label>
                                            <label class="radio-container update-radio" for="send_message_nothing"
                                                   style="margin-top: 10px; margin-bottom: 10px;">
                                                Ничего не отправлять в канал
                                                <input type="radio" id="send_message_nothing" name="message_type"
                                                       value="nothing">
                                                <span class="checkmark"></span>
                                            </label>
                                            <label>__________</label>
                                            <a href="{{ route('detail.index.uri', ['uri' => $game->uri]) }}"
                                               target="_blank"
                                               class="btn btn-orange" style="width: 18em;">
                                                Просмотр черновика
                                            </a>

                                            <a href="{{ route('detail.edit.index', ['uri' => $game->uri]) }}"
                                               class="btn btn-orange" style="width: 18em; margin-top: 10px;">
                                                Редактировать
                                            </a>

                                            <button id="publish-game" data-game-id="{{ $game->id }}"
                                                    class="btn btn-orange"
                                                    style="width: 18em; margin-top: 10px;">
                                                Опубликовать игру
                                            </button>

                                            <button id="delete-game" data-game-id="{{ $game->id }}"
                                                    class="btn btn-danger"
                                                    style="width: 18em; margin-top: 100px;">
                                                Удалить игру
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <script type="module" src="{{ asset('Modules/PublicationModule/resources/assets/js/publish.js') }}?version={{config('app.version')}}"></script>
@endsection
