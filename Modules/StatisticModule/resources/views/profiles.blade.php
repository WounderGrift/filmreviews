@extends('layouts.main')
@section('content')
    <div class="review">
        <div class="container">
            <h2 class="title">{{ $title }}</h2>

            <div class="error error_profiles">
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

            <nav style="display: flex; justify-content: flex-end;">
                <button class="btn btn-info get-data-profiles-chart">7Д</button>
                <button class="btn btn-info get-data-profiles-chart">1МЕС</button>
                <button class="btn btn-info get-data-profiles-chart">1ГОД</button>
                <button class="btn btn-info get-data-profiles-chart">5ЛЕТ</button>
                <button class="btn btn-info get-data-profiles-chart">ВСЮ</button>
            </nav>

            <form id="filter" method="GET" style="margin-top: 30px;">
                <input type="text" placeholder="Фильтр" style="margin-top: 15px; text-align: center; width: 100%; margin-bottom: 10px;">
            </form>

            <table id="table-profiles" class="display">
                <thead>
                <tr>
                    <th>Страниц ({{ $allValues }})</th>
                    <th>Имя</th>
                    <th>CID</th>
                    <th>Активность</th>
                    <th>Поддержка</th>
                    <th>Загрузок</th>
                    <th>Желаемые</th>
                    <th>Подписки на обновления</th>
                </tr>
                </thead>
                <tbody>
                @foreach($profiles as $key => $profile)
                    <tr>
                        <td style="display: flex; justify-content: center;">
                            @if ($profile->is_banned)
                                <img src="{{ asset('images/banned.png') }}"
                                     class="img-responsive profile-avatar"
                                     alt="{{ $profile->avatar_name ?? 'images/banned.png' }}"
                                     style="width: 100px; height: 100px;"/>
                            @else
                                <img
                                    src="{{ $profile->avatar_path ? Storage::url($profile->avatar_path) : asset('images/350.png') }}?timestamp={{ $profile->updated_at->timestamp }}"
                                    class="img-responsive profile-avatar"
                                    alt="{{ $profile->avatar_name ?? 'images/350.png' }}"
                                    style="width: 100px; height: 100px;"/>
                            @endif
                        </td>
                        <td data-label="Имя">{{$profile->name}} {{ $profile->is_banned ? ' (Бан)' : '' }}</td>
                        <td data-label="CID">
                            <a href="{{ route('profile.index.cid', ['cid' => $profile->cid]) }}">
                                {{$profile->cid}}
                            </a>
                        </td>
                        <td data-label="Активность">{{ \App\Http\Helpers\DateHelper::getLastActivity($profile->last_activity, Auth::user()->timezone) }}</td>
                        <td data-label="Поддержка">{{ $profile->link_count + $profile->banner_count }}</td>
                        <td data-label="Загрузок">{{ $profile->download_count }}</td>
                        <td data-label="Желаемые">{{ $profile->wishlist_count }}</td>
                        <td data-label="Подписки на обновления">{{ $profile->newsletters_count }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>

        </div>
    </div>

    @if(isset($profiles) && $profiles->isNotEmpty())
        <div class="pagination">
            {{ $profiles->onEachSide(1)->links('pagination::bootstrap-4') }}
        </div>
    @endif

    <script src="https://cdn.canvasjs.com/jquery.canvasjs.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.js"></script>
    <script type="module" src="{{ asset('Modules/StatisticModule/resources/assets/js/profiles-page.js') }}?version={{ config('app.version') }}"></script>
@endsection
