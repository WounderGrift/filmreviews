@extends('layouts.main')
@section('content')

    <div class="blog">
        <div class="container">
            <h2 class="title">{{ $title }}</h2>

            <div class="error error_comment">
                <h3></h3>
            </div>

            <x-statisticmodule.chart-menu></x-statisticmodule.chart-menu>

            <div class="corner-box-6" style="height: 295px; width: 100%; background: rgb(74 85 104 / 70%); display: none;">
                <div class="corner-ribbon-6 corner-ribbon-6-top-left">
                    <span>404</span>
                </div>
                <p class="striped-text">!Data.length</p>
            </div>

            <div id="chartContainer" style="height: 300px; width: 100%;"></div>

            <nav style="display: flex; justify-content: flex-end; margin-top: 30px;">
                <button class="btn btn-info get-data-profiles-chart">7Д</button>
                <button class="btn btn-info get-data-profiles-chart">1МЕС</button>
                <button class="btn btn-info get-data-profiles-chart">1ГОД</button>
                <button class="btn btn-info get-data-profiles-chart">5ЛЕТ</button>
                <button class="btn btn-info get-data-profiles-chart">ВСЮ</button>
            </nav>

            @if (!isset($commentaries) || $commentaries->isEmpty() || !$commentaries->total() || $commentaries->currentPage() > $commentaries->lastPage())
                <div class="info-block">
                    <div class="info_title"><b>Комментариев не найдено</b></div>
                </div>
            @else
                <form id="filter" method="GET" style="margin-top: 30px;">
                    <input type="text" placeholder="Фильтр" style="margin-top: 15px; text-align: center; width: 100%; margin-bottom: 10px;">
                </form>
            @endif

            <div class="response">
                @foreach($commentaries as $comment)
                    <div class="media response-info">
                        <div class="media-left response-text-left" data-comment-id="{{ base64_encode($comment->id) }}">
                            <a href="{{ route('profile.index.cid', ['cid' => $comment->user->cid]) }}">
                                <img class="media-object" width="100" height="100"
                                     src="{{ $comment->user->avatar_path ? Storage::url($comment->user->avatar_path) : asset('images/440.png') }}"
                                     alt="{{ $profile->avatar_name ?? 'images/440.png' }}"/>
                            </a>
                            <h5>
                                <a href="{{ route('profile.index.cid', ['cid' => $comment->user->cid]) }}">
                                    {{ $comment->user->name }}
                                </a>

                            </h5>
                            @if ($comment->trashed())
                            <h5 class="is_deleted_comment">
                                Комментарий удален
                            </h5>
                            @endif
                        </div>
                        <div class="media-body response-text-right">
                            @php
                                $text = json_decode($comment->comment);
                            @endphp

                            @if (!empty($text->quote))
                                <blockquote>{{ $text->quote }}</blockquote>
                            @endif
                            <p>{{ $text->comment }}</p>
                            <ul>
                                @if (!$comment->game->trashed())
                                    <li>
                                        <a href="{{ route('detail.index.uri', ['uri' => $comment->game->uri]) }}#comment-textarea"
                                           target="_blank">Перейти к игре</a>
                                    </li>
                                @else
                                    <li>
                                        <a href="{{ route('detail.edit.index', ['uri' => $comment->game->uri]) }}#comment-textarea"
                                           target="_blank">Перейти к игре</a>
                                    </li>
                                @endif
                                <li>{{ \App\Http\Helpers\DateHelper::dateFormatterForComments($comment->created_at, config('app.timezone')) }}</li>
                                @if (Auth::check())
                                    <li>
                                        <label class="heart-detail like-action">
                                            <input type="checkbox" class="checkbox-detail"
                                                   data-game-id="{{ base64_encode($comment->game->id) }}"
                                                   data-comment-id="{{ base64_encode($comment->id) }}"
                                                {{ Auth::user()->likes->where('game_id', $comment->game->id)->where('comment_id', $comment->id)->isNotEmpty() ? 'checked' : '' }}>
                                            <span class="detail-heart-icon">
                                                <i class="far fa-star"></i>
                                            </span>
                                            <span class="detail-heart-icon-filled">
                                                <i class="fas fa-star"></i>
                                            </span>
                                        </label>
                                        <span class="like favorite-count">{{ count($comment->likes) }}</span>
                                    </li>

                                    @if (!$comment->trashed())
                                        @if (($comment->user->id === Auth::user()->id || Auth::user()->checkOwnerOrAdmin()))
                                            <li class="remove" data-comment-id="{{ base64_encode($comment->id) }}">Удалить</li>
                                        @endif
                                    @elseif ($comment->trashed() && Auth::user()->checkOwnerOrAdmin())
                                        <li class="reset" data-comment-id="{{ base64_encode($comment->id) }}">Восстановить</li>
                                    @endif

                                    @if (Auth::user()->checkOwnerOrAdmin())
                                        <li class="remove" data-comment-id="{{ base64_encode($comment->id) }}" data-hard="1">
                                            Удалить Жестко
                                        </li>
                                    @endif
                                @endif
                            </ul>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                @endforeach
            </div>

            @if(isset($commentaries))
                <div class="pagination">
                    {{ $commentaries->onEachSide(1)->links('pagination::bootstrap-4') }}
                </div>
            @endif
        </div>
    </div>

    <script src="https://cdn.canvasjs.com/jquery.canvasjs.min.js"></script>
    <script type="module" src="{{ asset('Modules/StatisticModule/resources/assets/js/activity-page.js') }}?version={{ config('app.version') }}"></script>
@endsection
