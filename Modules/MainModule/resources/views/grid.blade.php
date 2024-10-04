@extends('main::layouts.main')
@section('content')
    <div class="review">
        <div class="container">
            <h2 class="title">{{ $title }}</h2>
            <input type="checkbox" id="nav-toggle" hidden>

            @if (!empty($showWarning))
                <div class="info-block" style="display: none">
                    <div class="info_title"><b>Извините, но сейчас нет публикаций на сайте</b></div>
                    <div class="news_content">Но скоро появятся</div>
                </div>
            @endif

            @if (!empty($expirationWarning))
                <div class="info-block" style="display: none">
                    <div class="info_title"><b>Игр, которые уже должны были выйти, не найдено</b></div>
                </div>
            @endif

            <div class="games-skeleton-list">
                @for ($key = 0; $key < 8; $key++)
                    @if ($key % 4 == 0)
                        <div class="row align-items-start">
                    @endif

                    <div class="col-md-4 sed-md">
                        <x-skeleton-loader style="width: 275px; height: 310px"></x-skeleton-loader>
                    </div>

                    @if (($key + 1) % 4 == 0 || $key == 7)
                        <div class="clearfix"></div>
                        </div>
                    @endif
                @endfor
            </div>

            <div class="games-list" style="display: none;">
            @if (isset($games) && ($games->isNotEmpty() || $games->total() || $games->currentPage() < $games->lastPage()))
                @foreach ($games as $key => $game)
                    @if ($key % 4 == 0)
                        <div class="row align-items-start">
                    @endif

                    <div class="col-md-4 sed-md">
                        <div class="col-1" style="border-radius: 10px;">
                            <a href="{{ File::exists(base_path('Modules/DetailModule')) ? route('detail.index.uri', ['uri' => $game->uri]) : '#' }}">
                                <img class="img-responsive"
                                     src="{{ Storage::disk('public')->exists($game->preview_grid) ? Storage::url($game->preview_grid) : asset('images/440.png') }}?timestamp={{ $game->updated_at->timestamp }}"
                                     alt="{{ $game->name }}">
                                @if ($game->is_sponsor)
                                    <span class="vers hit"><i class="fas fa-sync-alt"></i>СПОНСОР</span>
                                @elseif ($game->is_waiting)
                                    <span class="vers hit"><i class="fas fa-hourglass"></i>ЕЩЕ НЕ ВЫШЛА</span>
                                @elseif ($game->torrents->isNotEmpty() && $game->torrents->max('version') != 'v0.0')
                                    <span class="vers"><i class="fas fa-sync-alt"></i>{{ $game->torrents->max('version') }}</span>
                                @endif
                            </a>
                            <a href="{{ File::exists(base_path('Modules/DetailModule')) ? route('detail.index.uri', ['uri' => $game->uri]) : '#' }}" class="game-name">
                                <h4>{{ $game->name }}</h4>
                            </a>
                            @if (Auth::check())
                                @if (Auth::user()->is_verify)
                                    <label class="heart-checkbox wishlist-action">
                                        <input type="checkbox" class="wishlist-checkbox"
                                               data-game-id="{{ base64_encode($game->id) }}" {{ Auth::user()->wishlist->contains('id', $game->id) ? 'checked' : '' }}>
                                        <span class="heart-icon">
                                            <i class="far fa-heart"></i>
                                        </span>
                                        <span class="heart-icon-filled">
                                            <i class="fas fa-heart"></i>
                                        </span>
                                    </label>
                                @endif
                            @else
                                <label class="heart-checkbox button-enter">
                                    <input type="checkbox" class="wishlist-checkbox">
                                    <span class="heart-icon-stub">
                                        <i class="far fa-heart"></i>
                                    </span>
                                </label>
                            @endif
                        </div>
                    </div>

                    @if (($key + 1) % 4 == 0 || $loop->last)
                        <div class="clearfix"></div>
                        </div>
                    @endif
                @endforeach
            @endif
            </div>

            @if (isset($categories) && !$categories->isEmpty())
                <nav class="nav">
                    <label for="nav-toggle" class="nav-toggle" onclick></label>

                    <h2 class="logo">
                        <a>КАТЕГОРИИ</a>
                    </h2>
                    <ul>
                        <li>
                            <form class="search-mobile" action="{{ route('search.index') }}" method="GET">
                                <button type="submit"><i class="fa fa-search"></i></button>
                                <input type="text" name="query" autocomplete="off" placeholder="Поиск по сайту...">
                            </form>
                        </li>
                        @foreach ($categories as $url => $category)
                            <li>
                                <a href="{{ route($route, ['category' => $url]) }}">
                                    {{ $category }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </nav>
                <div class="mask-content" style="z-index: 10000;"></div>
            @endif
        </div>
    </div>

    @if (!isset($games) || $games->isEmpty() || !$games->total() || $games->currentPage() > $games->lastPage())
        @if (isset($inWishlistSearch))
            <div class="info-block" style="display: none">
                <div class="news_content"><b>В списке желаемых ничего не найдено</b></div>
            </div>
        @elseif (isset($isWishlistPage))
            <div class="info-block" style="display: none">
                <div class="news_content"><b>Вы не добавили ни одной игры в список желаемого</b></div>
            </div>
        @elseif (isset($isSeriesSearch))
            <div class="info-block" style="display: none">
                <div class="info_title"><b>Такой серии на сайте не найдено</b></div>
            </div>
        @elseif (isset($justSearch))
            <div class="info-block" style="display: none">
                <div class="info_title"><b>Такой игры на сайте не найдено</b></div>
            </div>
        @endif
    @endif

    @if(isset($games) && $games->isNotEmpty())
        <div class="pagination">
            {{ $games->onEachSide(1)->links('pagination::bootstrap-4') }}
        </div>
    @endif

    @if (isset($jsFile))
        <script src="{{ asset($jsFile) }}?version={{config('app.version')}}"></script>
    @endif
    @if (Auth::check())
        <script type="module" src="{{ asset('modules/mainmodule/resources/assets/js/wishlistOnGrid.js') }}?version={{ config('app.version') }}"></script>
    @endif

@endsection
