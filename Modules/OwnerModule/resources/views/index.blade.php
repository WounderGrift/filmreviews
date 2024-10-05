@extends('layouts.main')
@section('content')

    <div class="review">
        <div class="container">
            <h2 class="title">ПАНЕЛЬ УПРАВЛЕНИЯ</h2>
            <div class="blog-left">
                <div class="blog-info">
                    <div class="blog-info-text">
                        <div class="error error_harvester">
                            <h3></h3>
                        </div>

                        <label class="checkbox-container" for="hide_broken">
{{--                            <a href="{{ route('owner.index', ['hideBroken' => $hideBroken ? 0 : 1, 'source' => \App\Http\Helpers\UriHelper::getSourceInUrlInOwnerPanel()]) }}">--}}
{{--                                Скрыть сломанные--}}
{{--                                <input type="checkbox" id="hide_broken" name="hide_broken_name" value="1" {{ $hideBroken ? 'checked' : '' }}>--}}
{{--                                <span class="checkmark"></span>--}}
{{--                            </a>--}}
                        </label>
                        <div class="custom-dropdown source-dropdown">
                            <div id="source-list" data-default-value="null">
                            <span id="source-answer" class="placeholder source-select">
{{--                                <input type="text" id="searchSource" class="selected-options"--}}
{{--                                       style="width: 100%; text-align: center;"--}}
{{--                                       value="{{ $sourced }}">--}}
                            </span>
                            </div>
                            <div class="options">
{{--                                <a href="{{ route('owner.index', ['hideBroken' => $hideBroken ? 1 : 0, 'source' => 'null']) }}">--}}
{{--                                    <div class="option {{ $sourced == 'null' ? 'selected' : ''}}"--}}
{{--                                         data-value="null">null--}}
{{--                                    </div>--}}
{{--                                </a>--}}
{{--                                @foreach(\App\Models\Harvester::getSources() as $source)--}}
{{--                                    <a href="{{ route('owner.index', ['hideBroken' => $hideBroken ? 1 : 0, 'source' => $source]) }}">--}}
{{--                                        <div--}}
{{--                                            class="option {{ $source == $sourced ? 'selected' : ''}}"--}}
{{--                                            data-value="{{ $source }}">{{ $source }}</div>--}}
{{--                                    </a>--}}
{{--                                @endforeach--}}
                            </div>
                        </div>
                        <table id="table-harvester-log" class="display">
                            <thead>
                            <tr>
                                <th>Имя</th>
                                <th>Путь</th>
                                <th>Страница</th>
                                <th>Источник</th>
                                <th>Тип</th>
                                <th>Статус</th>
                                <th>Время</th>
                            </tr>
                            </thead>
                            <tbody>
{{--                            @foreach($harvesting as $harvest)--}}
{{--                                <tr>--}}
{{--                                    <td data-label="Имя">--}}
{{--                                        @if (isset($harvest->game_id) && isset($harvest->game))--}}
{{--                                            <a href="{{ route('detail.edit.index', ['uri' => $harvest->game->uri]) }}"--}}
{{--                                               target="_blank">--}}
{{--                                                {{ $harvest->name }}--}}
{{--                                            </a>--}}
{{--                                        @else--}}
{{--                                            {{ $harvest->name }}--}}
{{--                                        @endif--}}
{{--                                    </td>--}}
{{--                                    <td data-label="Путь">--}}
{{--                                        <a href="{{ $harvest->url }}" target="_blank">--}}
{{--                                            @if (isset($harvest->game_id) && isset($harvest->game))--}}
{{--                                                {{ $harvest->game->name }}--}}
{{--                                            @else--}}
{{--                                                {{ basename($harvest->url) }}--}}
{{--                                            @endif--}}
{{--                                        </a>--}}
{{--                                    </td>--}}
{{--                                    <td data-label="Страница">{{ $harvest->page_count }}</td>--}}
{{--                                    <td data-label="Источник">{{ $harvest->source }}</td>--}}
{{--                                    <td data-label="Тип">{{ $harvest->action }}</td>--}}
{{--                                    <td data-label="Статус">{{ $harvest->checkShowDoubleStatus() ? $harvest->status ." / ". $harvest?->game?->status : $harvest->status  }}</td>--}}
{{--                                    <td data-label="Время">{{ $harvest->updated_at }}</td>--}}
{{--                                </tr>--}}
{{--                            @endforeach--}}
                            </tbody>
                        </table>

                        @if(isset($harvesting) && $harvesting->isNotEmpty())
                            <div class="pagination">
                                {{ $harvesting->onEachSide(1)->links('pagination::bootstrap-4') }}
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.canvasjs.com/jquery.canvasjs.min.js"></script>
    <script type="module" src="{{asset('Modules/OwnerModule/resources/assets/js/owner.js')}}?version={{config('app.version')}}"></script>
@endsection
