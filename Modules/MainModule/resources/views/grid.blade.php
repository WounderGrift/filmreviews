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
                    <div class="info_title"><b>Фильмы, которые уже должны были выйти, не найдено</b></div>
                </div>
            @endif

            <div class="films-skeleton-list">
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

            <div class="films-list" style="display: none;">
            @if (isset($films) && ($films->isNotEmpty() || $films->total() || $films->currentPage() < $films->lastPage()))
                @foreach ($films as $key => $film)
                    @if ($key % 4 == 0)
                        <div class="row align-items-start">
                    @endif

                    <div class="col-md-4 sed-md">
                        <div class="col-1" style="border-radius: 10px;">
                            <a href="{{ File::exists(base_path('Modules/DetailModule')) ? route('detail.index.uri', ['uri' => $film->uri]) : '#' }}">
                                <img class="img-responsive"
                                     src="{{ Storage::disk('public')->exists($film->preview_grid) ? Storage::url($film->preview_grid) : asset('images/440.png') }}?timestamp={{ $film->updated_at->timestamp }}"
                                     alt="{{ $film->name }}">
                                @if ($film->is_sponsor)
                                    <span class="vers hit"><i class="fas fa-sync-alt"></i>СПОНСОР</span>
                                @elseif ($film->is_waiting)
                                    <span class="vers hit"><i class="fas fa-hourglass"></i>ЕЩЕ НЕ ВЫШЛА</span>
                                @elseif ($film->files->isNotEmpty() && $film->files->max('version') != 'v0.0')
                                    <span class="vers"><i class="fas fa-sync-alt"></i>{{ $film->files->max('version') }}</span>
                                @endif
                            </a>
                            <a href="{{ File::exists(base_path('Modules/DetailModule')) ? route('detail.index.uri', ['uri' => $film->uri]) : '#' }}" class="film-name">
                                <h4>{{ $film->name }}</h4>
                            </a>
                            @if (Auth::check())
                                @if (Auth::user()->is_verify)
                                    <label class="heart-checkbox wishlist-action">
                                        <input type="checkbox" class="wishlist-checkbox"
                                               data-film-id="{{ base64_encode($film->id) }}" {{ Auth::user()->wishlist->contains('id', $film->id) ? 'checked' : '' }}>
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

    @if (!isset($films) || $films->isEmpty() || !$films->total() || $films->currentPage() > $films->lastPage())
        @if (isset($inWishlistSearch))
            <div class="info-block" style="display: none">
                <div class="news_content"><b>В списке желаемых ничего не найдено</b></div>
            </div>
        @elseif (isset($isWishlistPage))
            <div class="info-block" style="display: none">
                <div class="news_content"><b>Вы не добавили ни одного фильма в список желаемого</b></div>
            </div>
        @elseif (isset($isSeriesSearch))
            <div class="info-block" style="display: none">
                <div class="info_title"><b>Такой серии на сайте не найдено</b></div>
            </div>
        @elseif (isset($justSearch))
            <div class="info-block" style="display: none">
                <div class="info_title"><b>Такого фильма на сайте не найдено</b></div>
            </div>
        @endif
    @endif

    @if(isset($films) && $films->isNotEmpty())
        <div class="pagination">
            {{ $films->onEachSide(1)->links('pagination::bootstrap-4') }}
        </div>
    @endif

    @if (isset($jsFile))
        <script src="{{ asset($jsFile) }}?version={{config('app.version')}}"></script>
    @endif
    @if (Auth::check())
        <script type="module" src="{{ asset('Modules/MainModule/resources/assets/js/wishlistOnGrid.js') }}?version={{ config('app.version') }}"></script>
    @endif

@endsection
