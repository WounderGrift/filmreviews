@extends('layouts.main')
@section('content')

    @if (!isset($game) || !isset($game->detail))
        <div class="blog">
            <h2 class="title">Ошибка адреса</h2>
            <div class="info-block">
                <div class="info_title"><b>Извините! Обнаружена ошибка</b></div>
                <div class="news_content">По данному адресу публикации на сайте не найдено</div>
            </div>
        </div>
    @else
    <div class="popup-subscribe popup disabled">
        <div class="popup-content subscribe disabled">
            <div class="loader"></div>
            <h2><u>Хочу знать об обновлении этой игры</u></h2>
            <h3 class="error error-subscribe-popup"></h3>
            <form id="subscribe-form" method="POST" onsubmit="return false;">
                <input id="subscribe-email" class="regist-login" type="email" name="subscribe" placeholder="Ваше Мыло">
                <button type="submit">ПОДПИСАТЬСЯ</button>
            </form>
        </div>
    </div>

    <div class="popup-send-error popup disabled">
        <div class="popup-content send-error disabled">
            <div class="loader"></div>
            <h2><u>Сообщить об ошибке</u></h2>
            <h3 class="error error-reporter"></h3>
            <form id="send-error-form" method="POST" onsubmit="return false;">
                <h5 id="counter_report">150 / 0</h5>
                <textarea id="send-error-text" class="regist-login" type="text" name="error-report" placeholder="Описание ошибки" autocomplete="off"></textarea>
                <button type="submit">ОТПРАВИТЬ</button>
            </form>
        </div>
    </div>

    <div class="blog">
        <div class="container" data-game-id="{{ base64_encode($game->id) }}">
            <h2 class="title">
                @if (Auth::check() && Auth::user()->checkOwnerOrAdmin())
                    @if ($game->status === $game::STATUS_UNPUBLISHED && !$game->trashed())
                        (Неопубликованная)
                    @endif
                    {{ $game->name }}
                    @if (!$game->trashed() && ($game->status === $game::STATUS_UNPUBLISHED && Auth::user()->checkAdmin() || Auth::user()->checkOwner()))
                        <a href="{{ route('detail.edit.index', ['uri' => $game->uri]) }}">
                            <i class="fas fa-pencil-alt pencil-ico" title="Редактировать"></i></a>
                    @endif
                @else
                    {{ $game->name }}
                @endif
            </h2>

            @if ($game->trashed())
                <div class="info-block">
                    <div class="info_title"><b>Игра удалена</b></div>
                </div>
            @endif

            <a href="https://boosty.to/greensteam_games_and_softt/donate" id="thankYouButton"
               class="btn btn-orange support hide-anim vertical-button" target="_blank">
                СПАСИБО
            </a>

            <div class="blog-left" >
                <div class="blog-info">
                    <div class="blog-info-text">
                        <div class="col-12 order-2">
                            <div class="poster-box" style="background: rgba(251, 251, 251, 0.9) url({{ isset($detail->preview_detail) && Storage::disk('public')->exists($detail->preview_detail) ? Storage::url($detail->preview_detail) : asset('images/730.png') }}) center center; background-size: cover;">
                                @if (isset($detail->preview_detail) && Storage::disk('public')->exists($detail->preview_detail))
                                    <div>
                                        <img class="poster-games"
                                             src="{{ Storage::url($detail->preview_detail) }}?timestamp={{ $detail->updated_at->timestamp }}"
                                             height="350" alt="{{ $game->name ?? 'preview' }}">
                                    </div>
                                @endif
                                <div id="media-normal" class="summary">
                                    <ul class="requirement-list">
                                        <li class="requirement-item">
                                            <span class="requirement-label">Категории:</span>
                                            <span class="requirement-value">{!! \App\Http\Helpers\DetailHelper::getCategoriesForView($detail) !!}</span>
                                        </li>
                                        <li class="requirement-item">
                                            <span class="requirement-label">Дата выхода:</span>
                                            <span class="requirement-value">{{ \App\Http\Helpers\DateHelper::dateFormatterForDetail($game->date_release, env('TIMEZONE')) }}</span>
                                        </li>

                                        @if (isset($game->series) && ($showSeries || ( Auth::check() && Auth::user()->checkOwnerOrAdmin())))
                                        <li class="requirement-item">
                                            <span class="requirement-label">Серия:</span>
                                            <a class="requirement-value series-link" href="{{ route('series.indexSeries', ['uri' => $game->series->uri]) }}">{{ $game->series->name }}</a>
                                        </li>
                                        @endif

                                        @foreach ($info->summary as $key => $value)
                                            <li class="requirement-item">
                                                <span class="requirement-label">{{ $key }}</span>
                                                <span class="requirement-value">{!! \App\Http\Helpers\DetailHelper::getSeparatedSummary($value) !!}</span>
                                            </li>
                                        @endforeach
                                        @if (isset($isRecommended) && !empty($recommended))
                                            <a class="recommended btn btn-info" href="{{ route('recommended.index', ['ids' => $recommended]) }}">
                                                {{ $buttonTitle }}
                                            </a>
                                        @elseif (isset($isRecommended))
                                            <a class="recommended btn btn-info" href="{{ route('main.index') }}">Вернутся к рекомендациям</a>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        </div>

                        @php
                            $detailBanner = \App\Http\Helpers\BannerHelper::getDetailBannerMenu(true);
                        @endphp

                        @if ($detailBanner->isNotEmpty())
                            <h4> {{ $detailBanner->count() > 1 ? 'Спонсоры' : 'Спонсор'  }}</h4>
                            <x-bannersmodule.banner-detail :banners="$detailBanner"></x-bannersmodule.banner-detail>
                        @endif

                        @if (isset($info->description))
                            <h4>Описание игры</h4>
                            {!! $info->description !!}
                        @endif

                        @php
                            $minSysCount = isset($info?->system?->min) ? count((array) $info?->system?->min) : 0;
                            $maxSysCount = isset($info?->system?->max) ? count((array) $info?->system?->max) : 0;
                        @endphp

                        @if ($minSysCount > 0 || $maxSysCount > 0)
                        <h4>Системные требования</h4>
                        @endif
                        <div class="requirements-container">
                            @if ($minSysCount > 0)
                            <div class="system-requirements min-requirements">
                                <h2 class="section-title">Минимальные</h2>
                                <ul class="requirement-list">
                                    @foreach($info->system->min as $key => $value)
                                        <li class="requirement-item">
                                            <span class="requirement-label">{{ $key }}</span>
                                            <span class="requirement-value">{!! $value !!}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif
                            @if ($maxSysCount > 0)
                            <div class="system-requirements recommended-requirements">
                                <h2 class="section-title">Рекомендуемые</h2>
                                <ul class="requirement-list">
                                    @foreach($info->system->max as $key => $value)
                                        <li class="requirement-item">
                                            <span class="requirement-label">{{ $key }}</span>
                                            <span class="requirement-value">{!! $value !!}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif
                        </div>

                        @if (Storage::disk('public')->exists($detail->preview_trailer) && isset($detail->trailer_detail))
                        <h4>Видео об игре</h4>
                        <div class="black-ground-container">
                            <div class="black-ground" id="videoContainer" data-trailer="{{ $detail->trailer_detail }}">
                                <img src="{{ isset($detail->preview_trailer) && Storage::disk('public')->exists($detail->preview_trailer) ? Storage::url($detail->preview_trailer) : asset('images/730.png') }}" alt="{{ $game->name ?? 'preview' }}">
                                <div class="overlay" id="playButton">
                                    <p>Кликните, чтобы начать видео</p>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if ($detail?->screenshots->isNotEmpty())
                        <h4 style="margin-top: 2em">Скриншоты из игры</h4>
                        <div class="gallery">
                            @foreach($detail?->screenshots as $screenshot)
                                <div class="photo-container">
                                    <a href="{{ isset($screenshot->path) && Storage::disk('public')->exists($screenshot->path) ? Storage::url($screenshot->path) : asset('images/350.png')}}"
                                       data-fancybox="gallery" class="photo">
                                        <img src="{{ isset($screenshot->path) && Storage::disk('public')->exists($screenshot->path) ? Storage::url($screenshot->path) : asset('images/350.png') }}" alt="{{ $game->name ?? 'preview' }}">
                                    </a>
                                </div>
                            @endforeach
                        </div>
                        @endif

                        @php
                            $basementBanner = \App\Http\Helpers\BannerHelper::getBasementBanners(!isset($inBasementBannerPage));
                        @endphp

                        @if ($basementBanner->isNotEmpty())
                            <h4> {{ $basementBanner->count() > 1 ? 'Спонсоры' : 'Спонсор'  }}</h4>
                            <x-bannersmodule.banner-detail :banners="$basementBanner"></x-bannersmodule.banner-detail>
                        @endif

                        @if (!$game->is_waiting)
                        @foreach($detail?->torrents->sortBy('version') as $torrent)
                            @if (!$game->is_sponsor && !$torrent->is_link && !Storage::disk('public')->exists($torrent->path))
                                @continue
                            @endif

                            @if (($game->is_sponsor && $torrent->is_link) || (!$game->is_sponsor && !$torrent->is_link))
                            <div class="download-container">
                                <div class="error error_download">
                                    <h3></h3>
                                </div>

                                <div class="spoiler">
                                    <div class="spoiler-header" style="{{ $torrent?->additional_info ? 'cursor: pointer;' : 'cursor: auto;' }}">
                                        <div style="display: flex; align-items: flex-start;">
                                            <h2 class="download-title">
                                                {{ $torrent?->repacks?->label ? "Репак от {$torrent->repacks->label} " : ""}}
                                                {{ $torrent?->repacks?->label && $torrent?->size ? " | " : " " }}
                                                {{ $torrent?->size ? "Размер: {$torrent->size}" : "" }}
                                                {{ $torrent?->size && $torrent?->version && $torrent?->version != "v0.0" ? " | " : " " }}
                                                {{ $torrent?->version && $torrent?->version != "v0.0" ? "{$torrent->version}" : "" }}
                                            </h2>

{{--                                            @if (!$game->is_sponsor && pathinfo($torrent->name, PATHINFO_EXTENSION) == 'torrent')--}}
{{--                                                @php--}}
{{--                                                    $seedAndPeer = \App\Http\Helpers\TorrentHelper::getSeedPeer($torrent->path);--}}
{{--                                                @endphp--}}

{{--                                                <h2 class="download-title">--}}
{{--                                                   {{ $seedAndPeer['seeds'] }} {{ $seedAndPeer['peers'] }}--}}
{{--                                                </h2>--}}
{{--                                            @endif--}}

                                            @if ($torrent?->additional_info)
                                                <span class="toggle-icon">▲</span>
                                            @endif
                                        </div>

                                        <div style="display: flex; align-items: center;">
                                            @if ($torrent->is_link)
                                                <a data-code="{{ base64_encode($torrent->id) }}" class="btn btn-success download" style="background-image: url({{ asset('images/download-button-link-bg.png') }});">
                                                    Перейти на сайт
                                                </a>
                                            @elseif (pathinfo($torrent->name, PATHINFO_EXTENSION) == 'torrent')
                                                <a data-code="{{ base64_encode($torrent->id) }}" class="btn btn-success download" style="background-image: url({{ asset('images/download-button-tor-bg.png') }});">
                                                    Скачать .{{ pathinfo($torrent->name, PATHINFO_EXTENSION) }}
                                                </a>
                                            @elseif (pathinfo($torrent->name, PATHINFO_EXTENSION) == 'rar' || pathinfo($torrent->name, PATHINFO_EXTENSION) == 'zip')
                                                <a data-code="{{ base64_encode($torrent->id) }}" class="btn btn-success download-rar" style="background-image: url({{ asset('images/download-button-rar-bg.png') }});">
                                                    Скачать .{{ pathinfo($torrent->name, PATHINFO_EXTENSION) }}
                                                </a>
                                            @endif

                                            <div class="download-count">
                                                <i class="fa fa-download" style="margin-right: 5px; margin-top: 10px;"></i>
                                                <span>{{ $torrent->downloadStatistic()->count() }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="spoiler-content" style="text-align: center;">
                                        @if ($torrent?->additional_info)
                                            {!! $torrent?->additional_info !!}
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endif
                        @endforeach
                        @else
                            <div class="info-block">
                                <div class="info_title"><b>Эта игра еще не вышла</b></div>
                                <div class="news_content">Подпишитесь на обновления, чтобы не пропустить.</div>
                            </div>
                        @endif

                        <div class="outline">
                            <div class="text-center">
                                <a href="{{ route('faq') }}" target="_blank">FAQ по проблемам с установкой и запуском репаков</a>
                            </div>
                        </div>
                    </div>

                    <div class="error error_subscribe">
                        <h3></h3>
                    </div>

                    <div class="comment-icons" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap;">
                        <ul>
                            <li><i class="far fa-calendar"></i>{{ \App\Http\Helpers\DateHelper::dateFormatterForDetail($game->created_at, config('app.timezone')) }}</li>
                            <li><i class="far fa-comment"></i>{{ count($comments) }}</li>

                            @if (Auth::check())
                                @if (Auth::user()->is_verify)
                                <li>
                                    <label class="heart-detail wishlist-action">
                                        <input type="checkbox" class="checkbox-detail"
                                            {{ Auth::user()->wishlist->contains('id', $game->id) ? 'checked' : '' }}>
                                        <span class="detail-heart-icon">
                                        <i class="far fa-heart"></i>
                                        </span>
                                        <span class="detail-heart-icon-filled">
                                            <i class="fas fa-heart"></i>
                                        </span>
                                    </label>
                                    <span class="wishlist favorite-count">{{ count($game->wishlist) }}</span>
                                </li>
                                @else
                                    <li><i class="far fa-heart"></i>{{ count($game->wishlist) }}</li>
                                @endif
                                <li>
                                    <label class="heart-detail like-action game">
                                        <input type="checkbox" class="checkbox-detail"
                                             {{ Auth::user()->likes->where('game_id', $game->id)->whereNull('comment_id')->isNotEmpty() ? 'checked' : '' }}>
                                        <span class="detail-heart-icon">
                                            <i class="far fa-star"></i>
                                        </span>
                                        <span class="detail-heart-icon-filled">
                                            <i class="fas fa-star"></i>
                                        </span>
                                    </label>
                                    <span class="like favorite-count">{{ count($game->likes) }}</span>
                                </li>
                            @else
                                <li>
                                    <label class="heart-detail button-enter">
                                        <input type="checkbox" class="checkbox-detail">
                                        <span class="detail-heart-icon-stub">
                                            <i class="far fa-heart"></i>
                                        </span>
                                    </label>
                                    <span class="wishlist favorite-count">{{ count($game->wishlist) }}</span>
                                </li>
                                <li>
                                    <label class="heart-detail button-enter">
                                        <input type="checkbox" class="checkbox-detail">
                                        <span class="detail-heart-icon-stub">
                                            <i class="far fa-star"></i>
                                        </span>
                                    </label>
                                    <span class="like favorite-count">{{ count($game->likes) }}</span>
                                </li>
                            @endif

                            @if (Auth::check())
                                @if (!Auth::user()->newsletters->contains($game->id))
                                    <li class="user-subscribe btn btn-orange" style="color: #fff">
                                        Подписаться на обновления
                                    </li>
                                @else
                                    <li class="user-unsubscribe btn btn-orange" style="color: #fff">
                                        Отписаться от новостей
                                    </li>
                                @endif
                            @else
                                <li class="button-subscribe btn btn-orange" style="color: #fff">
                                    Подписаться на обновления
                                </li>
                            @endif
                            <li class="newsletter_count">{{ $game->newsletters->count() }}</li>
                        </ul>
                        <button id="report-error" class="btn btn-danger" style="margin-top: 6px;">
                            Сообщить об ошибке
                        </button>
                    </div>

                    <div class="comment-section">
                        <h4>{{ !$comments->isEmpty() ? 'Комментарии' : '' }}</h4>
                        @foreach($comments as $comment)
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
                                        <li>{{ \App\Http\Helpers\DateHelper::dateFormatterForComments($comment->created_at, config('app.timezone')) }}</li>
                                        @if (Auth::check())
                                            <li>
                                                <label class="heart-detail like-action comment">
                                                    <input type="checkbox" class="checkbox-detail"
                                                        data-comment-id="{{ base64_encode($comment->id) }}"
                                                        {{ Auth::user()->likes->where('game_id', $game->id)->where('comment_id', $comment->id)->isNotEmpty() ? 'checked' : '' }}>
                                                    <span class="detail-heart-icon">
                                                        <i class="far fa-star"></i>
                                                    </span>
                                                    <span class="detail-heart-icon-filled">
                                                        <i class="fas fa-star"></i>
                                                    </span>
                                                </label>
                                                <span class="like favorite-count">{{ count($comment->likes) }}</span>
                                            </li>

                                            <li class="reply" data-comment-id="{{ base64_encode($comment->id) }}">Ответить</li>
                                            @if ($comment->user->id === Auth::check() && (Auth::user()->id || Auth::user()->checkOwnerOrAdmin()))
                                                <li class="remove" data-comment-id="{{ base64_encode($comment->id) }}" data-hard="false">Удалить</li>
                                            @endif
                                            @if (Auth::check() && Auth::user()->checkOwnerOrAdmin())
                                                <li class="remove" data-comment-id="{{ base64_encode($comment->id) }}" data-hard="true">Удалить Жестко</li>
                                            @endif
                                        @endif
                                    </ul>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                        @endforeach
                    </div>

                    @if(!$comments->isEmpty())
                        <div class="pagination">
                            {{ $comments->onEachSide(1)->links('pagination::bootstrap-4') }}
                        </div>
                    @endif

                </div>
                @if (Auth::check() && Auth::user()->is_verify && !Auth::user()->is_banned)
                    <div class="error error_comment">
                        <h3></h3>
                    </div>

                    <div class="comment-form" data-game-uri="{{ $game->uri }}">
                        <h4>{{ !$comments->isEmpty() ? 'Оставьте комментарий' : 'Комментариев нет, будьте первыми' }}</h4>
                        <div class="reply-label">
                            <blockquote id="comment-reply" style="display: none"></blockquote>
                            <button id="cancel-reply" style="display: none"><i class="fas fa-times"></i></button>
                        </div>


                        <form id="response-form" method="POST">
                            <h5 id="counter">150 / 0</h5>
                            <textarea id="comment-textarea" type="text" name="comment" placeholder="Ваш комментарий..." required></textarea>
                            <input type="submit" value="Отправить">
                        </form>
                    </div>
                @else
                    <div class="info-block">
                        <div class="info_title"><b>Только верифицированные пользователи</b></div>
                        <div class="news_content">Могут оставлять комментарии.</div>
                    </div>
                @endif
            </div>
            <div class="clearfix"> </div>
        </div>
    </div>
    @endif

    <script type="module" src="{{ asset('Modules/DetailModule/resources/assets/js/detail.js') }}?version={{ config('app.version') }}"></script>
    @if(File::exists(base_path('Modules/MainModule')))
    <script type="module" src="{{ asset('Modules/MainModule/resources/assets/js/wishlistOnGrid.js') }}?version={{ config('app.version') }}"></script>
    @endif
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.js"></script>
@endsection
