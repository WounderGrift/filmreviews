@extends('layouts.main')
@section('content')

    <div class="blog">
        <div class="container">
            <h2 class="title">{{ $title }}</h2>

            <x-bannersmodule.banner-menu></x-bannersmodule.banner-menu>

            @if (isset($inDetailBannerPage))
                @php
                    $detailBanner = \App\Http\Helpers\BannerHelper::getDetailBannerMenu();
                @endphp

                @if ($detailBanner->isNotEmpty())
                    <x-bannersmodule.banner-detail :banners="$detailBanner"></x-bannersmodule.banner-detail>
                @endif
            @endif

            <div style="display: flex; justify-content: flex-end;">
                <button id="add-banner" class="btn btn-orange"
                        style="margin-top: 10px; margin-right: 10px; float: right">
                    Добавить баннер
                </button>
            </div>

            <div id="big-banners-menu">
                @if (isset($banners))
                    @foreach($banners as $banner)
                        <div class="banner-container old" data-banner-id="{{ $banner->id }}">
                            <i class="fas fa-times fa-lg remove remove-banner"
                               style="float: right; margin-left: 10px;"
                               title="Мягкое удаление баннера"></i>

                            <i class="fas fa-times fa-lg remove remove-forced-banner"
                               style="float: left; margin-left: 10px; color: red;"
                               title="Жесткое удаление баннера"></i>

                            @if ($banner->active)
                                <i class="fas fa-check-circle fa-lg active-banner" style="float: right;"
                                   title="Активный\Неактивный отображает текущий статус"></i>
                            @else
                                <i class="fas fa-times-circle fa-lg active-banner" style="float: right;"
                                   title="Активный\Неактивный отображает текущий статус"></i>
                            @endif

                            <div class="spoiler">
                                @if ($banner->trashed())
                                    <h2 class="removed-sign" style="color: black">УДАЛЕНО</h2>
                                @endif
                                <div class="spoiler-header">
                                    <div class="inside-banner" style="display: flex; align-items: flex-start;">
                                        <h2 class="download-title">
                                            Баннер - <input type="text" class="banner-name-input"
                                                            value="{{ $banner->banner_name }}"
                                                            style="text-align: center; width: 80%;">
                                            <ul class="requirement-list" style="margin-top: 10px;">
                                                Позиция
                                                <li style="margin-bottom: 5px;">
                                                    <input type="text" class="banner-position-input"
                                                           value="{{ $banner->position }}"
                                                           style="text-align: center; width: 80%;">
                                                </li>
                                                Ссылка
                                                <li style="margin-bottom: 5px;">
                                                    <input type="text" class="banner-href-input"
                                                           value="{{ $banner->href }}"
                                                           style="text-align: center; width: 80%;">
                                                </li>
                                            </ul>
                                        </h2>
                                        <span class="toggle-icon">▲</span>
                                    </div>
                                </div>
                                <div class="spoiler-content" style="text-align: center">
                                    <div class="spoiler-description">
                                        <div class="grid-box grid-block">
                                            <div>
                                                <div class="header-avatar banner-preview">
                                                    @if ($banner->media_type == 'image')
                                                        <img id="avatar"
                                                             src="{{ Storage::url($banner->banner_path) }}"
                                                             class="img-responsive"
                                                             alt="{{ $banner->banner_name }}"/>
                                                    @elseif ($banner->media_type == 'video')
                                                        <video data-type="video" autoplay muted loop
                                                               style="width: 100%; height: 100%; border-radius: 10px;">
                                                            <source src="{{ Storage::url($banner->banner_path) }}"
                                                                type="video/webm">
                                                        </video>
                                                    @endif
                                                </div>
                                                <label class="footer-avatar big-banner-label">
                                                    <svg fill="#000000" viewBox="0 0 32 32"
                                                         xmlns="http://www.w3.org/2000/svg">
                                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round"
                                                           stroke-linejoin="round"></g>
                                                        <g id="SVGRepo_iconCarrier">
                                                            <path d="M15.331 6H8.5v20h15V14.154h-8.169z"></path>
                                                            <path d="M18.153 6h-.009v5.342H23.5v-.002z"></path>
                                                        </g>
                                                    </svg>
                                                    <p id="avatar-name">{{ $banner->banner_name }}</p>
                                                </label>
                                                <input id="bannerInput" type="file" name="avatar"
                                                       data-id="{{ $banner->id }}"
                                                       accept="{{ $mimeTypeBanner }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif

                @foreach(\App\Http\Helpers\BannerHelper::getExtraBanners() as $extraBanner)
                        <div class="banner-container extra">
                            <i class="fas fa-times fa-lg remove remove-forced-banner"
                               style="float: left; margin-left: 10px; color: red;"
                               title="Жесткое удаление баннера"></i>

                            <div class="spoiler">
                                <h2 class="removed-sign" style="color: black">Лишний баннер</h2>
                                <div class="spoiler-header">
                                    <div class="inside-banner" style="display: flex; align-items: flex-start;">
                                        <h2 class="download-title">{{ $extraBanner }}</h2>
                                    </div>

                                    <a href="{{ $extraBanner }}" class="btn btn-success">
                                        <i class="fa fa-download" style="margin-right: 5px;" aria-hidden="true"></i>
                                        Просмотр
                                    </a>
                                </div>
                            </div>
                        </div>
                @endforeach
            </div>

            <div id="template-banner-big-menu" hidden>
                <x-bannersmodule.banner-spoiler memeType="{{ $mimeTypeBanner }}"></x-bannersmodule.banner-spoiler>
            </div>

            <div class="error error_banner">
                <h3></h3>
            </div>

            @if (isset($inBigBannerPage))
                <button id="banners-save" class="btn btn-orange" data-type-banner="big_banner_menu"
                        style="margin-top: 10px; margin-right: 10px; float: right">
                    Сохранить
                </button>
            @elseif (isset($inDetailBannerPage))
                <button id="banners-save" class="btn btn-orange" data-type-banner="detail_banner"
                        style="margin-top: 10px; margin-right: 10px; float: right">
                    Сохранить
                </button>
            @elseif (isset($inBasementBannerPage))
                <button id="banners-save" class="btn btn-orange" data-type-banner="basement_banner"
                        style="margin-top: 10px; margin-right: 10px; float: right">
                    Сохранить
                </button>
            @endif
        </div>
    </div>

    <script type="module" src="{{ asset('modules/bannersmodule/resources/assets/js/banners-page.js') }}?version={{ config('app.version') }}"></script>
@endsection
