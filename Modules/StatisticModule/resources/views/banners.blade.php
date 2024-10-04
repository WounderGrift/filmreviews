@extends('layouts.main')
@section('content')
    <div class="review">
        <div class="container">
            <h2 class="title">{{ $title }}</h2>

            <div class="error error_banners">
                <h3></h3>
            </div>

            <x-statisticmodule.chart-menu></x-statisticmodule.chart-menu>

            <div class="corner-box-6"
                 style="height: 295px; width: 100%; background: rgb(74 85 104 / 70%); display: none;">
                <div class="corner-ribbon-6 corner-ribbon-6-top-left">
                    <span>404</span>
                </div>
                <p class="striped-text">!Data.length</p>
            </div>

            <div id="chartContainer" style="height: 300px; width: 100%;"></div>

            <nav style="display: flex; justify-content: flex-end; margin-bottom: 10px;">
                <button class="btn btn-info get-data-profiles-chart">7Д</button>
                <button class="btn btn-info get-data-profiles-chart">1МЕС</button>
                <button class="btn btn-info get-data-profiles-chart">1ГОД</button>
                <button class="btn btn-info get-data-profiles-chart">5ЛЕТ</button>
                <button class="btn btn-info get-data-profiles-chart">ВСЮ</button>
            </nav>

            <table id="table-bannners" class="display">
                <thead>
                <tr>
                    <th>Баннеров ({{ $allBanners }})</th>
                    <th>Название</th>
                    <th>Ссылка</th>
                    <th>Переходов</th>
                    <th>Создан</th>
                </tr>
                </thead>
                <tbody>
                @foreach($banners as $key => $banner)
                    <tr data-banner-id="{{$banner->id}}">
                        <td>
                            @if ($banner->media_type == 'image')
                            <img src="{{ Storage::url($banner->banner_path)  }}"
                                 class="img-responsive"
                                 alt="{{ $banner->banner_name ?? 'images/banned.png' }}"
                                 style="width: 100px; height: 60px;"/>
                            @elseif ($banner->media_type == 'video')
                                <video data-type="video" autoplay muted loop
                                       style="width: 100px;">
                                    <source src="{{ Storage::url($banner->banner_path) }}" type="video/webm">
                                </video>
                            @endif
                        </td>
                        <td data-label="Название">{{$banner->banner_name}}
                            ({{$banner->active ? 'Активно' : 'Не активно'}})
                        </td>
                        <td data-label="Ссылка">{{$banner->href ?? 'Не указано'}}</td>
                        <td id="jump-banners" data-label="Переходов"></td>
                        <td data-label="Создан">{{$banner->created_at}}</td>
                    </tr>
                @endforeach
                @foreach($gamesSponsors as $key => $gamesSponsor)
                    <tr data-game-id="{{$gamesSponsor->id}}">
                        <td>
                            <img src="{{ Storage::url($gamesSponsor->preview_grid) }}"
                                 class="img-responsive"
                                 alt="{{ $gamesSponsor->name }}"
                                 style="width: 100px; height: 110px;"/>
                        </td>
                        <td data-label="Название">{{ $gamesSponsor->name }}
                            ({{ $gamesSponsor->status == App\Models\Game::STATUS_PUBLISHED ? 'Активно' : 'Не активно' }})
                        </td>
                        <td data-label="Ссылка">
                            <a href="{{ route('detail.index.uri', ['uri' => $gamesSponsor->uri]) }}" target="_blank">
                            {{ $gamesSponsor->uri ?? 'Не указано' }}
                            </a>
                        </td>
                        <td id="jump-sponsors" data-label="Переходов"></td>
                        <td data-label="Создан">{{ $gamesSponsor->created_at }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>

        </div>
    </div>

    <script src="https://cdn.canvasjs.com/jquery.canvasjs.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.js"></script>
    <script type="module" src="{{ asset('modules/statisticmodule/resources/assets/js/banners-page.js') }}?version={{ config('app.version') }}"></script>
@endsection
