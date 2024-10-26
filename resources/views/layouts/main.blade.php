<!DOCTYPE html>
<html lang="ru-RU" class="h-100">
<head>
    <title>{{ config('app.app_name') }} - Реценции на фильмы</title>
    <link href="{{asset('css/bootstrap.css')}}" rel='stylesheet' type='text/css'/>
    <link href="{{asset('css/style.css')}}" rel='stylesheet' type='text/css'/>
    <link href="{{asset('css/itc-slider.css')}}" rel="stylesheet">

    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"/>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Swiper/11.0.4/swiper-bundle.css"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Swiper/11.0.4/swiper-bundle.min.js"></script>
    <link rel="icon" href="{{ asset('images/favicon.svg') }}" type="image/svg+xml">

    <script src="{{ asset('lib/underscore/underscore.js') }}"></script>
    <script src="{{ asset('lib/backbone/backbone.js') }}"></script>
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.js') }}"></script>
    <script type="module" src="{{ asset('js/view/main.js') }}?version={{ config('app.version') }}"></script>

    @if (File::exists(base_path('Modules/ProfileModule')) && !Auth::check())
        <script type="module"
                src="{{ asset('Modules/ProfileModule/resources/assets/js/registration.js') }}?version={{ config('app.version') }}"></script>
    @endif
</head>
<body>

@if (File::exists(base_path('Modules/ProfileModule')) && !Auth::check())
    <div class="popup-login popup disabled">
        <div class="popup-content auth disabled">
            <div class="loader"></div>
            <h2><u>Авторизация</u></h2>
            <h3 class="error"></h3>
            <form id="login-form" method="POST" onsubmit="return false;">
                <input id="login-email" class="regist-login" type="email" name="email" placeholder="Ваше Мыло">
                <input id="login-password" class="regist-login" type="password" name="password"
                       placeholder="Ваш Пароль">
                <label class="checkbox-container" for="login-remember">
                    Запомнить меня
                    <input type="checkbox" id="login-remember">
                    <span class="checkmark"></span>
                </label>
                <button type="submit">ЗАЙТИ</button>
            </form>
            <hr>
            <p>У вас нет аккаунта? <a class="register-link">Зарегистрируйтесь</a></p>
            <p>Или вы забыли пароль? <a class="restore-link">Восстановить</a></p>
        </div>
        <div class="popup-content register disabled">
            <div class="loader"></div>
            <h2><u>Зарегистрироваться</u></h2>
            <h3 class="error"></h3>
            <form id="registration-form" method="POST" onsubmit="return false;">
                <input id="registration-name" class="regist-login" type="text" name="name" placeholder="Ваше Имя">
                <input id="registration-email" class="regist-login" type="email" name="email" placeholder="Ваше Мыло">
                <input id="registration-password" class="regist-login" type="password" name="password"
                       placeholder="Ваш Пароль">
                <label class="checkbox-container" for="registration-remember">
                    Запомнить меня
                    <input type="checkbox" id="registration-remember">
                    <span class="checkmark"></span>
                </label>
                <label class="checkbox-container" for="mailing">
                    Хочу получать письма о новинках
                    <input type="checkbox" id="mailing" value="1">
                    <span class="checkmark"></span>
                </label>
                <button type="submit">ЗАЙТИ</button>
            </form>
            <p>У вас есть аккаунт? <a class="login-link">Вернуться назад</a></p>
        </div>
        <div class="popup-content restore disabled">
            <div class="loader"></div>
            <h2><u>Восстановить доступ</u></h2>
            <h3 class="error"></h3>
            <form id="restore-form" method="POST" onsubmit="return false;">
                <input id="restore-name" class="regist-login" type="text" name="name" placeholder="Ваше Имя">
                <input id="restore-email" class="regist-login" type="email" name="email" placeholder="Ваше Мыло">
                <button type="submit">ПРИСЛАТЬ ПИСЬМО</button>
            </form>
            <p>Вспомнили пароль? <a class="login-link">Вернуться</a></p>
        </div>
    </div>
@endif

<div class="top-banner">
    <div class="header">
        <div class="container">
            <div class="header-left">
                @if (isset($isUnpublished))
                    <div class="search">
                        <form action="{{ route('search.unpublished') }}" method="GET">
                            <input type="submit" value="">
                            <input type="text" name="query" autocomplete="off" placeholder="Поиск неопубликованных...">
                        </form>
                    </div>
                @elseif (isset($isSeries))
                    <div class="search">
                        <form action="{{ route('search.series') }}" method="GET">
                            <input type="submit" value="">
                            <input type="text" name="query" autocomplete="off" placeholder="Поиск серий...">
                        </form>
                    </div>
                @elseif (isset($isWishlistPage))
                    <div class="search">
                        <form action="{{ route('search.wishlist') }}" method="GET">
                            <input type="submit" value="">
                            <input type="text" name="query" autocomplete="off" placeholder="Поиск желаемых...">
                        </form>
                    </div>
                @else
                    <div class="search">
                        <form action="{{ route('search.index') }}" method="GET">
                            <input type="submit" value="">
                            <input type="text" name="query" autocomplete="off" placeholder="Поиск по сайту...">
                        </form>
                    </div>
                @endif
                <div class="clearfix"></div>
            </div>
            <div class="headr-right">
                <div class="details">
                    @if (!Auth::check())
                        @if (File::exists(base_path('Modules/ProfileModule')))
                            <a class="button-enter button">
                                <i class="fa fa-sign-in" aria-hidden="true"></i>ЗАГЛЯНУТЬ
                            </a>
                        @endif
                    @else
                        @if (Auth::user()->checkOwner())
                            <a href="{{ route('owner.index', ['hideBroken' => 0, 'source' => 'null']) }}"
                               class="btn btn-success">
                                <i class="fas fa-user-shield"></i>
                            </a>
                        @elseif (Auth::user()->checkAdmin())
                            <a href="{{ route('profiles.chart.table') }}" class="btn btn-success">
                                <i class="fas fa-user-shield"></i>
                            </a>
                        @endif

                        @if (File::exists(base_path('Modules/ProfileModule')))
                            <a href="{{ route('profile.index.cid', ['cid' => Auth::user()->cid]) }}"
                               class="btn btn-orange">
                                <i class="fas fa-user"></i>
                            </a>
                        @endif
                        {{--                         TODO message--}}
                        @if (Auth::user()->is_verify)
                            @if (!Auth::user()->is_banned && false)
                                <a class="btn btn-light">
                                    <i class="fas fa-envelope"></i>
                                </a>
                            @endif
                            <a href="{{ route('wishlist.index') }}" class="btn btn-info">
                                <i class="fas fa-heart"></i>
                            </a>
                        @endif

                        @if (File::exists(base_path('Modules/ProfileModule')))
                            <a href="{{ route('profile.logout') }}" class="btn btn-danger">
                                <i class="fas fa-sign-out-alt"></i>
                            </a>
                        @endif
                    @endif
                </div>
            </div>
            <div class="clearfix"></div>
        </div>
        <div id="main-loader" class="loader show"></div>
    </div>
    <!--banner-info-->

    <div class="banner-info" style="
        background: rgba(0, 0, 0, 0.5);
        position: fixed;
        width: 100% !important;
        left: 0 !important;
        height: 52px;
        top: 55px;">
        <div class="container" style="display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 10px;">
            <div class="logo">
                <h1><a href="{{ route('main.index') }}"> {{ strtoupper(config('app.app_name')) }}</a></h1>
            </div>
            <div class="top-menu">
                <span class="menu"></span>
                <ul class="nav1">
                    <li><a href="{{ route('all.index') }}">ВСЕ ФИЛЬМЫ</a></li>
                    <li><a href="{{ route('series.index') }}">СЕРИАЛЫ</a></li>
                    <li><a href="{{ route('new.index') }}">НОВИНОЧКИ</a></li>
                    <li><a href="{{ route('waiting.index') }}">ЖДЕМ</a></li>
                    @if (App\Models\YearReleases::query()->count() > 0)
                        <li>
                            <a href="{{ route('year.index.category', ['category' => App\Http\Helpers\UriHelper::yearForMenu()]) }}">ПО
                                ГОДАМ</a>
                        </li>
                    @endif\
                </ul>
            </div>
            <div class="clearfix"></div>
        </div>
    </div>
</div>
<!-- banner -->
<!-- Slider-starts-Here -->
<script src="{{ asset('js/responsiveslides.min.js') }}"></script>

@if ((isset($inProfilePage) || isset($isWishlistPage) || isset($inOwnerPanel) || isset($inPublishPage)) && Auth::check() && Auth::user()->is_verify)
    <div class="slider-swiper" style="display: none;">
        <div class="banner">
            <div class="bnr2"
                 style="background: url({{ Storage::url('banners/default/bnr3.jpg') }}) no-repeat 0 0; background-size: cover;">
            </div>
        </div>
    </div>

    <div class="slider-skeleton">
        <x-skeleton-loader style="width: 100%; height: 180px"></x-skeleton-loader>
    </div>
@else
    @php
        $bigBannerMenu = \App\Http\Helpers\BannerHelper::getBigBannerMenu($onlyActive = !isset($inBigBannerPage));
        $showDefaultBanner = true;
    @endphp

    @if ($bigBannerMenu->isNotEmpty())
        <div class="slider-swiper" style="display: none;">
            <div class="banner-slider">
                <div class="swiper" data-interval="3000">
                    <div class="swiper-wrapper bigBannerMenu">
                        @foreach ($bigBannerMenu as $index => $banner)
                            <div class="swiper-slide">
                                @if ($banner->media_type == 'image')
                                    <a data-code="{{ base64_encode($banner->id) }}" class="itc-slider-item banner-jump"
                                       style="background: url({{ Storage::url($banner->banner_path) }}?timestamp={{ $banner->updated_at->timestamp }}) no-repeat 0 0; background-size: cover; cursor: {{ !empty($banner->href) ? 'pointer' : 'auto' }}; height: 350px !important;"></a>

                                    @php $showDefaultBanner = false; @endphp
                                @elseif ($banner->media_type == 'video')
                                    <a data-code="{{ base64_encode($banner->id) }}" class="itc-slider-item banner-jump"
                                       style="cursor: {{ !empty($banner->href) ? 'pointer' : 'auto' }}; height: 350px !important;">
                                        <video autoplay muted loop
                                               style="object-fit: cover; width: 100%; height: 100%;">
                                            <source src="{{ Storage::url($banner->banner_path) }}?timestamp={{ $banner->updated_at->timestamp }}"
                                                    type="video/webm">
                                        </video>
                                    </a>

                                    @php $showDefaultBanner = false; @endphp
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <div class="swiper-scrollbar"></div>
                </div>
            </div>
        </div>

        <div class="slider-skeleton">
            <x-skeleton-loader style="width: 100%; height: 450px"></x-skeleton-loader>
        </div>
    @endif

    @if ($showDefaultBanner)
        <div class="slider-swiper" style="display: none;">
            <div class="swiper" data-interval="3000">
                <div class="swiper-wrapper bigBannerMenu">
                    <div class="swiper-slide">
                        <a class="itc-slider-item" style="height: 350px !important;">
                            <video autoplay muted loop style="object-fit: cover; width: 100%; height: 100%;">
                                <source src="{{ Storage::url('banners/default/big_banner_menu_default.webm') }}?version={{ config('app.version') }}"
                                        type="video/webm">
                            </video>
                        </a>
                    </div>
                </div>

                <div class="swiper-scrollbar"></div>
            </div>
        </div>

        <div class="slider-skeleton">
            <x-skeleton-loader style="width: 100%; height: 400px"></x-skeleton-loader>
        </div>
    @endif

    @if (false)
        <div class="slider">
            <div class="callbacks_container">
                <ul class="rslides" id="slider">
                    <div class="slid banner1">
                        <div class="caption"></div>
                    </div>

                    <div class="slid banner2">
                        <div class="caption"></div>
                    </div>

                    <div class="slid banner3">
                        <div class="caption"></div>
                    </div>
                </ul>
            </div>
        </div>
    @endif
@endif

<main>
    @if (isset($inOwnerPanel) && Auth::check() && Auth::user()->checkOwnerOrAdmin())
        <input type="checkbox" id="nav-toggle" hidden>
        <nav class="nav">
            <label for="nav-toggle" class="nav-toggle" onclick></label>

            <h2 class="logo">
                <a>УПРАВЛЕНИЕ</a>
            </h2>
            <ul>
                <li>
                    <a href="{{ route('owner.index', ['hideBroken' => 0, 'source' => 'null']) }}">
                        Панель управления
                    </a>
                </li>
                <li>
                    <a href="{{ route('series.list') }}">
                        Сериалы
                    </a>
                </li>
                <li>
                    <a href="{{ route('activity.chart.table') }}">
                        Статистика
                    </a>
                </li>
                @if (Auth::user()->checkOwner())
                    <li>
                        <a href="{{ route('big-banner.index') }}">
                            Спонсорство
                        </a>
                    </li>
                @endif
            </ul>
        </nav>
        <div class="mask-content" style="z-index: 10000;"></div>
    @endif

    @yield('content')

    <a id="back2Top" title="Наверх" href="#">&#10148;</a>
</main>

@if (!isset($inProfilePage) && !isset($inDetailPage))
    @php
        $basementBanner = \App\Http\Helpers\BannerHelper::getBasementBanners($onlyActive = !isset($inBasementBannerPage));
    @endphp

    @if ($basementBanner->isNotEmpty())
        <x-bannersmodule.banner-detail :banners="$basementBanner"></x-bannersmodule.banner-detail>
    @endif
@endif

<footer style="flex-shrink: 0;">
    <div class="copywrite">
        <div class="basement container">
            <a class="copyright" href="{{ route('main.index') }}">
                <p><i class="fas fa-copyright"></i> {{ date('Y') }} {{ config('app.app_name') }}</p>
            </a>
        </div>
    </div>
</footer>

</body>
</html>
