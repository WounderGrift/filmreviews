@extends('main::layouts.main')
@section('content')
    @if ($showWarning)
        <div class="review">
            <div class="container">
                <h2 class="title">Добро пожаловать на мой сайт</h2>
            </div>
        </div>

        <div class="info-block" style="display: none">
            <div class="info_title"><b>Извините, но сейчас нет публикаций на сайте</b></div>
            <div class="news_content">По этому и рекомендаций нету</div>
        </div>
    @endif

    @if ($popularAndRecommended->isNotEmpty() && $popularAndRecommended->count() > 4)
    <div class="content">
        <div class="container">
            <div class="top-games">
                <h3>Популярное и рекомендуемое</h3>
            </div>

            <div class="popular-and-recommended skeleton" style="margin-top: 1.5em;">
                <x-skeleton-loader style="width: 100%; height: 310px"></x-skeleton-loader>
            </div>

            <div class="top-game-grids popular-and-recommended" style="display: none;">
                <ul id="flexiselDemo1">
                    @foreach ($popularAndRecommended as $index => $game)
                        <li>
                            <a href="{{ route('detail.index.uri', ['uri' => $game->uri]) }}">
                                <div class="game-grid">
                                    <h4 style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $game->name }}</h4>
                                    <p>{!! $game->torrents->max('version') && $game->torrents->max('version') != 'v0.0' ? $game->torrents->max('version') : "<br>" !!}</p>
                                    <img src="{{ Storage::disk('public')->exists($game->preview_grid) ? Storage::url($game->preview_grid) : asset('images/440.png')}}?timestamp={{ $game->updated_at->timestamp }}"
                                         class="img-responsive" alt="{{ $game->name }}"/>
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif

    @if (!empty($recommended))
    <div class="skeleton">
        <x-skeleton-loader style="width: 100%; height: 310px"></x-skeleton-loader>
    </div>

    <div class="poster" style="background: url({{ asset('images/pst1.jpg') }}) no-repeat 0 0; background-size: cover; min-height: 20em; display: none;">
        <div class="container" style="display: flex; justify-content: center;">
            <div class="poster-info" style="margin-top: 3em; margin-bottom: 3em;">
                <h3>Рекомендованное вам</h3>
                <p>Откройте для себя новые впечатления с нашими рекомендациями.
                    Мы подобрали несколько уникальных предложений специально для вас,
                    чтобы сделать ваш опыт еще более захватывающим с {{ config('app.app_name') }}.
                    <br>
                    Приглашаем вас познакомиться с нашими рекомендациями и насладиться неповторимым опытом!</p>
                @if (!Auth::check())
                <p>Откройте для себя персональные рекомендации!
                    Авторизуйтесь, чтобы мы могли подстроить рекомендации ваши интересы.</p>
                @endif
                <a class="hvr-bounce-to-bottom" href="{{ route('recommended.index', ['ids' => $recommended]) }}">Посмотреть</a>
            </div>
        </div>
    </div>
    @endif

    @if ($lastPublication->isNotEmpty() && $lastPublication->count() > 3)
    <div class="latest">
        <div class="container">
            <div class="latest-games">
                <h3>Последние релизы</h3>
                <span></span>
            </div>

            <div class="latest-top skeleton">
                <div class="col-md-5 trailer-text">
                    @for ($key = 0; $key < 4; $key++)
                        <div class="sub-trailer">
                            <div class="col-md-4">
                                <x-skeleton-loader style="width: 100px; height: 55px"></x-skeleton-loader>
                            </div>
                            <div class="col-md-12 sub-text">
                                <x-skeleton-loader style="width: 100%; height: 55px"></x-skeleton-loader>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    @endfor
                </div>
                <div class="col-md-7 trailer">
                    <div class="black-ground" id="videoContainer">
                        <x-skeleton-loader style="width: 100%"></x-skeleton-loader>
                    </div>
                </div>
            </div>

            <div class="latest-top">
                <div class="last-release" style="display: none;">
                    <div class="col-md-5 trailer-text">
                        @foreach ($lastPublication as $index => $game)
                            @if (isset($game->detail->preview_detail))
                            <div class="sub-trailer">
                                <div class="col-md-4 sub-img">
                                    <img src="{{ Storage::disk('public')->exists($game->detail->preview_detail) ? Storage::url($game->detail->preview_detail) : asset('images/730.png') }}?timestamp={{ $game->updated_at->timestamp }}"
                                         alt="{{ $game->name }}"/>
                                </div>
                                <div class="col-md-8 sub-text">
                                    <a href="{{ route('detail.index.uri', ['uri' => $game->uri]) }}">{{ $game->name }}</a>
                                    <p style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        {!! strip_tags(json_decode($game->detail->info)->description) !!}
                                    </p>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                            @endif
                        @endforeach
                    </div>
                    @php
                        $showBreakPicture = true;
                    @endphp

                    @foreach ($lastPublication as $index => $game)
                        @if (!isset($game->detail->preview_trailer) || !Storage::disk('public')->exists($game->detail->preview_trailer))
                            @continue;
                        @endif

                        <div class="col-md-7 trailer">
                            <div class="black-ground" id="videoContainer" data-trailer="{{ $game->detail->trailer_detail }}">
                                <img src="{{ Storage::url($game->detail->preview_trailer) }}" alt="{{ $game->name ?? 'preview' }}">
                                <div class="overlay" id="playButton">
                                    <p>Кликните, чтобы начать видео</p>
                                </div>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        @php
                            $showBreakPicture = false;
                            break;
                        @endphp
                    @endforeach

                    @if ($showBreakPicture)
                        <div class="col-md-7 trailer">
                            <div class="black-ground" id="videoContainer">
                                <img src="{{ asset('images/730.png') }}" alt="{{ 'preview' }}">
                            </div>
                        </div>
                        <div class="clearfix"></div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    @if ($lastUpdate->isNotEmpty() && $lastUpdate->count() > 4)
    <div class="content">
        <div class="container">
            <div class="top-games">
                <h3>Последние обновления</h3>
            </div>

            <div class="popular-and-recommended skeleton" style="margin-top: 1.5em;">
                <x-skeleton-loader style="width: 100%; height: 310px"></x-skeleton-loader>
            </div>

            <div class="top-game-grids last-update" style="display: none;">
                <ul id="flexiselDemo2">
                    @foreach ($lastUpdate as $index => $game)
                        @if (isset($game->detail->preview_detail))
                        <li>
                            <a href="{{ route('detail.index.uri', ['uri' => $game->uri]) }}">
                                <div class="game-grid">
                                    <h4 style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $game->name }}</h4>
                                    <p>{!! $game->torrents->max('version') && $game->torrents->max('version') != 'v0.0' ? $game->torrents->max('version') : "<br>" !!}</p>
                                    <img src="{{ Storage::disk('public')->exists($game->detail->preview_detail) ? Storage::url($game->detail->preview_detail) : asset('images/730.png') }}?timestamp={{ $game->updated_at->timestamp }}"
                                         class="img-responsive" alt="{{ $game->name }}"/>
                                </div>
                            </a>
                        </li>
                        @endif
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif

    @if (false)
    <div class="poster" style="background: url({{ asset('images/pst1.jpg') }}) no-repeat 0 0; background-size: cover;">
        <div class="container">
            <div class="poster-info">
                <h3>Nunc cursus dui in metus efficitur, sit amet ullamcorper dolor viverra.</h3>
                <p>Proin ornare metus eros, quis mattis lorem venenatis eget. Curabitur eget dui euismod,
                    varius nisl eu, pharetra lacus. Sed vehicula tempor leo. Aenean dictum suscipit magna vel
                    tempus. Aliquam nec dui dolor. Quisque scelerisque aliquet est et dignissim. Morbi magna quam, molestie sed fermentum et, elementum at dolor</p>
                <a class="hvr-bounce-to-bottom" href="reviews.html">Read More</a>
            </div>
        </div>
    </div>
    @endif

    @if (false)
    <div class="x-box">
        <div class="container">
            <div class="x-box_sec">
                <div class="col-md-7 x-box-left">
                    <h2>XBOX 360</h2>
                    <h3>Suspendisse ornare nisl et tellus convallis, non vehicula nibh convallis.</h3>
                    <p>Proin ornare metus eros, quis mattis lorem venenatis eget. Curabitur eget dui
                        euismod, varius nisl eu, pharetra lacus. Sed vehicula tempor leo. Aenean dictum suscipit magna vel tempus.
                        Aliquam nec dui dolor. Quisque scelerisque aliquet est et dignissim.</p>
                    <a class="hvr-bounce-to-top" href="reviews.html">Read More</a>
                </div>
                <div class="col-md-5 x-box-right">
                    <img src="{{ asset('images/xbox.jpg') }}" class="img-responsive" alt=""/>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
    </div>
    @endif

    @if (isset($jsFile))
        <script src="{{ asset($jsFile) }}"></script>
    @endif
    <script type="module" src="{{ asset('Modules/MainModule/resources/assets/js/recommended.js') }}?version={{ config('app.version') }}"></script>
    <script type="text/javascript" src="{{ asset('js/jquery.flexisel.js') }}"></script>
@endsection
